<?php

namespace App\Http\Controllers\ApiLFV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\MascotaLFV;

class LFVMascotasControllerAPI extends Controller
{
    public function listarMascotasLFV()
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Obtener las mascotas del usuario
        $mascotas = $user->mascotas;

        // Puedes filtrar los campos si es necesario
        $mascotasFiltradas = $mascotas->map(function ($mascota) {
            return [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'descripcion' => $mascota->descripcion,
                'tipo' => $mascota->tipo,
                'publica' => $mascota->publica,
                'megustas' => $mascota->megustas
            ];
        });

        // Retornar respuesta JSON
        return response()->json($mascotasFiltradas);
    }


    //Metodo Crear Mascota (form-urlencoded (application/x-www-form-urlencoded))
    public function crearMascotaLFV(Request $request)
    {
        // Definir reglas de validación
        $reglas = [
            'nombre' => 'required|string|max:50',
            'descripcion' => 'required|string|max:250',
            'tipo' => 'required|in:Perro,Gato,Pájaro,Dragon,Conejo,Hamster,Tortuga,Pez,Serpiente',
            'publica' => 'required|in:Si,No',
        ];

        // Validar datos recibidos
        $validator = Validator::make($request->all(), $reglas);

        // Código 400 si falla validación (Bad Request)
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        // Obtener usuario autenticado
        $user = auth()->user();

        // Crear nueva mascota
        $mascota = new MascotaLFV();
        $mascota->nombre = $request->nombre;
        $mascota->descripcion = $request->descripcion;
        $mascota->tipo = $request->tipo;
        $mascota->publica = ($request->publica == 'Si') ? true : false;
        $mascota->megustas = 0;
        $mascota->user_id = $user->id;
        $mascota->save();

        // Codigo 200 (exito) cuando se crea la mascota
        return response()->json([
            'id' => $mascota->id,
            'message' => 'Mascota creada correctamente',
            'user' => $user->name
        ], 200);
    }


    //Metodo cambiar mascota (Datos recibidos en JSON)
    public function cambiarMascotaLFV(MascotaLFV $mascota, Request $request)
    {
        // Verificar que los datos recibidos son JSON
        if (!$request->isJson()) {
            return response()->json([
                'message' => 'Los datos deben enviarse en formato JSON'
            ], 400);
        }

        // Definir reglas de validación
        $reglas = [
            'descripcion' => 'required|string|max:250',
            'publica' => 'required|in:Si,No',
        ];

        // Validar datos recibidos
        $validator = Validator::make($request->all(), $reglas);

        // Si la validación falla, retornar errores con código 400
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        // Obtener usuario autenticado
        $user = auth()->user();

        // Verificar que la mascota pertenece al usuario autenticado
        if ($mascota->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tienes permiso para modificar esta mascota'
            ], 403);
        }

        // Actualizar los datos de la mascota
        $mascota->descripcion = $request->descripcion;
        $mascota->publica = $request->publica;
        $mascota->save();

        return response()->json([
            'id' => $mascota->id,
            'message' => $mascota->nombre . ' ha sido modificada correctamente con id ' . $mascota->id,
            'user' => $user->name
        ], 200);
    }

    public function borrarMascotaLFV($mascota)
    {

        // Obtener usuario autenticado
        $user = auth()->user();

        if (!is_numeric($mascota)) {
            return response()->json([
                'message' => 'Debes introducir el id de la mascota (sólo números)'
            ], 400);

        }

        // Buscar en DB el objeto/instancia con esa id
        $mascotaObj = MascotaLFV::find($mascota);

        // Verificar que existe y pertenece al usuario
        if (!$mascotaObj) {
            return response()->json([
                'message' => 'La mascota no existe'
            ], 200);
        }

        // Verificar que pertenece al usuario
        if ($mascotaObj->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tienes permiso para borrar esta mascota'
            ], 200);
        }

        // Eliminar la mascota
        $nombre = $mascotaObj->nombre;
        $id = $mascotaObj->id;
        $mascotaObj->delete();

        // Devolver respuesta
        return response()->json([
            'id' => $id,
            'message' => $nombre . ' ha sido borrada correctamente con id ' . $id,
        ], 200);
    }

}




