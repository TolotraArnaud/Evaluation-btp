<?php
namespace App\Services;

use Illuminate\Support\Facades\Session;

class ClientService
{
    public static function data()
    {
        return session('client');
    }
}
