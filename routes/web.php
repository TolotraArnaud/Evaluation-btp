<?php

use App\Http\Controllers\CinemaController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CSVController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return to_route('client.index');
});


Route::middleware('auth')->group(function (){
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/reset', [HomeController::class, 'resetDB'])->name('reset.database');
    // Route::get('/forms', [TemplateController::class, 'forms'])->name('forms');
    // Route::post('/forms/import/csv', [TemplateController::class, 'csvImport'])->name('forms.importcsv');
    Route::prefix('/import')->group(function () {
        Route::get('/form/btp', [CSVController::class, 'maisonDevisImport'])->name('import.form.maison.devis');
        Route::post('/form/btp', [CSVController::class, 'importTypeMaisonDevis'])->name('import.maison.devis');
        Route::get('/form/paiement', [CSVController::class, 'paiementImport'])->name('import.form.paiement');
        Route::post('/form/paiement', [CSVController::class, 'importPaiement'])->name('import.paiement');
    });
    Route::get('/list', [HomeController::class, 'viewAnyDevis'])->name('list');
    Route::get('/devi/{devis}', [HomeController::class, 'viewDevis'])->name('detail.devis');
    Route::get('/travaux', [HomeController::class, 'viewAnyTravaux'])->name('travail.list');
    Route::put('/travail', [HomeController::class, 'setTravail'])->name('travail.modif');
    Route::get('/finitions', [HomeController::class, 'viewAnyFinitions'])->name('finition.list');
    Route::put('/finition', [HomeController::class, 'setFinition'])->name('finition.modif');
});

Route::get('/login', [UserController::class, 'loginPage'])->name('auth.login');
Route::post('/login', [UserController::class, 'doLogin'])->name('auth.login');
Route::get('/register', [UserController::class, 'registerPage'])->name('auth.register');
Route::post('/register', [UserController::class, 'doRegister'])->name('auth.register');
Route::delete('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/client/login', [ClientController::class, 'loginPage'])->name('client.login');
Route::post('/client/login', [ClientController::class, 'doLogin'])->name('client.login');
Route::delete('/client/logout', [ClientController::class, 'logout'])->name('client.logout');

Route::middleware('client.auth')->prefix('/client')->group(function () {
    Route::get('/index', [ClientController::class, 'index'])->name('client.index');
    Route::get('/add/devis', [ClientController::class, 'addDevis'])->name('client.addDevis');
    Route::post('/add/devis', [ClientController::class, 'storeDevis'])->name('client.addDevis');
    Route::get('/devi/{devis}', [ClientController::class, 'viewDevis'])->name('view.devis');
    Route::prefix('/pdf')->group(function () {
        Route::get('/devis/{devis}', [PDFController::class, 'generatePDF'])->name('pdf.devis');
    });
    Route::get('/paiement/{devis}', [ClientController::class, 'payement'])->name('paiment.devis');
    Route::post('/pay', [ClientController::class, 'pay'])->name('pay.devis');
    Route::post('/ws/pay', [ClientController::class, 'ws_pay'])->name('ws.pay.devis');
});
