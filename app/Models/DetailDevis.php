<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailDevis extends Model
{
    use HasFactory;

    protected $fillable = ['iddevis', 'idtravail', 'idunite', 'qte', 'pu'];

    public function travail() {
        return $this->belongsTo(Travail::class, 'idtravail');
    }

    public function unite() {
        return $this->belongsTo(Unite::class, 'idunite');
    }
}
