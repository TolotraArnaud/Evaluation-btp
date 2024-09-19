<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTravail extends Model
{
    use HasFactory;

    protected $fillable = ['idtypemaison', 'idtravail', 'qte', 'pu'];

    public function type_maison() {
        return $this->belongsTo(TypeMaison::class, 'idtypemaison');
    }

    public function travail() {
        return $this->belongsTo(Travail::class, 'idtravail');
    }
}
