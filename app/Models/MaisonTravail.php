<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MaisonTravail extends Model
{
    use HasFactory;

    protected $fillable = ['type_maison','description','surface','code_travaux','type_travaux','unite','pu','qte','duree_travaux'];

    public static function insertCollectionData($donnees)
    {
        // Préparer les données à insérer dans le bon format
        $donneesAInserer = $donnees->map(function ($element) {
            static $line = 0;
            $line++;
            return [
                'line' => $line,
                'type_maison' => $element['type_maison'],
                'description' => trim($element['description']),
                'surface' => intval($element['surface']),
                'code_travaux' => $element['code_travaux'],
                'type_travaux' => $element['type_travaux'],
                'unite' => $element['unité'],
                'pu' => floatval(str_replace(',', '.', $element['prix_unitaire'])),
                'qte' => floatval(str_replace(',', '.', $element['quantite'])),
                'duree_travaux' => intval($element['duree_travaux']),
            ];
        })->toArray();
        // Insérer les données dans la base de données
        MaisonTravail::insert($donneesAInserer);
        // dd($donneesAInserer);
    }

    public static function checkDataCoherence($data) {
        $error = array();
        // Check if duree is more than 03h

        /* Autre check, ajouter dans la variable si erreur */

        return $error;
    }


    public static function insertDataFromTable(bool $commitable) {
        // Insérer les données distinctes de la table temps dans les tables film et salle
        if ($commitable) {
            DB::beginTransaction();
        }
        try{
            TypeMaison::insert(
                DB::table('maison_travails')->select('type_maison', 'description', 'duree_travaux', 'surface')->distinct()->get()->map(function ($item) {
                    return [
                    'designation' => $item->type_maison,
                    'description' => $item->description,
                    'duree' => $item->duree_travaux,
                    'surface' => $item->surface
                ];
                })->toArray()
            );
            $types = TypeMaison::all();
            // dd($types);
            // $test = DB::table('temps')->select('film')->distinct()->get()->map(function ($item) {
            //     return ['titre' => $item->film];
            // })->toArray();
            // dd($test);
            Unite::insert(
                DB::table('maison_travails')->select('unite')->distinct()->get()->map(function ($item) {
                    return [
                    'unite' => $item->unite
                ];
                })->toArray()
            );
            Travail::insert(
                DB::table('maison_travails')
                ->select('maison_travails.code_travaux', 'maison_travails.type_travaux', 'maison_travails.pu', 'unites.id as idunite')
                ->join('unites', 'unites.unite', '=', 'maison_travails.unite')
                ->distinct()->get()->map(function ($item) {
                    return [
                        'numero' => $item->code_travaux,
                        'designation' => $item->type_travaux,
                        'pu' => $item->pu,
                        'idunite' => $item->idunite
                    ];
                })->toArray()
            );
            foreach ($types as $key => $type) {
                // DetailTravail::insert(
                //     DB::table('maison_travails')
                //     ->select('travails.id as idtravail', 'maison_travails.pu', 'maison_travails.qte')
                //     ->join('travails', 'travails.numero', '=', 'maison_travails.code_travaux')
                //     ->where('type_maison', '=', $type->designation)->get()->map(function ($item) use ($type) {
                //         return [
                //             'idtypemaison' => $type->id,
                //             'idtravail' => $item->idtravail,
                //             'qte' => $item->qte,
                //             'pu' => $item->pu
                //         ];
                //     })->toArray()
                // );
                DetailTravail::insert(
                    DB::table('maison_travails')
                    ->select('maison_travails.pu', 'maison_travails.code_travaux', 'maison_travails.qte')
                    ->where('type_maison', '=', $type->designation)->get()->map(function ($item) use ($type) {
                        return [
                            'idtypemaison' => $type->id,
                            'idtravail' => Travail::where('numero', '=', $item->code_travaux)->limit(1)->get()->first()->id,
                            'qte' => $item->qte,
                            'pu' => $item->pu
                        ];
                    })->toArray()
                );
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
        DB::beginTransaction();
        try {
            MaisonTravail::insertCollectionData($data);
            $errors = MaisonTravail::checkDataCoherence($data);
            if (sizeof($errors) > 0) {
                if ($commitable) {
                    # code...
                    DB::rollBack();
                }
            } else {
                MaisonTravail::insertDataFromTable(false);
                if ($commitable) {
                    DB::commit();
                    # code...
                }
            }
            return $errors;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

}
