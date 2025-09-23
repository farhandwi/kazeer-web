<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Table;

class RegenerateTableQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tables:regenerate-qr {--size=300 : QR code size in pixels}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate QR codes for all tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $size = (int) $this->option('size');
        $tables = Table::all();
        
        if ($tables->isEmpty()) {
            $this->info('No tables found.');
            return;
        }

        $this->info("Regenerating QR codes for {$tables->count()} tables...");
        
        $bar = $this->output->createProgressBar($tables->count());
        $bar->start();

        foreach ($tables as $table) {
            try {
                // Generate table code if not exists
                if (empty($table->table_code)) {
                    $table->generateTableCode();
                }
                
                // Generate QR code
                $table->generateQRCode($size);
                
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to generate QR code for table {$table->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('QR codes regenerated successfully!');
    }
}