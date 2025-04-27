<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // <--- Agregado
use App\Models\User;
use Exception;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return view('index');
    }

    public function list()
    {
        $users = User::select('id', 'name')->get();
        return response()->json($users);
    }

    public function listUsers()
    {
        $users = User::get();
        return response()->json($users);
    }

    public function register(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'name' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'identification' => 'required|numeric',
                'department_id' => 'required|integer',
                'city_id' => 'required|integer',
                'phone' => 'required|numeric',
                'email' => 'required|email',
                'authorization' => 'required|boolean',
            ]);

            $user = User::where('email', $request->email)
                ->orWhere('identification', $request->identification)
                ->orWhere('phone', $request->phone)
                ->first();

            if (!empty($user)) {
                if ($user->email == $request->email)
                    throw new Exception("Ya existe un usuario registrado con ese correo", 400);
                if ($user->identification == $request->identification)
                    throw new Exception("Ya existe un usuario registrado con ese cédula", 400);
                if ($user->phone == $request->phone)
                    throw new Exception("Ya existe un usuario registrado con ese celular", 400);
            }

            $user = User::create([
                'name' => $request->name,
                'lastName' => $request->lastName,
                'identification' => $request->identification,
                'department_id' => $request->department_id,
                'city_id' => $request->city_id,
                'phone' => $request->phone,
                'email' => $request->email,
                'authorization' => $request->authorization,
            ]);

            DB::commit();

            return response()->json([
                "message" => "Registro exitoso",
                "data" => $user
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
            ], 400);
        }
    }

    public function updateWin(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::find($request->id);
            $user->win = $user->win + 1;
            $user->save();

            DB::commit();
            return response()->json([
                "message" => "Registro exitoso",
                "data" => $user
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
            ], 400);
        }
    }

    public function getDepartments()
    {
        try {
            $response = Http::get('https://api-colombia.com/api/v1/Department');

            if (!$response->ok())
                throw new Exception('Error al obtener departamentos');

            return response()->json($response->json(), 200);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
    }

    public function getCities(Request $request)
    {
        try {
            $request->validate([
                'department_id' => 'required|integer',
            ]);

            $response = Http::get('https://api-colombia.com/api/v1/City');

            if (!$response->ok())
                throw new Exception('Error al obtener ciudades');

            $cities = collect($response->json())
                ->where('departmentId', (int)$request->department_id)
                ->values();

            return response()->json($cities, 200);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
    }
}
