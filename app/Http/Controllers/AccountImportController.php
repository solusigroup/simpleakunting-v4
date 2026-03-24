<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AccountImportController extends Controller
{
    /**
     * Show import form
     */
    public function showForm()
    {
        return view('accounts.import');
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Template
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chart of Accounts');
        
        // Headers
        $headers = ['Code', 'Name', 'Type', 'Report Type', 'Normal Balance', 'Parent Code', 'Is Parent', 'Is Active'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        
        // Sample data
        $sampleData = [
            ['1', 'ASET', 'Asset', 'NERACA', 'DEBIT', '', 'TRUE', 'TRUE'],
            ['1.1', 'Aset Lancar', 'Asset', 'NERACA', 'DEBIT', '1', 'TRUE', 'TRUE'],
            ['1.1.1', 'Kas & Bank', 'Asset', 'NERACA', 'DEBIT', '1.1', 'TRUE', 'TRUE'],
            ['1.1.1.1', 'Kas', 'Asset', 'NERACA', 'DEBIT', '1.1.1', 'FALSE', 'TRUE'],
            ['1.1.1.2', 'Bank', 'Asset', 'NERACA', 'DEBIT', '1.1.1', 'FALSE', 'TRUE'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Sheet 2: Instructions
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        $instructions = [
            ['Column', 'Required?', 'Type', 'Valid Values', 'Description'],
            ['Code', 'Yes', 'Text', 'Max 20 characters', 'Unique account code'],
            ['Name', 'Yes', 'Text', 'Max 100 characters', 'Account name'],
            ['Type', 'Yes', 'Enum', 'Asset, Liability, Equity, Revenue, Expense', 'Account type'],
            ['Report Type', 'Yes', 'Enum', 'NERACA, LABARUGI', 'Financial report type'],
            ['Normal Balance', 'Yes', 'Enum', 'DEBIT, KREDIT', 'Normal balance side'],
            ['Parent Code', 'No', 'Text', 'Must match existing code', 'Parent account code (for hierarchy)'],
            ['Is Parent', 'No', 'Boolean', 'TRUE, FALSE', 'Header account (cannot have transactions)'],
            ['Is Active', 'No', 'Boolean', 'TRUE, FALSE', 'Account status (default TRUE)'],
        ];
        $instructionsSheet->fromArray($instructions, null, 'A1');
        
        // Style instruction headers
        $instructionsSheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        foreach (range('A', 'E') as $col) {
            $instructionsSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set active sheet back to first
        $spreadsheet->setActiveSheetIndex(0);
        
        // Download
        $writer = new Xlsx($spreadsheet);
        $filename = 'chart_of_accounts_template_' . date('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Import Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 400);
        }

        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk import akun.',
            ], 403);
        }

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header
            $headers = array_shift($rows);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $imported = [];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-index

                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $code = trim($row[0] ?? '');
                    $name = trim($row[1] ?? '');
                    $type = trim($row[2] ?? '');
                    $reportType = trim($row[3] ?? '');
                    $normalBalance = trim($row[4] ?? '');
                    $parentCode = trim($row[5] ?? '');
                    $isParent = strtoupper(trim($row[6] ?? 'FALSE')) === 'TRUE';
                    $isActive = strtoupper(trim($row[7] ?? 'TRUE')) === 'TRUE';

                    // Validation
                    if (empty($code) || empty($name) || empty($type) || empty($reportType) || empty($normalBalance)) {
                        throw new \Exception('Required fields missing');
                    }

                    // Check if code already exists
                    $exists = ChartOfAccount::where('company_id', $company->id)
                        ->where('code', $code)
                        ->exists();

                    if ($exists) {
                        throw new \Exception('Code already exists');
                    }

                    // Validate type
                    $validTypes = ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'];
                    if (!in_array($type, $validTypes)) {
                        throw new \Exception('Invalid type. Must be: ' . implode(', ', $validTypes));
                    }

                    // Validate report type
                    if (!in_array($reportType, ['NERACA', 'LABARUGI'])) {
                        throw new \Exception('Invalid report type. Must be: NERACA or LABARUGI');
                    }

                    // Validate normal balance
                    if (!in_array($normalBalance, ['DEBIT', 'KREDIT'])) {
                        throw new \Exception('Invalid normal balance. Must be: DEBIT or KREDIT');
                    }

                    // Find parent if specified
                    $parentId = null;
                    $level = 1;
                    if (!empty($parentCode)) {
                        $parent = ChartOfAccount::where('company_id', $company->id)
                            ->where('code', $parentCode)
                            ->first();

                        if (!$parent) {
                            throw new \Exception("Parent code '$parentCode' not found");
                        }

                        $parentId = $parent->id;
                        $level = $parent->level + 1;
                    }

                    // Create account
                    $account = ChartOfAccount::create([
                        'company_id' => $company->id,
                        'code' => $code,
                        'name' => $name,
                        'type' => $type,
                        'report_type' => $reportType,
                        'normal_balance' => $normalBalance,
                        'parent_id' => $parentId,
                        'is_parent' => $isParent,
                        'level' => $level,
                        'is_system' => false,
                        'is_active' => $isActive,
                    ]);

                    $successCount++;
                    $imported[] = [
                        'row' => $rowNumber,
                        'code' => $code,
                        'name' => $name,
                    ];

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'code' => $code ?? '',
                        'name' => $name ?? '',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import completed: $successCount success, $errorCount failed",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'imported' => $imported,
                    'errors' => $errors,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Load Default COA (BUMDesa Structure)
     */
    public function loadDefault(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 400);
        }

        if (!$user->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengelola akun.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Attempt to delete existing accounts
            // This will fail if accounts are referenced by other tables (journals, etc.)
            try {
                // Delete detail accounts first, then headers? 
                // Alternatively, just delete all for this company. 
                // Dependent on how FKs are set up (CASCADE or RESTRICT). Usually RESTRICT for integrity.
                ChartOfAccount::where('company_id', $company->id)->delete();
            } catch (\Illuminate\Database\QueryException $e) {
                // Check code '23000' for Integrity constraint violation
                if ($e->getCode() === '23000') {
                    throw new \Exception('Gagal menghapus akun lama karena sedang digunakan dalam transaksi atau master data lain. Hapus data terkait terlebih dahulu.');
                }
                throw $e;
            }
            
            // Run the seeder
            $seeder = new \Database\Seeders\CoaCustomSeeder();
            $seeder->run($company);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil! Akun lama telah dihapus dan Default COA berhasil dimuat.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat COA: ' . $e->getMessage(),
            ], 500);
        }
    }
}
