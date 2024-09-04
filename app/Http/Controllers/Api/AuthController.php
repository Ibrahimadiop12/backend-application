<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function getUserInfo()
    {
        try {
            // Authentifie l'utilisateur à partir du token JWT
            $user = JWTAuth::parseToken()->authenticate();

            // Prépare les données de réponse avec l'URL de la photo
            $response = [
                'name' => $user->name,
                'photo' => url('storage/app/' . $user->photo), // Assurez-vous que le chemin est correct
                // Ajoutez d'autres informations si nécessaire
            ];

            // Retourne les informations utilisateur en format JSON
            return response()->json($response);
        } catch (\Exception $e) {
            // En cas d'erreur, retourne un message d'erreur en format JSON
            return response()->json([
                'message' => 'Erreur lors de la récupération de l\'utilisateur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function register(Request $request)
{
    // Valider les données du formulaire
    $data = $request->validate([
        "name" => "required",
        "email" => "required|email|unique:users",
        "password" => "required|confirmed",
        'role' => 'required|in:admin,vendeur,client',
        "adresse" => "required",
        "photo" => "required|image|mimes:jpeg,png,jpg,gif|max:2048", // Ajout de la validation pour l'image
        "telephone" => "required",
    ]);

    try {
        // Traitement de l'upload de l'image
        if($request->hasFile('photo'))  {
            $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
            $path = $request->file('photo')->storeAs('images', $filename, 'public');
            $data['photo'] = '/storage/' . $path; // Chemin stocké dans la base de données
        } else {
            return response()->json(['message' => 'Erreur lors de l\'insertion de l\'image'], 422);
        }

        // Hash du mot de passe avant de le stocker
        $data['password'] = Hash::make($data['password']);

        // Définir le statut par défaut à "debloquer"
        $data['statut'] = 'debloquer';

        // Création de l'utilisateur
        $user = User::create($data);

        // Génération du token JWT
        $token = JWTAuth::fromUser($user);

        // Réponse avec les données de l'utilisateur et le token
        return response()->json([
            'statut' => 201,
            'data' => $user,
            "token" => $token,
        ], 201);

    } catch (\Exception $e) {
        // En cas d'erreur, retourne un message d'erreur
        return response()->json([
            "statut" => false,
            "message" => "Erreur lors de l'inscription",
            "error" => $e->getMessage()
        ], 500);
    }
}

    public function login(Request $request)
    {
        $data =  $request->validate([
            "email" => "required|email|",
            "password" => "required"
        ]);

        $token = JWTAuth::attempt($data);

        if(!empty($token))
        {
            return response()->json([
                'statut' => 200,
                'data'=> auth()->user(),
                "token" =>  $token
            ]);

        }else{
            return response()->json([
                "statut" => false,
                "token" =>  null
            ]);
        }
    }



    public function logout()
    {
        auth()->logout();
        return response()->json([
            'statut' => true,
            "message" =>  "utilisateur s'est deconnecte !"
        ]);
    }

    public  function refresh()
    {
        $newToken = auth()->refresh();
        return response()->json([
            'statut' => true,
            "token" =>  $newToken
        ]);
    }
}
