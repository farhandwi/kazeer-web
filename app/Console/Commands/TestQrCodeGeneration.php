<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class TestQrCodeGeneration extends Command
{
    protected $signature = 'qr:test {--url=https://example.com : URL to encode in QR code}';
    protected $description = 'Test QR code generation functionality';

    public function handle()
    {
        $this->info('Testing QR Code generation...');

        // Test 1: Check if package is available
        $this->info('1. Checking SimpleSoftwareIO QrCode package...');
        if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            $this->error('❌ SimpleSoftwareIO QrCode package not found!');
            $this->info('Install with: composer require simplesoftwareio/simple-qrcode');
            return 1;
        }
        $this->info('✅ SimpleSoftwareIO QrCode package is available');

        // Test 2: Check storage
        $this->info('2. Checking storage configuration...');
        try {
            $disk = Storage::disk('public');
            if (!$disk->exists('')) {
                $this->error('❌ Public storage not accessible. Run: php artisan storage:link');
                return 1;
            }
            $this->info('✅ Public storage is accessible');
        } catch (\Exception $e) {
            $this->error('❌ Storage error: ' . $e->getMessage());
            return 1;
        }

        // Test 3: Generate test QR code
        $this->info('3. Generating test QR code...');
        try {
            $url = $this->option('url');
            $qrCode = QrCode::format('png')
                           ->size(200)
                           ->margin(1)
                           ->errorCorrection('M')
                           ->generate($url);

            $filename = 'test_qr_' . time() . '.png';
            Storage::disk('public')->put($filename, $qrCode);
            
            $this->info('✅ QR code generated successfully: ' . $filename);
            $this->info('📍 File location: ' . storage_path('app/public/' . $filename));
            $this->info('🌐 URL: ' . Storage::url($filename));

            // Clean up test file
            if ($this->confirm('Delete test QR code file?')) {
                Storage::disk('public')->delete($filename);
                $this->info('Test file deleted.');
            }

        } catch (\Exception $e) {
            $this->error('❌ QR code generation failed: ' . $e->getMessage());
            return 1;
        }

        // Test 4: Check different formats
        $this->info('4. Testing different QR code formats...');
        $formats = ['png', 'svg'];
        foreach ($formats as $format) {
            try {
                $qrCode = QrCode::format($format)
                               ->size(100)
                               ->generate('test-' . $format);
                
                $this->info("✅ {$format} format: OK");
            } catch (\Exception $e) {
                $this->error("❌ {$format} format failed: " . $e->getMessage());
            }
        }

        $this->info('');
        $this->info('🎉 QR Code functionality test completed!');
        return 0;
    }
}