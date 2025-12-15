<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ContactImportController extends Controller
{
    public function showForm()
    {
        return view('contacts.import');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contacts');
        
        // Headers
        $headers = ['Code', 'Name', 'Type', 'Phone', 'Email', 'Address', 'NPWP', 'Is Active'];
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
            ['C001', 'PT Maju Jaya', 'Customer', '081234567890', 'maju@example.com', 'Jl. Sudirman No. 123', '01.234.567.8-901.000', 'TRUE'],
            ['C002', 'CV Sejahtera', 'Customer', '081234567891', 'sejahtera@example.com', 'Jl. Thamrin No. 45', '01.234.567.8-902.000', 'TRUE'],
            ['S001', 'PT Supplier Indo', 'Supplier', '081234567892', 'supplier@example.com', 'Jl. Gatot Subroto No. 78', '01.234.567.8-903.000', 'TRUE'],
            ['B001', 'PT Multi Service', 'Both', '081234567893', 'multi@example.com', 'Jl. Kuningan No. 99', '01.234.567.8-904.000', 'TRUE'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Instructions sheet
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        $instructions = [
            ['Column', 'Required?', 'Type', 'Valid Values', 'Description'],
            ['Code', 'No', 'Text', 'Max 20 characters, unique', 'Kode kontak (optional, auto-generated if empty)'],
            ['Name', 'Yes', 'Text', 'Max 255 characters', 'Nama kontak'],
            ['Type', 'Yes', 'Enum', 'Customer, Supplier, Both', 'Tipe kontak'],
            ['Phone', 'No', 'Text', '-', 'Nomor telepon'],
            ['Email', 'No', 'Email', '-', 'Email address'],
            ['Address', 'No', 'Text', '-', 'Alamat lengkap'],
            ['NPWP', 'No', 'Text', '-', 'Nomor NPWP'],
            ['Is Active', 'No', 'Boolean', 'TRUE, FALSE', 'Status (default TRUE)'],
        ];
        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        foreach (range('A', 'E') as $col) {
            $instructionsSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'contacts_template_' . date('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

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

        if (!$user->canManageMasterData()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk import kontak.',
            ], 403);
        }

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            array_shift($rows); // Remove header

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $imported = [];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    if (empty(array_filter($row))) continue;

                    $code = trim($row[0] ?? '');
                    $name = trim($row[1] ?? '');
                    $type = trim($row[2] ?? '');
                    $phone = trim($row[3] ?? '');
                    $email = trim($row[4] ?? '');
                    $address = trim($row[5] ?? '');
                    $npwp = trim($row[6] ?? '');
                    $isActive = strtoupper(trim($row[7] ?? 'TRUE')) === 'TRUE';

                    // Validation
                    if (empty($name) || empty($type)) {
                        throw new \Exception('Name and Type are required');
                    }

                    if (!in_array($type, ['Customer', 'Supplier', 'Both'])) {
                        throw new \Exception('Invalid type. Must be: Customer, Supplier, or Both');
                    }

                    // Check duplicate code if provided
                    if (!empty($code)) {
                        $exists = Contact::where('company_id', $company->id)
                            ->where('code', $code)
                            ->exists();

                        if ($exists) {
                            throw new \Exception('Code already exists');
                        }
                    }

                    // Email validation
                    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception('Invalid email format');
                    }

                    $contact = Contact::create([
                        'company_id' => $company->id,
                        'code' => $code ?: null,
                        'name' => $name,
                        'type' => $type,
                        'phone' => $phone ?: null,
                        'email' => $email ?: null,
                        'address' => $address ?: null,
                        'npwp' => $npwp ?: null,
                        'is_active' => $isActive,
                    ]);

                    $successCount++;
                    $imported[] = [
                        'row' => $rowNumber,
                        'code' => $contact->code ?? '-',
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

    public function export(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            abort(404, 'Company not found');
        }

        // Get all contacts for this company
        $contacts = Contact::where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contacts');
        
        // Headers
        $headers = ['Code', 'Name', 'Type', 'Phone', 'Email', 'Address', 'NPWP', 'Is Active'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        
        // Data rows
        $rowIndex = 2;
        foreach ($contacts as $contact) {
            $sheet->fromArray([
                $contact->code ?? '',
                $contact->name,
                $contact->type,
                $contact->phone ?? '',
                $contact->email ?? '',
                $contact->address ?? '',
                $contact->npwp ?? '',
                $contact->is_active ? 'TRUE' : 'FALSE',
            ], null, "A{$rowIndex}");
            $rowIndex++;
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'contacts_export_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
