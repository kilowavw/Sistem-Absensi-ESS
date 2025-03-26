<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'aktivitas',
        'lokasi',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Hitung total jam kerja (jam & menit)
    public function getTotalHoursAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            return $this->clock_in->diff($this->clock_out)->format('%H:%I');
        }
        return '0:00';
    }

    // Format aktivitas singkat untuk kalender
    public function getShortActivityAttribute()
    {
        return str()->limit($this->aktivitas, 20, '...');
    }

    // Format tanggal dalam bahasa Indonesia
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->translatedFormat('d M Y');
    }
}
