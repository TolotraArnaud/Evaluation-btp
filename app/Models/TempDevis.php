<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TempDevis extends Model
{
    use HasFactory;

    protected $fillable = ['client','ref_devis','type_maison','finition','taux_finition','date_devis','date_debut','lieu'];

    public static function insertCollectionData($donnees)
    {
        // Préparer les données à insérer dans le bon format
        $donneesAInserer = $donnees->map(function ($element) {
            static $line = 0;
            $line++;
            return [
                'line' => $line,
                'client' => $element['client'],
                'ref_devis' => $element['ref_devis'],
                'type_maison' => $element['type_maison'],
                'finition' => $element['finition'],
                'taux_finition' => floatval(str_replace(',','.',$element['taux_finition'])),
                'date_devis' => $element['date_devis'],
                'date_debut' => $element['date_debut'],
                'lieu' => $element['lieu']
            ];
        })->toArray();
        // Insérer les données dans la base de données
        TempDevis::insert($donneesAInserer);
        // dd($donneesAInserer);
    }

    public static function checkDataCoherence($data) {
        $error = array();
        // Check if duree is more than 03h
        // $dureeSuperieure = Temp::where('horaire', '>', '03:00:00')->get();
        // // dd(Temp::all());
        // if (sizeof($dureeSuperieure) > 0) {
        //     // dd($dureeSuperieure);
        //     $message = "La duree des films ne devrait pas depasser les 03h. Lignes : ";
        //     foreach ($dureeSuperieure as $item) {
        //         $message.= $item->line.",";
        //     }
        //     $error['duree'] = $message;
        // }
        /* Autre check, ajouter dans la variable si erreur */

        return $error;
    }


    public static function insertDataFromTable(bool $commitable) {
        // Insérer les données distinctes de la table temps dans les tables film et salle
        if ($commitable) {
            DB::beginTransaction();
        }
        try{
            Client::insert(
                DB::table('temp_devis')->select('client')->distinct()->get()->map(function ($item) {
                    return [
                    'tel' => $item->client
                ];
                })->toArray()
            );
            $clients = Client::all();
            // dd($types);
            // $test = DB::table('temps')->select('film')->distinct()->get()->map(function ($item) {
            //     return ['titre' => $item->film];
            // })->toArray();
            // dd($test);

            Finition::insert(
                DB::table('temp_devis')->select('finition', 'taux_finition')->distinct()->get()->map(function ($item) {
                    return [
                    'designation' => $item->finition,
                    'percent' => $item->taux_finition,
                ];
                })->toArray()
            );
            foreach ($clients as $key => $client) {
                # code...
                Devis::insert(
                    DB::table('temp_devis')
                    ->select('temp_devis.ref_devis','type_maisons.id as idtypemaison', 'finitions.id as idfinition','temp_devis.date_devis', 'temp_devis.date_debut', 'temp_devis.lieu')
                    ->join('type_maisons', 'type_maisons.designation', '=', 'temp_devis.type_maison')
                    ->join('finitions', 'finitions.designation', '=', 'temp_devis.finition')
                    ->where('temp_devis.client', '=', $client->tel)->get()->map(function ($item) use ($client){
                        return [
                            'idclient' => $client->id,
                            'ref_devis' => $item->ref_devis,
                            'idtypemaison' => $item->idtypemaison,
                            'idfinition' => $item->idfinition,
                            'date_devis' => $item->date_devis,
                            'date_debut' => $item->date_debut,
                            'lieu' => $item->lieu
                        ];
                    })->toArray()
                );
            }


            $devis = Devis::all();
            foreach ($devis as $devi) {
                $date = Carbon::parse($devi->date_debut);
                $date = $date->addDays($devi->type_maison->duree);
                $devi->date_fin = $date;
                $devi->pu = $devi->getTotalMontant() + (($devi->getTotalMontant() * $devi->finition->percent) / 100);
                $devi->save();
                $devi->saveDetails();
            }
            // Récupérer les id_film et id_salle correspondant aux noms de film et de salle dans temps


            // Insérer les données dans la table seance
            // Seance::insert($seances->toArray());

            if ($commitable) {
                // DB::delete('delete from maison_travails');
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

    public static function importDataToDB($data, $commitable) {
        if ($commitable) {
            DB::beginTransaction();
            # code...
        }
        try {
            TempDevis::insertCollectionData($data);
            $errors = TempDevis::checkDataCoherence($data);
            if (sizeof($errors) > 0) {
                if ($commitable) {
                    DB::rollBack();
                    # code...
                }
            } else {
                TempDevis::insertDataFromTable(false);
                if ($commitable) {
                    DB::commit();
                }
            }
            return $errors;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }
}
