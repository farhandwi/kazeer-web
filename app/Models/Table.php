<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'table_number', 'table_code', 'qr_code_path', 'capacity', 
        'status', 'description'
    ];

    protected $appends = ['qr_code_url'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class);
    }

    public function currentSession()
    {
        return $this->sessions()->where('status', 'active')->latest()->first();
    }

    /**
     * Generate unique table code if not exists
     */
    public function generateTableCode()
    {
        if (empty($this->table_code)) {
            $this->table_code = 'TBL_' . $this->restaurant_id . '_' . $this->table_number . '_' . Str::random(6);
            $this->save();
        }
        return $this->table_code;
    }

    /**
     * Generate QR Code with order link
     */
    public function generateQRCode($size = 300)
    {
        // Ensure table code exists
        if (empty($this->table_code)) {
            $this->generateTableCode();
        }

        // Create the order URL
        $orderUrl = config('app.url') . '/order?table=' . $this->table_code;
        
        // Generate QR code
        $qrCode = QrCode::format('png')
                        ->size($size)
                        ->margin(2)
                        ->errorCorrection('M')
                        ->generate($orderUrl);

        // Create filename
        $filename = 'qrcodes/table_' . $this->id . '_' . time() . '.png';
        
        // Save QR code to storage
        Storage::disk('public')->put($filename, $qrCode);
        
        // Delete old QR code if exists
        if ($this->qr_code_path && Storage::disk('public')->exists($this->qr_code_path)) {
            Storage::disk('public')->delete($this->qr_code_path);
        }
        
        // Update model
        $this->qr_code_path = $filename;
        $this->save();
        
        return $this->qr_code_path;
    }

    /**
     * @return string|null
     */
    public function getQrCodeUrlAttribute()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        if ($this->qr_code_path && $disk->exists($this->qr_code_path)) {
            return $disk->url($this->qr_code_path);
        }
        return null;
    }


    /**
     * Get order URL for this table
     */
    public function getOrderUrl()
    {
        if (empty($this->table_code)) {
            $this->generateTableCode();
        }
        return config('app.url') . '/order?table=' . $this->table_code;
    }

    /**
     * Regenerate QR code (useful for updating existing tables)
     */
    public function regenerateQRCode($size = 300)
    {
        return $this->generateQRCode($size);
    }

    /**
     * Boot method to auto-generate table code when creating new table
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($table) {
            if (empty($table->table_code)) {
                $table->table_code = 'TBL_' . $table->restaurant_id . '_' . $table->table_number . '_' . Str::random(6);
            }
        });
        
        static::created(function ($table) {
            // Auto generate QR code after table is created
            $table->generateQRCode();
        });
    }
}