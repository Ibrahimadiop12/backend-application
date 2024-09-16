<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function getUserInfo()
    {
        $user = Auth::user();
        if ($user) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                // Ajoute d'autres champs si nécessaire
            ]);
        } else {
            return response()->json(['error' => 'User not authenticated'], 401);
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
        'statut' => 'sometimes|in:bloquer,debloquer',
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
public function update(Request $request, $id)
{
    // Trouver l'utilisateur par ID
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'statut' => false,
            'message' => 'Utilisateur non trouvé'
        ], 404);
    }

    // Valider les nouvelles données du formulaire
    $data = $request->validate([
        "name" => "required",
        "email" => "required|email|unique:users,email," . $id, // Ignorer l'email de l'utilisateur actuel dans la validation unique
        "password" => "nullable|confirmed", // Le mot de passe peut être optionnel
        'role' => 'required|in:admin,vendeur,client',
        "adresse" => "required",
        "photo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048", // L'image est optionnelle
        "telephone" => "required",
        'statut' => 'sometimes|in:bloquer,debloquer',
    ]);

    try {
        // Traitement de l'upload de l'image si une nouvelle image est fournie
        if ($request->hasFile('photo')) {
            $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
            $path = $request->file('photo')->storeAs('images', $filename, 'public');
            $data['photo'] = '/storage/' . $path; // Chemin stocké dans la base de données

            // Supprimer l'ancienne photo si elle existe
            if ($user->photo) {
                $oldImagePath = public_path($user->photo);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Supprime l'ancienne image
                }
            }
        }

        // Hash du mot de passe si un nouveau mot de passe est fourni
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Ne pas écraser l'ancien mot de passe s'il n'est pas modifié
        }

        // Mettre à jour les informations de l'utilisateur
        $user->update($data);

        // Réponse avec les nouvelles données de l'utilisateur
        return response()->json([
            'statut' => 200,
            'data' => $user,
            'message' => 'Profil mis à jour avec succès'
        ], 200);

    } catch (\Exception $e) {
        // En cas d'erreur, retourne un message d'erreur
        return response()->json([
            "statut" => false,
            "message" => "Erreur lors de la mise à jour",
            "error" => $e->getMessage()
        ], 500);
    }
}

public function login(Request $request)
{
    // Validation des données de connexion
    $data =  $request->validate([
        "email" => "required|email",
        "password" => "required"
    ]);

    // Vérifier si l'utilisateur existe avec l'email fourni
    $user = User::where('email', $data['email'])->first();

    // Vérifier si l'utilisateur est bloqué avant de tenter la connexion
    if ($user && $user->statut == 'bloquer') {
        return response()->json([
            'statut' => false,
            'message' => 'Votre compte est bloqué. Veuillez contacter l\'administrateur.',
        ], 403); // 403 = Forbidden
    }

    // Tentative de connexion avec les données fournies
    $token = JWTAuth::attempt($data);

    // Vérifier si les informations d'identification sont valides
    if ($token) {
        return response()->json([
            'statut' => 200,
            'data' => auth()->user(),
            "token" =>  $token
        ]);
    } else {
        return response()->json([
            "statut" => false,
            "message" => "Identifiants invalides.",
            "token" =>  null
        ], 401); // 401 = Unauthorized
    }
}
///fonction qui permet de bloquer un utilisateur

public function bloquerUtilisateur($id)
{
    try {
        // Trouver l'utilisateur par son ID
        $user = User::findOrFail($id);

        // Vérifier si l'utilisateur est déjà bloqué
        if ($user->statut == 'bloquer') {
            return response()->json([
                'statut' => false,
                'message' => 'L\'utilisateur est déjà bloqué.'
            ], 400); // 400 = Bad Request
        }

        // Mettre à jour le statut de l'utilisateur à "bloquer"
        $user->statut = 'bloquer';
        $user->save();

        return response()->json([
            'statut' => true,
            'message' => 'L\'utilisateur a été bloqué avec succès.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'statut' => false,
            'message' => 'Erreur lors du blocage de l\'utilisateur.',
            'error' => $e->getMessage()
        ], 500);
    }
}
   ///Fonction qui permet de debloquer un utilisateur


   public function debloquerUtilisateur($id)
{
    try {
        // Trouver l'utilisateur par son ID
        $user = User::findOrFail($id);

        // Vérifier si l'utilisateur est déjà débloqué
        if ($user->statut == 'debloquer') {
            return response()->json([
                'statut' => false,
                'message' => 'L\'utilisateur est déjà débloqué.'
            ], 400);
        }

        // Mettre à jour le statut de l'utilisateur à "debloquer"
        $user->statut = 'debloquer';
        $user->save();

        return response()->json([
            'statut' => true,
            'message' => 'L\'utilisateur a été débloqué avec succès.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'statut' => false,
            'message' => 'Erreur lors du déblocage de l\'utilisateur.',
            'error' => $e->getMessage()
        ], 500);
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


    public function getUsers()
    {

        // Retrieve all users
        $users = User::all();

        return response()->json([
            'statut' => 200,
            'data' => $users
        ]);
    }



    public function updateStatus(Request $request, $id)
    {
        // Validation des données
        $request->validate([
            'statut' => 'required|string|in:debloquer,bloquer', // Validation pour le statut
        ]);

        // Recherche de la catégorie
        $user = User::find($id);

        // Vérification si la catégorie existe
        if (!$user) {
            return response()->json(['message' => 'produit non trouvée'], 404);
        }

        // Mise à jour du statut
        $user->update([
            'statut' => $request->statut,
        ]);

        return response()->json([
            'message' => 'Statut de l\'utilisateur mis à jour avec succès!',
            'produit' => $user
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'user non trouvé'], 404);
        }

        // Supprimer l'image associée si elle existe
        if ($user->image && Storage::exists('public/images/' . $user->image)) {
            Storage::delete('public/images/' . $user->image);
        }

        $user->delete();

        return response()->json(['message' => 'user supprimé avec succès'], 200);
    }
}
