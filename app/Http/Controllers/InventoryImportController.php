<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InventoryImportController extends Controller
{
    public function showForm()
    {
        return view('inventory.import');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory');
        
        $headers = ['Code', 'Name', 'Unit', 'Cost', 'Price', 'Stock', 'Min Stock', 'COA Code', 'Is Active'];
        $sheet->fromArray($headers, null, 'A1');
        
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        $sampleData = [
            ['BRG001', 'Buku Tulis', 'pcs', '5000', '7500', '100', '10', '1.1.3.1', 'TRUE'],
            ['BRG002', 'Pulpen', 'pcs', '3000', '5000', '200', '20', '1.1.3.1', 'TRUE'],
            ['BRG003', 'Kertas A4', 'rim', '35000', '45000', '50', '5', '1.1.3.1', 'TRUE'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');
        
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        $instructions = [
            ['Column', 'Required?', 'Type', 'Valid Values', 'Description'],
            ['Code', 'Yes', 'Text', 'Max 20 chars, unique', 'Kode barang'],
            ['Name', 'Yes', 'Text', 'Max 255 chars', 'Nama barang'],
            ['Unit', 'Yes', 'Text', 'pcs, kg, liter, etc', 'Satuan'],
            ['Cost', 'No', 'Decimal', '>= 0', 'Harga beli (default 0)'],
            ['Price', 'No', 'Decimal', '>= 0', 'Harga jual (default 0)'],
            ['Stock', 'No', 'Integer', '>= 0', 'Stok awal (default 0)'],
            ['Min Stock', 'No', 'Integer', '>= 0', 'Stok minimal (default 0)'],
            ['COA Code', 'No', 'Text', 'Must exist in COA', 'Kode akun persediaan'],
            ['Is Active', 'No', 'Boolean', 'TRUE, FALSE', 'Status (default TRUE)'],
        ];
        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        foreach (range('A', 'E') as $col) {
            $instructionsSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'inventory_template_' . date('Ymd') . '.xlsx';
        
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

        if (!$user->canEdit()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk import inventory.'], 403);
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
                    $unit = trim($row[2] ?? '');
                    $cost = trim($row[3] ?? '0');
                    $price = trim($row[4] ?? '0');
                    $stock = trim($row[5] ?? '0');
                    $minStock = trim($row[6] ?? '0');
                    $coaCode = trim($row[7] ?? '');
                    $isActive = strtoupper(trim($row[8] ?? 'TRUE')) === 'TRUE';

                    if (empty($code) || empty($name) || empty($unit)) {
                        throw new \Exception('Code, Name, and Unit are required');
                    }

                    $exists = Inventory::where('company_id', $company->id)
                        ->where('code', $code)
                        ->exists();

                    if ($exists) {
                        throw new \Exception('Code already exists');
                    }

                    $coaId = null;
                    if (!empty($coaCode)) {
                        $coa = ChartOfAccount::where('company_id', $company->id)
                            ->where('code', $coaCode)
                            ->first();

                        if (!$coa) {
                            throw new \Exception("COA code '$coaCode' not found");
                        }
                        $coaId = $coa->id;
                    }

                    $inventory = Inventory::create([
                        'company_id' => $company->id,
                        'coa_id' => $coaId,
                        'code' => $code,
                        'name' => $name,
                        'unit' => $unit,
                        'cost' => (float)$cost,
                        'price' => (float)$price,
                        'stock' => (int)$stock,
                        'min_stock' => (int)$minStock,
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

        $items = Inventory::where('company_id', $company->id)
            ->with('account')
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory');
        
        $headers = ['Code', 'Name', 'Unit', 'Cost', 'Price', 'Stock', 'Min Stock', 'COA Code', 'Is Active'];
        $sheet->fromArray($headers, null, 'A1');
        
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        $rowIndex = 2;
        foreach ($items as $item) {
            $sheet->fromArray([
                $item->code,
                $item->name,
                $item->unit,
                $item->cost,
                $item->price,
                $item->stock,
                $item->min_stock,
                $item->account ? $item->account->code : '',
                $item->is_active ? 'TRUE' : 'FALSE',
            ], null, "A{$rowIndex}");
            $rowIndex++;
        }
        
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'inventory_export_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
