<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Devis extends Model
{
    use HasFactory;

    protected $fillable = ['idclient', 'idtypemaison', 'idfinition', 'pu', 'percent', 'date_debut', 'date_fin', 'date_devis', 'ref_devis', 'lieu'];

    public function saveDetails() {
        $details = $this->type_maison->details;
        foreach ($details as $key => $detail) {
            DetailDevis::create([
                'iddevis' => $this->id,
                'idtravail' => $detail->idtravail,
                'idunite' => $detail->travail->idunite,
                'qte' => $detail->qte,
                'pu' => $detail->travail->pu
            ]);
        }
    }

    public static function sumAll($devis) {
        $sum = 0;
        foreach ($devis as $devi) {
            $sum += $devi->pu;
        }
        return number_format($sum,2,',',' ');
    }

    public function getEtatPaiement() {
        if ($this->getTotalPaye() == $this->getTotalMontant()) {
            return 'check';
        }
        return 'close';
    }

    public function getPayPercent() {
        $total = $this->pu; /* 100 % */
        $paye = $this->getTotalPaye(); /* ? % */
        return number_format(($paye/$total)*100, 2);
    }

    public function getTotalPaye() {
        $paiements = $this->paiements;
        $total = 0;
        foreach ($paiements as $key => $paiement) {
            $total += $paiement->montant;
        }
        return $total;
    }

    public function getStyleTd() {
        $percent = $this->getPayPercent();
        if ($percent < 50) {
            return 'danger';
        } else if ($percent > 50) {
            return 'success';
        }
        return '';
    }

    public function getTotalPayeFormatted() {
        return number_format($this->getTotalPaye(),2,',',' ');
    }

    public function getTotalMontant() {
        return $this->type_maison->getTotalMontant();
    }

    public function getTotalFormated() {
        return number_format($this->getTotalMontant(),2,',',' ');
    }

    public function getDenormalizeTotal() {
        return number_format($this->pu,2,',',' ');
    }

    public static function getNextRefNumber()
    {
        $nextVal = DB::select("SELECT nextval('seq_devis')")[0]->nextval;
        return 'D' . str_pad($nextVal, 3, '0', STR_PAD_LEFT);
    }

    public function paiements() {
        return $this->hasMany(Paiement::class, 'iddevis');
    }

    public function details() {
        return $this->hasMany(DetailDevis::class, 'iddevis');
    }

    public function client() {
        return $this->belongsTo(Client::class, 'idclient');
    }

    public function type_maison() {
        return $this->belongsTo(TypeMaison::class, 'idtypemaison');
    }

    public function finition() {
        return $this->belongsTo(Finition::class, 'idfinition');
    }
}
