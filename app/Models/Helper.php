<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Helper extends Model
{
    use HasFactory;

    public static function CSVtoCollection($pathFile)
    {
        // Chemin vers le fichier CSV (par exemple, stocké dans le dossier "storage/app")
        // $cheminFichier = storage_path('app/fichier.csv');

        // Vérifier si le fichier existe
        if (!file_exists($pathFile)) {
            // Gérer le cas où le fichier n'existe pas
            return;
        }

        // Lire le contenu du fichier CSV
        $contenuCSV = file_get_contents($pathFile);

        // Convertir le contenu CSV en un tableau de lignes
        $lignes = explode("\n", $contenuCSV);

        // Initialiser une collection vide pour stocker les données
        $donnees = collect();
        $column = collect();
        $first = true;
        // Parcourir chaque ligne du fichier CSV
        foreach ($lignes as $ligne) {
            // Ignorer les lignes vides
            if (empty($ligne)) {
                continue;
            }
            if ($first) {
                $donneesLigne = str_getcsv($ligne);
                for ($i=0; $i < sizeof($donneesLigne); $i++) {
                    $column->push(strtolower($donneesLigne[$i]));
                }
                $first = false;
            } else {
                // Diviser la ligne en tableau en utilisant la virgule comme séparateur
                $donneesLigne = str_getcsv($ligne);

                // Ajouter les données de la ligne à la collection
                $temp = array();
                for ($i=0; $i < sizeof($donneesLigne); $i++) {
                    // $temp->push($donneesLigne[$i]);
                    $temp[$column[$i]] = $donneesLigne[$i];
                }
                $donnees->push($temp);
            }
        }

        // Afficher ou traiter les données lues
        return $donnees;
    }

}
