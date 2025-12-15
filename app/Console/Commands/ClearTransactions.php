<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-transactions {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all test transactions from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Show warning
        $this->warn('⚠️  WARNING: This will delete ALL transactions from the database!');
        $this->newLine();
        
        $this->line('The following data will be DELETED:');
        $this->line('  • All Journal Entries and Journal Items');
        $this->line('  • All Invoices (Sales & Purchase) and Invoice Items');
        $this->newLine();
        
        $this->line('The following data will be PRESERVED:');
        $this->line('  ✓ Master Data (Companies, Business Units, Chart of Accounts)');
        $this->line('  ✓ Contacts (Customers, Suppliers)');
        $this->line('  ✓ Inventories and Fixed Assets');
        $this->line('  ✓ User Accounts');
        $this->newLine();

        // Confirm deletion unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to continue?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting transaction cleanup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // Count records before deletion
            $invoiceItemsCount = DB::table('invoice_items')->count();
            $invoicesCount = DB::table('invoices')->count();
            $journalItemsCount = DB::table('journal_items')->count();
            $journalsCount = DB::table('journals')->count();

            // Delete in correct order (child tables first)
            $this->info('Deleting invoice items...');
            DB::table('invoice_items')->delete();
            
            $this->info('Deleting invoices...');
            DB::table('invoices')->delete();
            
            $this->info('Deleting journal items...');
            DB::table('journal_items')->delete();
            
            $this->info('Deleting journals...');
            DB::table('journals')->delete();

            DB::commit();

            $this->newLine();
            $this->info('✅ Transaction cleanup completed successfully!');
            $this->newLine();
            
            // Show summary
            $this->table(
                ['Table', 'Records Deleted'],
                [
                    ['Invoice Items', $invoiceItemsCount],
                    ['Invoices', $invoicesCount],
                    ['Journal Items', $journalItemsCount],
                    ['Journals', $journalsCount],
                    ['TOTAL', $invoiceItemsCount + $invoicesCount + $journalItemsCount + $journalsCount],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error('❌ Error clearing transactions: ' . $e->getMessage());
            return 1;
        }
    }
}
