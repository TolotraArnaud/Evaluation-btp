<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Temp extends Model
{
    use HasFactory;

    protected $fillable = ['salle', 'film', 'horaire', 'duree'];

    public static function insertCollectionData($donnees)
    {
        // Préparer les données à insérer dans le bon format
        $donneesAInserer = $donnees->map(function ($element) {
            static $line = 0;
            $line++;
            return [
                'line' => $line,
                'salle' => $element['salle'],
                'film' => $element['film'],
                'horaire' => $element['horaire'],
                'duree' => $element['duree'],
            ];
        })->toArray();
        // Insérer les données dans la base de données
        Temp::insert($donneesAInserer);
        // dd($donneesAInserer);
    }

    public static function checkDataCoherence($data) {
        $error = array();
        // Check if duree is more than 03h
        $dureeSuperieure = Temp::where('horaire', '>', '03:00:00')->get();
        // dd(Temp::all());
        if (sizeof($dureeSuperieure) > 0) {
            // dd($dureeSuperieure);
            $message = "La duree des films ne devrait pas depasser les 03h. Lignes : ";
            foreach ($dureeSuperieure as $item) {
                $message.= $item->line.",";
            }
            $error['duree'] = $message;
        }
        /* Autre check, ajouter dans la variable si erreur */

        return $error;
    }

    public static function importDataToDB($data) {
        DB::beginTransaction();
        try {
            Temp::insertCollectionData($data);
            $errors = Temp::checkDataCoherence($data);
            if (sizeof($errors) > 0) {
                DB::rollBack();
            } else {
                Temp::insertDataFromTable(false);
                DB::commit();
            }
            return $errors;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public static function insertDataFromTable(bool $commitable) {
        // Insérer les données distinctes de la table temps dans les tables film et salle
        if ($commitable) {
            DB::beginTransaction();
        }
        try{
            Film::insert(
                DB::table('temps')->select('film', 'horaire')->distinct()->get()->map(function ($item) {
                    return [
                    'titre' => $item->film,
                    'duree' => $item->horaire
                ];
                })->toArray()
            );

            // $test = DB::table('temps')->select('film')->distinct()->get()->map(function ($item) {
            //     return ['titre' => $item->film];
            // })->toArray();
            // dd($test);

            Salle::insert(
                DB::table('temps')->select('salle')->distinct()->get()->map(function ($item) {
                    return ['designation' => $item->salle];
                })->toArray()
            );

            // Récupérer les id_film et id_salle correspondant aux noms de film et de salle dans temps

            $seances = DB::table('temps')
            ->join('films', 'films.titre', '=', 'temps.film')
            ->join('salles', 'salles.designation', '=', 'temps.salle')
            ->select('films.id as id_film', 'salles.id as id_salle', 'temps.horaire', 'temps.duree')
            ->get()
            ->map(function ($item) {
                return [
                    'idfilm' => $item->id_film,
                    'idsalle' => $item->id_salle,
                    'heure_diffusion' => $item->duree
                ];
            });


            // Insérer les données dans la table seance
            Seance::insert($seances->toArray());
            if ($commitable) {
                DB::delete('delete from temps');
                DB::commit();
            }
            // dd($seances);

        } catch(Exception $e){
            if ($commitable) {
                DB::rollBack();
            }
            throw $e;
        }
    }
}
