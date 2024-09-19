<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinitionRequest;
use App\Http\Requests\TravauxRequest;
use App\Models\Devis;
use App\Models\Finition;
use App\Models\Paiement;
use App\Models\Travail;
use App\Models\Unite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request) {
        $devis = Devis::all();
        $total = Devis::sumAll($devis);
        $totalPaye = Paiement::sum('montant');
        $annees = $this->getAnneeDevis();
        if($request->has('annee')){
            $aivr = $request->input('annee');
            $annee = intval($aivr);
        } else {
            if (sizeof($annees) > 0) {
                $aivr = ($this->getAnneeDevis()[0]);
                $annee = intval($aivr->annee);
            }
        }


        // dd($annees);
        $moisArray = array();
        $montantMois = array();
        if (sizeof($annees) > 0) {
            # code...
            $results = DB::select("
            SELECT
                TO_CHAR(date_devis, 'Month') AS mois,
                SUM(pu) AS total,
                TO_CHAR(date_devis, 'YYYY') AS Annee
            FROM
                devis
            WHERE
                TO_CHAR(date_devis, 'YYYY') = ?
            GROUP BY
                TO_CHAR(date_devis, 'Month'), date_devis
            ORDER BY
                TO_CHAR(date_devis, 'MM');
            ",[$annee]);
            foreach ($results as $row) {
                $mois = $row->mois;
                $montant = $row->total;
                $montantMois[] = $montant;
                $moisArray[] = $mois;
            }
        }



        $montantTotal = $this->totalMontant();
        // dd($montantTotal);
        $paiement = $this->totalPaiement();

        return view('cinema.salle',[
            'devis' => $devis,
            'mois' => json_encode($moisArray),
            'montantMois' => json_encode($montantMois),
            'totalDevis' => $total,
            'totalPaye' => $totalPaye,
            'annee'=> $annees,
            'current_year' => $annee
        ]);
    }
    // public function index(Request $request) {
    //     $devis = Devis::all();
    //     $total = Devis::sumAll($devis);
    //     $totalPaye = Paiement::sum('montant');
    //     $results = DB::table('devis')
    //     ->select(DB::raw('to_char(date_debut, \'Month\') AS mois'), DB::raw('SUM(pu) as total'))
    //     ->groupBy(DB::raw('to_char(date_debut, \'Month\')'), 'date_debut')
    //     ->orderBy(DB::raw('to_char(date_debut, \'MM\')'))
    //     ->get();
    //     // dd($results);
    //     $mois = $results->pluck('mois')->toArray();
    //     $montantMois = $results->pluck('total')->toArray();
    //     // dd($montantMois);
    //     return view('cinema.salle', [
    //         'devis' => $devis,
    //         'totalDevis' => $total,
    //         'totalPaye' => $totalPaye,
    //         'mois' => json_encode($mois),
    //         'montantMois' => json_encode($montantMois)
    //     ]);
    // }

    public function viewAnyDevis() {
        $devis = Devis::paginate(6);
        return view('pages.devis', ['devis' => $devis]);
    }
    public function viewDevis(Devis $devis) {
        return view('pages.devis.detail', [
            'devis' => $devis
        ]);
    }

    public function viewAnyTravaux() {
        $travaux = Travail::orderBy('numero')->paginate(5);
        return view('pages.data.type',[
            'types' => $travaux,
            'unites' => Unite::all()
        ]);
    }

    public function setTravail(TravauxRequest $request) {
        $travail = Travail::findOrFail($request->input('idtravail'));
        $credentials = $request->validated();
        $travail->numero = $credentials['numero'];
        $travail->designation = $credentials['designation'];
        $travail->pu = $credentials['pu'];
        $travail->idunite = $credentials['unite'];
        $travail->save();
        return to_route('travail.list');
    }

    public function viewAnyFinitions() {
        $finitions = Finition::orderBy('id')->paginate(5);
        return view('pages.data.finition',[
            'finitions' => $finitions
        ]);
    }

    public function setFinition(FinitionRequest $request) {
        $finition = Finition::findOrFail($request->input('idfinition'));
        $credentials = $request->validated();
        $finition->designation = $credentials['designation'];
        $finition->percent = $credentials['percent'];
        $finition->save();
        return to_route('finition.list');
    }

    public function resetDB() {
        DB::statement('TRUNCATE TABLE temp_paiements RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE temp_devis RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE maison_travails RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE paiements RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE devis RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE finitions RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE detail_travails RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE type_maisons RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE travails RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE unites RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE clients RESTART IDENTITY CASCADE');
        DB::statement("ALTER SEQUENCE seq_devis START 1");
        DB::statement("ALTER SEQUENCE seq_paiements START 1");
        return to_route('home');
    }

    // Fonction liee histogramme
    public function getAnneeDevis(){

        $resultsAnnee = DB::table('devis')
        ->select(DB::raw("to_char(date_devis, 'YYYY') AS annee"))
        ->groupBy(DB::raw("to_char(date_devis, 'YYYY')"))
        ->orderBy(DB::raw("to_char(date_devis, 'YYYY')"))
        ->get();

        return $resultsAnnee;
    }

    public function totalMontant(){
        $alldevis = Devis::all();
        $total = Devis::sumAll($alldevis);
        return $total;
    }
    public function totalPaiement(){
        $paiement = Paiement::sum('montant');
        return $paiement;
    }

}
