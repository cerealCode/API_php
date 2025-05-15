<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiLFV\Auth\LoginController;
use App\Http\Controllers\ApiLFV\LFVMascotasControllerAPI;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/** Respuesta por defecto cuando no hay usuario autenticado*/
Route::get('/login', function () {
    return response()->json(["mensaje" => "Es necesaria autenticación para acceder"], 401);
})->name('login');

/** Ruta que permite a un usuario autenticado ver sus datos completos (JSON) tras autenticación.
 *  HTTP GET
 *  http://localhost:.../api/user
 */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/** Ruta que permite a un usuario hacer login vía API.
 *  HTTP POST
 *  http://localhost:.../api/login
 */
Route::post('/login', [LoginController::class, 'doLogin']);

/** Ruta que permite a un usuario hacer logout (borrar tokens)
 *  HTTP Cualquiera
 *  http://localhost:.../api/logout
 */
Route::any('/logout', [LoginController::class, 'doLogout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta Listar Mascotas
Route::get('/mascotasLFV', [LFVMascotasControllerAPI::class, 'listarMascotasLFV'])
    ->middleware('auth:sanctum');

// Ruta para crear una mascota
Route::post('/crearmascotaLFV', [LFVMascotasControllerAPI::class, 'crearMascotaLFV'])
    ->middleware('auth:sanctum');

//Ruta cambiar mascota con parametro de ID
Route::put('/mascotaLFV/{mascota}', [LFVMascotasControllerAPI::class, 'cambiarMascotaLFV'])
    ->middleware('auth:sanctum');

//Ruta borrar mascota cojn busqueda en DB
Route::delete('/mascotaLFV/{mascota}', [LFVMascotasControllerAPI::class, 'borrarMascotaLFV'])
    ->middleware('auth:sanctum');
