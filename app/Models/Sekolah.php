<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\HasOne; 
use App\Observers\SekolahObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([SekolahObserver::class])]
class Sekolah extends Model
{
    protected $fillable = [
        'npsn', 'nama', 'jenjang', 'status', 'alamat',
        'kecamatan', 'telepon', 'latitude', 'longitude',
    ];

    public const BATAS_GESER_METER = 500;

    protected function casts(): array
    {
        return [
            'latitude'  => 'float',
            'longitude' => 'float',
        ];
    }

    public function profil(): HasOne 
    { 
        return $this->hasOne(ProfilSekolah::class); 
    } 
    
    public function users(): HasMany 
    { 
        return $this->hasMany(User::class); 
    } 
}