<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FixedAssetImportController extends Controller
{
    public function showForm()
    {
        return view('assets.import');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fixed Assets');
        
        $headers = ['Code', 'Name', 'Acquisition Date', 'Acquisition Cost', 'Salvage Value', 'Useful Life (Months)', 'Depreciation Method', 'Asset COA Code', 'Accum Dep COA Code',  'Is Active'];
        $sheet->fromArray($headers, null, 'A1');
        
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        $sampleData = [
            ['A001', 'Komputer', '2024-01-01', '15000000', '1000000', '36', 'straight_line', '1.2.1', '1.2.2', 'TRUE'],
            ['A002', 'Kendaraan', '2024-01-15', '150000000', '15000000', '60', 'straight_line', '1.2.3', '1.2.4', 'TRUE'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');
        
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        $instructions = [
            ['Column', 'Required?', 'Type', 'Valid Values', 'Description'],
            ['Code', 'Yes', 'Text', 'Max 20 chars, unique', 'Kode aset'],
            ['Name', 'Yes', 'Text', 'Max 255 chars', 'Nama aset'],
            ['Acquisition Date', 'Yes', 'Date', 'YYYY-MM-DD', 'Tanggal perolehan'],
            ['Acquisition Cost', 'Yes', 'Decimal', '> 0', 'Harga perolehan'],
            ['Salvage Value', 'No', 'Decimal', '>= 0', 'Nilai residu (default 0)'],
            ['Useful Life (Months)', 'Yes', 'Integer', '> 0', 'Umur manfaat dalam bulan'],
            ['Depreciation Method', 'No', 'Enum', 'straight_line, declining_balance', 'Metode penyusutan (default straight_line)'],
            ['Asset COA Code', 'No', 'Text', 'Must exist in COA', 'Kode akun aset'],
            ['Accum Dep COA Code', 'No', 'Text', 'Must exist in COA', 'Kode akun akumulasi penyusutan'],
            ['Is Active', 'No', 'Boolean', 'TRUE, FALSE', 'Status (default TRUE)'],
        ];
        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        foreach (range('A', 'E') as $col) {
            $instructionsSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'fixed_assets_template_' . date('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);

        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 400);
        }

        if (!$user->canManageMasterData()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk import aset tetap.'], 403);
        }

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            array_shift($rows);

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
                    $acquisitionDate = trim($row[2] ?? '');
                    $acquisitionCost = trim($row[3] ?? '0');
                    $salvageValue = trim($row[4] ?? '0');
                    $usefulLifeMonths = trim($row[5] ?? '0');
                    $depreciationMethod = trim($row[6] ?? 'straight_line');
                    $assetCoaCode = trim($row[7] ?? '');
                    $accumCoaCode = trim($row[8] ?? '');
                    $isActive = strtoupper(trim($row[9] ?? 'TRUE')) === 'TRUE';

                    if (empty($code) || empty($name) || empty($acquisitionDate) || empty($acquisitionCost) || empty($usefulLifeMonths)) {
                        throw new \Exception('Code, Name, Acquisition Date, Cost, and Useful Life are required');
                    }

                    $exists = FixedAsset::where('company_id', $company->id)
                        ->where('code', $code)
                        ->exists();

                    if ($exists) {
                        throw new \Exception('Code already exists');
                    }

                    if (!in_array($depreciationMethod, ['straight_line', 'declining_balance'])) {
                        throw new \Exception('Invalid depreciation method');
                    }

                    $coaId = null;
                    if (!empty($assetCoaCode)) {
                        $coa = ChartOfAccount::where('company_id', $company->id)
                            ->where('code', $assetCoaCode)
                            ->first();
                        if (!$coa) {
                            throw new \Exception("Asset COA code '$assetCoaCode' not found");
                        }
                        $coaId = $coa->id;
                    }

                    $accumCoaId = null;
                    if (!empty($accumCoaCode)) {
                        $coa = ChartOfAccount::where('company_id', $company->id)
                            ->where('code', $accumCoaCode)
                            ->first();
                        if (!$coa) {
                            throw new \Exception("Accum Dep COA code '$accumCoaCode' not found");
                        }
                        $accumCoaId = $coa->id;
                    }

                    $asset = FixedAsset::create([
                        'company_id' => $company->id,
                        'coa_id' => $coaId,
                        'accum_coa_id' => $accumCoaId,
                        'code' => $code,
                        'name' => $name,
                        'acquisition_date' => $acquisitionDate,
                        'acquisition_cost' => (float)$acquisitionCost,
                        'salvage_value' => (float)$salvageValue,
                        'useful_life_months' => (int)$usefulLifeMonths,
                        'depreciation_method' => $depreciationMethod,
                        'accumulated_depreciation' => 0,
                        'is_active' => $isActive,
                    ]);

                    $successCount++;
                    $imported[] = ['row' => $rowNumber, 'code' => $code, 'name' => $name];

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
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            abort(404, 'Company not found');
        }

        $assets = FixedAsset::where('company_id', $company->id)
            ->with(['account', 'accumulatedAccount'])
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fixed Assets');
        
        $headers = ['Code', 'Name', 'Acquisition Date', 'Acquisition Cost', 'Salvage Value', 'Useful Life (Months)', 'Depreciation Method', 'Asset COA Code', 'Accum Dep COA Code', 'Is Active'];
        $sheet->fromArray($headers, null, 'A1');
        
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        $rowIndex = 2;
        foreach ($assets as $asset) {
            $sheet->fromArray([
                $asset->code,
                $asset->name,
                $asset->acquisition_date,
                $asset->acquisition_cost,
                $asset->salvage_value,
                $asset->useful_life_months,
                $asset->depreciation_method,
                $asset->account ? $asset->account->code : '',
                $asset->accumulatedAccount ? $asset->accumulatedAccount->code : '',
                $asset->is_active ? 'TRUE' : 'FALSE',
            ], null, "A{$rowIndex}");
            $rowIndex++;
        }
        
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'fixed_assets_export_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
