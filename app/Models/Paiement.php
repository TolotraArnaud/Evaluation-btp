<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Paiement extends Model
{
    use HasFactory;
    protected $fillable = ['idclient', 'iddevis', 'montant', 'date_paiement', 'ref_paiement'];

    public static function getNextRefNumber()
    {
        $nextVal = DB::select("SELECT nextval('seq_paiements')")[0]->nextval;
        return 'P' . str_pad($nextVal, 4, '0', STR_PAD_LEFT);
    }
}
