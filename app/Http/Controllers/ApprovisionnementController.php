<?php

namespace App\Http\Controllers;

use App\Models\Approvisionnement;
use App\Models\DetailApprovisionnement;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ApprovisionnementController extends Controller
{
    /**
     * Liste tous les approvisionnements
     */
    public function index(Request $request)
    {
        try {
            $approvisionnements = Approvisionnement::with(['fournisseur', 'admin', 'details.produit'])
                ->orderBy('date_approv', 'desc')
                ->get();
            
            return response()->json([
                'status_message' => 'Approvisionnements récupérés avec succès',
                'status_code' => 200,
                'data' => $approvisionnements
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
     * Affiche un approvisionnement spécifique
     */
    public function show($id)
    {
        try {
            $approvisionnement = Approvisionnement::with(['fournisseur', 'admin', 'details.produit'])
                ->find($id);
            
            if (!$approvisionnement) {
                return response()->json([
                    'status_message' => 'Approvisionnement non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }
            
            return response()->json([
                'status_message' => 'Approvisionnement trouvé',
                'status_code' => 200,
                'data' => $approvisionnement
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
     * Crée un nouvel approvisionnement avec ses détails
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'date_approv' => 'required|date',
                'fournisseur_id' => 'required|exists:fournisseurs,id',
                'admin_id' => 'required|exists:users,id',
                'details' => 'required|array|min:1',
                'details.*.produit_id' => 'required|exists:produits,id',
                'details.*.quantite' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Calculer le prix d'achat total et la quantité totale
            $quantiteTotale = 0;
            $prixAchatTotal = 0;

            foreach ($request->details as $detail) {
                $produit = Produit::find($detail['produit_id']);
                $quantiteTotale += $detail['quantite'];
                $prixAchatTotal += $detail['quantite'] * $produit->prix;
            }

            // Créer l'approvisionnement
            $approvisionnement = Approvisionnement::create([
                'date_approv' => $request->date_approv,
                'quantite' => $quantiteTotale,
                'prix_achat' => $prixAchatTotal,
                'fournisseur_id' => $request->fournisseur_id,
                'admin_id' => $request->admin_id
            ]);

            // Créer les détails
            foreach ($request->details as $detail) {
                DetailApprovisionnement::create([
                    'approv_id' => $approvisionnement->id,
                    'produit_id' => $detail['produit_id'],
                    'quantite' => $detail['quantite']
                ]);
            }

            DB::commit();

            // Charger les relations
            $approvisionnement->load(['fournisseur', 'admin', 'details.produit']);

            return response()->json([
                'status_message' => 'Approvisionnement créé avec succès',
                'status_code' => 201,
                'data' => $approvisionnement
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Met à jour un approvisionnement
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $approvisionnement = Approvisionnement::find($id);
            
            if (!$approvisionnement) {
                return response()->json([
                    'status_message' => 'Approvisionnement non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'date_approv' => 'sometimes|date',
                'fournisseur_id' => 'sometimes|exists:fournisseurs,id',
                'admin_id' => 'sometimes|exists:users,id',
                'details' => 'sometimes|array|min:1',
                'details.*.produit_id' => 'required_with:details|exists:produits,id',
                'details.*.quantite' => 'required_with:details|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mise à jour des champs
            if ($request->has('date_approv')) $approvisionnement->date_approv = $request->date_approv;
            if ($request->has('fournisseur_id')) $approvisionnement->fournisseur_id = $request->fournisseur_id;
            if ($request->has('admin_id')) $approvisionnement->admin_id = $request->admin_id;

            // Mise à jour des détails si fournis
            if ($request->has('details')) {
                // Supprimer les anciens détails
                DetailApprovisionnement::where('approv_id', $id)->delete();

                $quantiteTotale = 0;
                $prixAchatTotal = 0;

                foreach ($request->details as $detail) {
                    $produit = Produit::find($detail['produit_id']);
                    $quantiteTotale += $detail['quantite'];
                    $prixAchatTotal += $detail['quantite'] * $produit->prix;

                    DetailApprovisionnement::create([
                        'approv_id' => $approvisionnement->id,
                        'produit_id' => $detail['produit_id'],
                        'quantite' => $detail['quantite']
                    ]);
                }

                $approvisionnement->quantite = $quantiteTotale;
                $approvisionnement->prix_achat = $prixAchatTotal;
            }
            
            $approvisionnement->save();

            DB::commit();

            // Charger les relations
            $approvisionnement->load(['fournisseur', 'admin', 'details.produit']);

            return response()->json([
                'status_message' => 'Approvisionnement mis à jour avec succès',
                'status_code' => 200,
                'data' => $approvisionnement
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Supprime un approvisionnement
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $approvisionnement = Approvisionnement::find($id);
            
            if (!$approvisionnement) {
                return response()->json([
                    'status_message' => 'Approvisionnement non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Supprimer les détails
            DetailApprovisionnement::where('approv_id', $id)->delete();

            // Supprimer l'approvisionnement
            $approvisionnement->delete();

            DB::commit();

            return response()->json([
                'status_message' => 'Approvisionnement supprimé avec succès',
                'status_code' => 200,
                'data' => null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Recherche avancée des approvisionnements
     */
    public function search(Request $request)
    {
        try {
            $fournisseurId = $request->query('fournisseur_id');
            $dateDebut = $request->query('date_debut');
            $dateFin = $request->query('date_fin');
            $perPage = $request->query('per_page', 15);

            $query = Approvisionnement::with(['fournisseur', 'admin', 'details.produit']);

            if ($fournisseurId) {
                $query->where('fournisseur_id', $fournisseurId);
            }

            if ($dateDebut) {
                $query->where('date_approv', '>=', $dateDebut);
            }
            if ($dateFin) {
                $query->where('date_approv', '<=', $dateFin);
            }

            $approvisionnements = $query->orderBy('date_approv', 'desc')->paginate($perPage);

            return response()->json([
                'status_message' => 'Approvisionnements récupérés avec succès',
                'status_code' => 200,
                'data' => [
                    'items' => $approvisionnements->items(),
                    'total' => $approvisionnements->total(),
                    'per_page' => $approvisionnements->perPage(),
                    'current_page' => $approvisionnements->currentPage(),
                    'last_page' => $approvisionnements->lastPage()
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
     * Derniers approvisionnements
     */
    public function latest(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            
            $approvisionnements = Approvisionnement::with(['fournisseur', 'admin', 'details.produit'])
                ->orderBy('date_approv', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'status_message' => 'Derniers approvisionnements récupérés',
                'status_code' => 200,
                'data' => [
                    'items' => $approvisionnements,
                    'total' => $approvisionnements->count()
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
     * Statistiques des approvisionnements
     */
    public function statistics(Request $request)
    {
        try {
            $year = $request->query('year', date('Y'));
            $month = $request->query('month');

            $query = Approvisionnement::query();

            if ($month) {
                $query->whereYear('date_approv', $year)
                      ->whereMonth('date_approv', $month);
            } else {
                $query->whereYear('date_approv', $year);
            }

            $stats = [
                'total_approvisionnements' => $query->count(),
                'total_quantite' => $query->sum('quantite'),
                'total_montant' => $query->sum('prix_achat'),
                'moyenne_prix_achat' => round($query->avg('prix_achat'), 2),
                'par_fournisseur' => Approvisionnement::selectRaw('fournisseur_id, count(*) as total, sum(quantite) as quantite_totale, sum(prix_achat) as montant_total')
                    ->with('fournisseur:id,nom')
                    ->whereYear('date_approv', $year)
                    ->groupBy('fournisseur_id')
                    ->get(),
                'par_mois' => Approvisionnement::selectRaw('MONTH(date_approv) as mois, YEAR(date_approv) as annee, count(*) as total, sum(quantite) as quantite_totale')
                    ->whereYear('date_approv', $year)
                    ->groupBy(\DB::raw('YEAR(date_approv), MONTH(date_approv)'))
                    ->orderBy('mois')
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