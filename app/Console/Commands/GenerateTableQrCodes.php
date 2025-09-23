<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Table;
use Illuminate\Support\Facades\Storage;

class GenerateTableQrCodes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tables:generate-qr {--all : Generate QR codes for all tables} {--missing : Generate QR codes only for tables without QR codes} {--table=* : Generate QR codes for specific table IDs}';

    /**
     * The console command description.
     */
    protected $description = 'Generate QR codes for restaurant tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting QR code generation...');

        // Check if SimpleSoftwareIO QrCode is available
        if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            $this->error('SimpleSoftwareIO QrCode package is not installed!');
            $this->info('Please install it with: composer require simplesoftwareio/simple-qrcode');
            return 1;
        }

        // Check if storage is properly configured
        if (!Storage::disk('public')->exists('')) {
            $this->error('Public storage disk is not accessible!');
            $this->info('Please run: php artisan storage:link');
            return 1;
        }

        // Create qrcodes directory if it doesn't exist
        if (!Storage::disk('public')->exists('qrcodes')) {
            Storage::disk('public')->makeDirectory('qrcodes');
            $this->info('Created qrcodes directory in storage/app/public/');
        }

        $tables = $this->getTablesQuery();
        $this->info("Found {$tables->count()} tables to process");

        $success = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($tables->count());
        $bar->start();

        foreach ($tables as $table) {
            try {
                // Skip if table already has QR code and we're only generating missing ones
                if ($this->option('missing') && $table->qr_code_path && Storage::disk('public')->exists($table->qr_code_path)) {
                    $bar->advance();
                    continue;
                }

                $table->generateQRCode();
                $success++;
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to generate QR code for Table ID {$table->id}: " . $e->getMessage());
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("QR code generation completed!");
        $this->info("✅ Success: {$success} tables");
        if ($failed > 0) {
            $this->error("❌ Failed: {$failed} tables");
        }

        return 0;
    }

    private function getTablesQuery()
    {
        if ($this->option('all')) {
            return Table::all();
        }

        if ($this->option('missing')) {
            return Table::where(function ($query) {
                $query->whereNull('qr_code_path')
                      ->orWhere('qr_code_path', '');
            })->get();
        }

        if ($tableIds = $this->option('table')) {
            return Table::whereIn('id', $tableIds)->get();
        }

        // Default: generate for missing QR codes
        return Table::where(function ($query) {
            $query->whereNull('qr_code_path')
                  ->orWhere('qr_code_path', '');
        })->get();
    }
}