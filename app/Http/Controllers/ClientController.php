<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Http\Requests\DevisRequest;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Finition;
use App\Models\Paiement;
use App\Models\TypeMaison;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{

    public function index() {
        $client = session('client');
        dd(Paiement::select('sum(montant)')->from('paiements')->get());
        $devis = Devis::where('idclient', $client->id)->paginate(5);
        // $paiements = Paiement::select('paiements.iddevis', DB::raw('sum(montant) as total'))
        //     ->join('devis as d', 'd.id', '=', 'paiements.iddevis')
        //     ->groupBy('paiements.iddevis')
        //     ->get();
        return view('client.index', [
            'client' => $client,
            'devis' => $devis
        ]);
    }

    public function addDevis() {
        $types = TypeMaison::all();
        $finitions = Finition::all();
        return view('client.devis.add', [
            'client' => session('client'),
            'types' => $types,
            'finitions' => $finitions
        ]);
    }

    public function storeDevis(DevisRequest $request) {
        $data = $request->validated();
        $client = session('client');
        try {
            //code...
            $devis = Devis::create([
                'ref_devis' => Devis::getNextRefNumber(),
                'idclient' => $client->id,
                'idtypemaison' => $data['type_maison'],
                'idfinition' => $data['finition'],
                'date_debut' => $data['date_debut'],
                'date_devis' => Carbon::now()->format('d/m/Y')
            ]);
            $date_debut = $data['date_debut'];
            $date = Carbon::parse($date_debut);
            $date = $date->addDays($devis->type_maison->duree);
            $devis->date_fin = $date;
            $devis->pu = $devis->getTotalMontant() + ($devis->getTotalMontant() * $devis->finition->percent / 100);
            $devis->save();
            $devis->saveDetails();

            return to_route('client.index');
        } catch (\Throwable $th) {
            throw $th;
            // dd($th);
            // return to_route('client.addDevis');
        }
    }

    public function viewDevis(Devis $devis) {
        return view('client.devis.detail', [
            'client' => session('client'),
            'devis' => $devis
        ]);
    }

    public function payement(Devis $devis) {
        return view('client.devis.paiement', [
            'client' => session('client'),
            'devis' => $devis
        ]);
    }

    public function pay(Request $request) {
        $credentials = $request->validate([
            'date_paiement' => ['required','date'],
            'iddevis' => ['required'],
            'montant' => 'required'
        ]);
        $client = session('client');
        $devis = Devis::find($credentials['iddevis']);
        DB::beginTransaction();
        Paiement::create([
            'ref_paiement' => Paiement::getNextRefNumber(),
            'iddevis' => $credentials['iddevis'],
            'montant' => $credentials['montant'],
            'date_paiement' => $credentials['date_paiement']
        ]);
        $sum = Paiement::sum('montant');
        if ($sum > $devis->pu) {
            DB::rollBack();
            return to_route('paiment.devis', ['devis' => $devis->id])->withErrors(['error' => ['Le total de vos paiements ne doit pas depasse la somme demande']]);
        }
        DB::commit();

        return to_route('client.index');
    }

    public function ws_pay(Request $request){
        $credentials = $request->validate([
            'date_paiement' => ['required','date'],
            'iddevis' => ['required'],
            'montant' => 'required'
        ]);
        $client = session('client');
        $devis = Devis::find($credentials['iddevis']);
        DB::beginTransaction();
        Paiement::create([
            'ref_paiement' => Paiement::getNextRefNumber(),
            'iddevis' => $credentials['iddevis'],
            'montant' => $credentials['montant'],
            'date_paiement' => $credentials['date_paiement']
        ]);
        $sum = Paiement::where('iddevis', '=', $credentials['iddevis'])->sum('montant');
        if ($sum > $devis->pu) {
            DB::rollBack();
            return response()->json(['errors' => 'Le montant total de vos paiements ne doit pas depasser la somme demande']);
        }
        DB::commit();

        return response()->json(['success' => 'Le paiement a ete effectue avec succes']);
    }

    public function loginPage() {
        return view('auth.client');
    }

    public function doLogin(ClientRequest $request) {
        $data = $request->validated();
        $client = Client::where('tel', $data['tel'])->limit(1)->get()->first();
        if ($client == null) {
            $client = Client::create([
                'tel' => $data['tel']
            ]);
        }
        session()->regenerate(true);
        session()->put('client', $client);
        return to_route('client.index');
    }

    public function logout() {
        session()->forget('client');
        return to_route('client.login');
    }
}
