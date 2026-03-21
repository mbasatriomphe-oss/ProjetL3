<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FournisseurController extends Controller
{
    /**
     * Liste tous les fournisseurs
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $fournisseurs = Fournisseur::all();
            
            return response()->json([
                'status_message' => 'Fournisseurs récupérés avec succès',
                'status_code' => 200,
                'data' => $fournisseurs
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Affiche un fournisseur spécifique
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $fournisseur = Fournisseur::find($id);
            
            if (!$fournisseur) {
                return response()->json([
                    'status_message' => 'Fournisseur non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }
            
            return response()->json([
                'status_message' => 'Fournisseur trouvé',
                'status_code' => 200,
                'data' => $fournisseur
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Crée un nouveau fournisseur
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:90',
                'adresse' => 'required|string|max:63',
                'ville' => 'required|string|max:50',
                'pays' => 'required|string|max:50',
                'contact' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            $fournisseur = Fournisseur::create([
                'nom' => $request->nom,
                'adresse' => $request->adresse,
                'ville' => $request->ville,
                'pays' => $request->pays,
                'contact' => $request->contact
            ]);

            return response()->json([
                'status_message' => 'Fournisseur créé avec succès',
                'status_code' => 201,
                'data' => $fournisseur
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Met à jour un fournisseur
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $fournisseur = Fournisseur::find($id);
            
            if (!$fournisseur) {
                return response()->json([
                    'status_message' => 'Fournisseur non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:90',
                'adresse' => 'sometimes|string|max:63',
                'ville' => 'sometimes|string|max:50',
                'pays' => 'sometimes|string|max:50',
                'contact' => 'sometimes|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mise à jour des champs
            if ($request->has('nom')) $fournisseur->nom = $request->nom;
            if ($request->has('adresse')) $fournisseur->adresse = $request->adresse;
            if ($request->has('ville')) $fournisseur->ville = $request->ville;
            if ($request->has('pays')) $fournisseur->pays = $request->pays;
            if ($request->has('contact')) $fournisseur->contact = $request->contact;
            
            $fournisseur->save();

            return response()->json([
                'status_message' => 'Fournisseur mis à jour avec succès',
                'status_code' => 200,
                'data' => $fournisseur
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Supprime un fournisseur
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $fournisseur = Fournisseur::find($id);
            
            if (!$fournisseur) {
                return response()->json([
                    'status_message' => 'Fournisseur non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            $fournisseur->delete();

            return response()->json([
                'status_message' => 'Fournisseur supprimé avec succès',
                'status_code' => 200,
                'data' => null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * ========== MÉTHODES DE RECHERCHE ==========
     */

    /**
     * Recherche avancée de fournisseurs
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $search = $request->query('search');
            $ville = $request->query('ville');
            $pays = $request->query('pays');
            $perPage = $request->query('per_page', 15);

            $query = Fournisseur::query();

            // Recherche par mot-clé (nom)
            if ($search) {
                $query->where('nom', 'like', "%{$search}%");
            }

            // Recherche par ville
            if ($ville) {
                $query->where('ville', 'like', "%{$ville}%");
            }

            // Recherche par pays
            if ($pays) {
                $query->where('pays', 'like', "%{$pays}%");
            }

            $fournisseurs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'status_message' => 'Fournisseurs récupérés avec succès',
                'status_code' => 200,
                'data' => [
                    'items' => $fournisseurs->items(),
                    'total' => $fournisseurs->total(),
                    'per_page' => $fournisseurs->perPage(),
                    'current_page' => $fournisseurs->currentPage(),
                    'last_page' => $fournisseurs->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Derniers fournisseurs ajoutés
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function latest(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            
            $fournisseurs = Fournisseur::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'status_message' => 'Derniers fournisseurs récupérés',
                'status_code' => 200,
                'data' => [
                    'items' => $fournisseurs,
                    'total' => $fournisseurs->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Statistiques des fournisseurs
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => Fournisseur::count(),
                'par_pays' => Fournisseur::selectRaw('pays, count(*) as total')
                    ->groupBy('pays')
                    ->get(),
                'par_ville' => Fournisseur::selectRaw('ville, count(*) as total')
                    ->groupBy('ville')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get()
            ];

            return response()->json([
                'status_message' => 'Statistiques récupérées avec succès',
                'status_code' => 200,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }
}