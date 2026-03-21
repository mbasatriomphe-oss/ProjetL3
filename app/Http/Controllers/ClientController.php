<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Créer un profil client pour l'utilisateur connecté
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();

            // Vérifier si l'utilisateur a déjà un profil client
            if ($user->hasClient()) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Vous avez déjà un profil client',
                    'data' => null,
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'adresse' => 'required|string',
                'numero_tel' => 'required|string|max:20|unique:clients,numero_tel',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'status_message' => 'Données invalides',
                    'errors' => $validator->errors(),
                    'data' => null,
                ], 422);
            }

            // Créer le client avec les informations de l'utilisateur
            $client = Client::create([
                'adresse' => $request->adresse,
                'numero_tel' => $request->numero_tel,
                'user_id' => $user->id,
            ]);

            DB::commit();

            // Charger la relation user
            $client->load('user');

            return response()->json([
                'status_code' => 201,
                'status_message' => 'Profil client créé avec succès',
                'data' => [
                    'id' => $client->id,
                    'nom' => $user->nom,
                    'post_nom' => $user->post_nom,
                    'email' => $user->email,
                    'adresse' => $client->adresse,
                    'numero_tel' => $client->numero_tel,
                    'created_at' => $client->created_at,
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
     * Récupérer le profil client de l'utilisateur connecté
     */
    public function getMyClient()
    {
        try {
            $user = auth()->user();
            $client = $user->client;

            if (!$client) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Vous n\'avez pas encore de profil client',
                    'data' => null,
                ], 404);
            }

            // Charger la relation user
            $client->load('user');

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Profil client récupéré avec succès',
                'data' => [
                    'id' => $client->id,
                    'nom' => $user->nom,
                    'post_nom' => $user->post_nom,
                    'email' => $user->email,
                    'adresse' => $client->adresse,
                    'numero_tel' => $client->numero_tel,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
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
     * Mettre à jour le profil client
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();
            $client = $user->client;

            if (!$client) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Vous n\'avez pas encore de profil client',
                    'data' => null,
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'adresse' => 'sometimes|string',
                'numero_tel' => 'sometimes|string|max:20|unique:clients,numero_tel,' . $client->id,
                'nom' => 'sometimes|string|max:255',
                'post_nom' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'status_message' => 'Données invalides',
                    'errors' => $validator->errors(),
                    'data' => null,
                ], 422);
            }

            // Mettre à jour les informations du client
            $clientData = [];
            if ($request->has('adresse')) {
                $clientData['adresse'] = $request->adresse;
            }
            if ($request->has('numero_tel')) {
                $clientData['numero_tel'] = $request->numero_tel;
            }
            
            if (!empty($clientData)) {
                $client->update($clientData);
            }

            // Mettre à jour les informations de l'utilisateur si fournies
            $userData = [];
            if ($request->has('nom')) {
                $userData['nom'] = $request->nom;
            }
            if ($request->has('post_nom')) {
                $userData['post_nom'] = $request->post_nom;
            }
            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Profil client mis à jour avec succès',
                'data' => [
                    'id' => $client->id,
                    'nom' => $user->nom,
                    'post_nom' => $user->post_nom,
                    'email' => $user->email,
                    'adresse' => $client->adresse,
                    'numero_tel' => $client->numero_tel,
                    'updated_at' => $client->updated_at,
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
     * Récupérer tous les clients (admin uniquement)
     */
    public function index(Request $request)
    {
        try {
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Accès non autorisé',
                    'data' => null,
                ], 403);
            }

            $query = Client::with('user');

            // Recherche
            if ($request->has('search') && !empty($request->search)) {
                $query->search($request->search);
            }

            // Tri
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if ($sortField === 'nom') {
                $query->join('users', 'clients.user_id', '=', 'users.id')
                      ->orderBy('users.nom', $sortDirection)
                      ->select('clients.*');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $clients = $query->paginate($perPage);

            // Transformer les données pour inclure les infos utilisateur
            $clients->getCollection()->transform(function ($client) {
                return [
                    'id' => $client->id,
                    'nom' => $client->user->nom,
                    'post_nom' => $client->user->post_nom,
                    'email' => $client->user->email,
                    'adresse' => $client->adresse,
                    'numero_tel' => $client->numero_tel,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
                ];
            });

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Clients récupérés avec succès',
                'data' => $clients,
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
     * Récupérer un client spécifique (admin uniquement)
     */
    public function getOne($id)
    {
        try {
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Accès non autorisé',
                    'data' => null,
                ], 403);
            }

            $client = Client::with('user')->find($id);

            if (!$client) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Client non trouvé',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Client récupéré avec succès',
                'data' => [
                    'id' => $client->id,
                    'nom' => $client->user->nom,
                    'post_nom' => $client->user->post_nom,
                    'email' => $client->user->email,
                    'adresse' => $client->adresse,
                    'numero_tel' => $client->numero_tel,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
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
     * Supprimer un client (admin uniquement)
     */
    public function delete($id)
    {
        try {
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Accès non autorisé',
                    'data' => null,
                ], 403);
            }

            $client = Client::find($id);

            if (!$client) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Client non trouvé',
                    'data' => null,
                ], 404);
            }

            // Note: On ne supprime pas l'utilisateur associé
            $client->delete();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Client supprimé avec succès',
                'data' => null,
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
     * Statistiques des clients (admin uniquement)
     */
    public function statistics()
    {
        try {
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Accès non autorisé',
                    'data' => null,
                ], 403);
            }

            $totalClients = Client::count();
            $totalUsers = User::count();
            $usersWithoutClient = User::doesntHave('client')->count();

            // Clients créés par mois (6 derniers mois)
            $clientsByMonth = Client::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Statistiques récupérées avec succès',
                'data' => [
                    'total_clients' => $totalClients,
                    'total_users' => $totalUsers,
                    'users_without_client' => $usersWithoutClient,
                    'conversion_rate' => $totalUsers > 0 ? round(($totalClients / $totalUsers) * 100, 2) . '%' : '0%',
                    'clients_by_month' => $clientsByMonth,
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