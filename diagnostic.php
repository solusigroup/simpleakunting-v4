<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==================================\n";
echo "DATABASE DIAGNOSTIC RESULTS\n";
echo "==================================\n\n";

// 1. Count records
echo "1. RECORD COUNTS:\n";
echo "   Total Invoices: " . DB::table('invoices')->count() . "\n";
echo "   Total Journals: " . DB::table('journals')->count() . "\n";
echo "   Posted Journals: " . DB::table('journals')->where('is_posted', true)->count() . "\n";
echo "   Total Journal Items: " . DB::table('journal_items')->count() . "\n";
echo "\n";

// 2. Last invoice
echo "2. LAST INVOICE:\n";
$invoice = DB::table('invoices')->orderBy('id', 'desc')->first();
if ($invoice) {
    echo "   ID: {$invoice->id}\n";
    echo "   Number: {$invoice->invoice_number}\n";
    echo "   Type: {$invoice->type}\n";
    echo "   Date: {$invoice->date}\n";
    echo "   Total: {$invoice->total}\n";
    echo "   Status: {$invoice->status}\n";
    echo "   Journal ID: " . ($invoice->journal_id ?? 'NULL') . "\n";
    echo "   Business Unit ID: " . ($invoice->business_unit_id ?? 'NULL') . "\n";
} else {
    echo "   No invoices found\n";
}
echo "\n";

// 3. All journals
echo "3. ALL JOURNALS:\n";
$journals = DB::table('journals')->orderBy('id', 'desc')->get();
foreach ($journals as $j) {
    echo "   ID: {$j->id} | Date: {$j->date} | Ref: {$j->reference} | Posted: " . ($j->is_posted ? 'YES' : 'NO') . " | Source: {$j->source}\n";
}
echo "\n";

// 4. Journal items from last journal
echo "4. LAST JOURNAL ITEMS:\n";
if ($invoice && $invoice->journal_id) {
    $items = DB::table('journal_items as ji')
        ->join('chart_of_accounts as coa', 'ji.coa_id', '=', 'coa.id')
        ->where('ji.journal_id', $invoice->journal_id)
        ->select('coa.code', 'coa.name', 'ji.debit', 'ji.credit', 'ji.memo')
        ->get();
    
    foreach ($items as $item) {
        echo "   {$item->code} - {$item->name}\n";
        echo "      Debit: {$item->debit} | Credit: {$item->credit}\n";
        echo "      Memo: {$item->memo}\n";
    }
} else {
    echo "   No journal items found\n";
}
echo "\n";

// 5. Trial Balance Summary
echo "5. TRIAL BALANCE SUMMARY (All Accounts):\n";
$summary = DB::table('journal_items as ji')
    ->join('journals as j', 'ji.journal_id', '=', 'j.id')
    ->join('chart_of_accounts as coa', 'ji.coa_id', '=', 'coa.id')
    ->where('j.is_posted', true)
    ->select('coa.code', 'coa.name', 
        DB::raw('SUM(ji.debit) as total_debit'),
        DB::raw('SUM(ji.credit) as total_credit'),
        DB::raw('(SUM(ji.debit) - SUM(ji.credit)) as balance')
    )
    ->groupBy('coa.id', 'coa.code', 'coa.name')
    ->havingRaw('(SUM(ji.debit) - SUM(ji.credit)) != 0')
    ->orderBy('coa.code')
    ->get();

foreach ($summary as $s) {
    $bal = number_format($s->balance, 2);
    echo "   {$s->code} - {$s->name}\n";
    echo "      Debit: " . number_format($s->total_debit, 2) . " | Credit: " . number_format($s->total_credit, 2) . " | Balance: {$bal}\n";
}

exit(0);
