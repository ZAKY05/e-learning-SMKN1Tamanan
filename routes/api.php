<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GuruApiController;

Route::apiResource('guru', GuruApiController::class);

?>