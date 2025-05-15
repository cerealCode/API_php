<?php

namespace App\Http\Controllers\ApiLFV\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function doLogin(Request $request): JsonResponse
    {
        //Creamos un validador
        $validator = Validator::make($request->only(['email', 'password']), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //"token": "1|MEYigxD0NSEWz4oUXDuODtM9ETvrCnCbvuDGiQYr8557a53e"

        // Si no se valida, al ser una respuesta JSON se retorna 422 Unprocessabel Entity y un objeto JSON con los errores
        if ($validator->fails()) {
            return response()->json(['mensaje' => 'Email o password no proporcionados o no válidos.'], 422);
        }

        $data = $validator->safe()->only('email', 'password');
        // Se valida el usuario (en vez de attempt hacemos solo validate, porque no necesitamos que se almacene en la sesión)
        if (!Auth::validate($data)) {
            return response()->json([
                'mensaje' => 'Credenciales no válidas',
            ], 401);
        }

        // Si llegamos hasta aquí, la validación se ha producido. Buscamos el usuario autenticado y generamos un token
        $user = User::where('email', $validator->getData()['email'])->first();
        $token = $user->createToken('TOKEN-ACCESO-API');

        // Enviamos el token.
        return response()->json([
            'mensaje' => 'Login correcto',
            'token' => $token->plainTextToken
        ]);
    }

    /**
     * Handle the logout request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function doLogout(Request $request): JsonResponse
    {
        //Borramos el token del usuario autenticado (dejando los demás intactos)
        $eliminados = $request->user()->currentAccessToken()->delete();
        //También se pueden borrar todos los tokens:
        //$eliminados=$request->user()->tokens()->delete();

        return response()->json([
            'mensaje' => $eliminados > 0 ? 'Logout correcto' : 'El usuario no estaba autenticado',
            'codigo' => $eliminados > 0 ? 1 : 0
        ], 200);
    }
}



