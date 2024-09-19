<?php

namespace App\Http\Controllers;

use App\Models\Devis;
use App\Models\Film;
use App\Models\Paiement;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\View as ViewView;
use Maatwebsite\Excel\Facades\Excel;

class PDFController extends Controller
{
    public function generatePDF(Devis $devis) {

        // Creer une nouvelle instance de DomPdf
        $dompdf = new Dompdf();
        // Rendre la vue avec les donnees
        $view = view('export.pdf.devis', ['devis' => $devis]);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($view);

        // Activer les options de rendu du PDF (optionel)
        $dompdf->setOptions(new Options(['isHtml5ParserEnabled' => true, 'isPhpEnable' => true]));

        // $dompdf->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnable' => true]);

        // Generer le PDF
        $dompdf->render();

        // Envoyer le PDF au navigateur
        return $dompdf->stream('devis.pdf');
    }
}
