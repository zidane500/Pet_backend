<?php

use Illuminate\Support\Facades\Route;

// La route sanctum/csrf-cookie est gérée automatiquement par Sanctum
// Tu n'as pas besoin de la définir ici

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});