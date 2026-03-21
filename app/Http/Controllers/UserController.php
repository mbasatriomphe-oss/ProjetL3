<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'post_nom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'sometimes|in:user,admin',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'status_message' => 'Données invalides',
                    'errors' => $validator->errors(),
                    'data' => null,
                ], 422);
            }

            // Créer l'utilisateur
            $user = User::create([
                'nom' => $request->nom,
                'post_nom' => $request->post_nom,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
            ]);

            $token = $user->createToken(env('APP_BACKEND_TOKEN_KEY', 'tutorer_token'))->plainTextToken;
            
            DB::commit();

            return response()->json([
                'status_code' => 201,
                'status_message' => 'Utilisateur créé avec succès',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'post_nom' => $user->post_nom,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ],
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ], [
            'email.exists' => 'Cette adresse email n\'est associée à aucun compte',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'status_message' => 'Données invalides',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        try {
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                $token = $user->createToken('tutorer_token')->plainTextToken;

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Connexion réussie',
                    'data' => [
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'nom' => $user->nom,
                            'post_nom' => $user->post_nom,
                            'email' => $user->email,
                            'role' => $user->role,
                            'has_client' => $user->hasClient(),
                        ],
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Email ou mot de passe incorrect',
                    'data' => null,
                ], 401);
            }

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Déconnexion d'un utilisateur
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Déconnexion réussie',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Récupérer le profil de l'utilisateur connecté
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user()->load('client');
            
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Profil récupéré avec succès',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'post_nom' => $user->post_nom,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                    ],
                    'client' => $user->client,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil utilisateur
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'post_nom' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'current_password' => 'required_with:new_password|string',
                'new_password' => 'sometimes|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'status_message' => 'Données invalides',
                    'errors' => $validator->errors(),
                    'data' => null,
                ], 422);
            }

            // Vérifier le mot de passe actuel si changement de mot de passe
            if ($request->has('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'status_code' => 422,
                        'status_message' => 'Mot de passe actuel incorrect',
                        'data' => null,
                    ], 422);
                }
                
                $user->password = Hash::make($request->new_password);
            }

            // Mettre à jour les autres champs
            if ($request->has('nom')) {
                $user->nom = $request->nom;
            }
            if ($request->has('post_nom')) {
                $user->post_nom = $request->post_nom;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }

            $user->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Profil mis à jour avec succès',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'post_nom' => $user->post_nom,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Lister tous les utilisateurs (admin uniquement)
     */
    public function index()
    {
        try {
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Accès non autorisé',
                    'data' => null,
                ], 403);
            }

            $users = User::with('client')->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Utilisateurs récupérés avec succès',
                'data' => [
                    'items' => $users,
                    'total' => $users->count(),
                ],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur',
                'error' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}