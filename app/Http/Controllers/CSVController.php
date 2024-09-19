<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Helper;
use App\Models\MaisonTravail;
use App\Models\Temp;
use App\Models\TempDevis;
use App\Models\TempPaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CSVController extends Controller
{
    public function index() {
        return view('cinema.salle');
    }

    public function list() {
        // $films = ::paginate(4);
        // $films->setOptions(['text' => 'Affichage de {start} a {end} sur {total} resultats']);
        // return view('pages.list', ['films' => $films]);
    }

    public function forms() {
        return view('pages.form');
    }

    public function maisonDevisImport() {
        return view('pages.import.maisondevis');
    }

    public function importTypeMaisonDevis(Request $request) {
        $request->validate([
            'maison_travaux' => 'required|file|max:2048',
            'devis' => 'required|file|max:2048'
        ]);
        // $data = $request->validate('data');
        $file_maison_travaux = $request->file('maison_travaux');
        $devisFile = $request->file('devis');

        // Déplacer le fichier téléchargé vers un emplacement temporaire
        $file_maison_travaux->move(storage_path('app'), 'temp_maison_travaux.csv');
        $devisFile->move(storage_path('app'), 'temp_devis.csv');

        // Chemin vers le fichier temporaire
        $pathFile = storage_path('app/temp_maison_travaux.csv');
        $pathDevis = storage_path('app/temp_devis.csv');
        $data_exploded_maisontravaux = Helper::CSVtoCollection($pathFile);
        $data_exploded_devis = Helper::CSVtoCollection($pathDevis);
        // dd($data_exploded);
        // Temp::checkDataCoherence($data_exploded);
        // $errors = Temp::importDataToDB($data_exploded);
        // if (sizeof($errors) > 0) {
        //     return to_route('forms')->withErrors($errors);
        // }
        // MaisonTravail::insertCollectionData($data_exploded_maisontravaux);
        // MaisonTravail::insertDataFromTable(true);
        // TempDevis::insertCollectionData($data_exploded_devis);
        // TempDevis::insertDataFromTable(true);
        DB::beginTransaction();
        $errors = array();
        $temp_error = MaisonTravail::importDataToDB($data_exploded_maisontravaux,true);  if
       (sizeof($temp_error) > 0) {
            array_push($errors, $temp_error);
            $temp_error = null;
            $temp_error = array();
        }
        $temp_error = TempDevis::importDataToDB($data_exploded_devis,true);
        if (sizeof($temp_error) > 0) {
            array_push($errors, $temp_error);
            $temp_error = null;
            $temp_error = array();
        }
        if (sizeof($errors) > 0) {
            // dd($errors);
            DB::rollBack();
            return to_route('import.form.maison.devis')->withErrors($errors);
        } else {
            DB::commit();
        }
        // dd($data_exploded_devis);
        // Temp::insertDataFromTable();
        return to_route('import.form.maison.devis');
    }

    public function paiementImport() {
        return view('pages.import.paiement');
    }

    public function importPaiement(Request $request) {
        $request->validate([
            'paiements' => 'required|file|max:2048'
        ]);
        // $data = $request->validate('data');
        $file_maison_travaux = $request->file('paiements');

        // Déplacer le fichier téléchargé vers un emplacement temporaire
        $file_maison_travaux->move(storage_path('app'), 'temp_paiement.csv');

        // Chemin vers le fichier temporaire
        $pathFile = storage_path('app/temp_paiement.csv');
        $data_exploded_paiement = Helper::CSVtoCollection($pathFile);
        // dd($data_exploded);
        // Temp::checkDataCoherence($data_exploded);
        // $errors = Temp::importDataToDB($data_exploded);
        // if (sizeof($errors) > 0) {
        //     return to_route('forms')->withErrors($errors);
        // }
        // TempPaiement::insertCollectionData($data_exploded_paiement);
        // TempPaiement::insertDataFromTable(true);
        DB::beginTransaction();
        $errors = TempPaiement::importDataToDB($data_exploded_paiement, false);
        if (sizeof($errors) > 0) {
            DB::rollBack();
            return to_route('import.form.paiement')->withErrors($errors);
        } else {
            DB::commit();
        }
            // dd($data_exploded_paiement);
        // Temp::insertDataFromTable();
        return to_route('import.form.paiement');
    }
}
