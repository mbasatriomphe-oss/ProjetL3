<?php

namespace App\Http\Controllers;

use App\Models\Approvisionnement;
use App\Models\DetailApprovisionnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovisionnementController extends Controller
{
    /**
     * Afficher la liste des approvisionnements avec leurs détails
     */
    public function index()
    {
        $approvisionnements = Approvisionnement::with([
            'fournisseur', 
            'admin',
            'detailApprovisionnements.produit'
        ])->get();
        
        return response()->json($approvisionnements);
    }

    /**
     * Créer un nouvel approvisionnement avec ses détails
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_approv' => 'required|date',
            'quantite' => 'required|string|max:50',
            'prix_achat' => 'required|string|max:62',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'admin_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
            'details.*.produit_id' => 'required|exists:produits,id',
            'details.*.quantite' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Créer l'approvisionnement
            $approvisionnement = Approvisionnement::create([
                'date_approv' => $validated['date_approv'],
                'quantite' => $validated['quantite'],
                'prix_achat' => $validated['prix_achat'],
                'fournisseur_id' => $validated['fournisseur_id'],
                'admin_id' => $validated['admin_id']
            ]);

            // Créer les détails d'approvisionnement
            foreach ($validated['details'] as $detail) {
                DetailApprovisionnement::create([
                    'approv_id' => $approvisionnement->id,
                    'produit_id' => $detail['produit_id'],
                    'quantite' => $detail['quantite']
                ]);
            }

            DB::commit();

            return response()->json(
                $approvisionnement->load([
                    'fournisseur', 
                    'admin', 
                    'detailApprovisionnements.produit'
                ]), 
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de l\'approvisionnement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un approvisionnement spécifique avec ses détails
     */
    public function show($id)
    {
        $approvisionnement = Approvisionnement::with([
            'fournisseur', 
            'admin', 
            'detailApprovisionnements.produit'
        ])->find($id);

        if (!$approvisionnement) {
            return response()->json(['message' => 'Approvisionnement non trouvé'], 404);
        }

        return response()->json($approvisionnement);
    }

    /**
     * Mettre à jour un approvisionnement
     */
    public function update(Request $request, $id)
    {
        $approvisionnement = Approvisionnement::find($id);

        if (!$approvisionnement) {
            return response()->json(['message' => 'Approvisionnement non trouvé'], 404);
        }

        $validated = $request->validate([
            'date_approv' => 'sometimes|date',
            'quantite' => 'sometimes|string|max:50',
            'prix_achat' => 'sometimes|string|max:62',
            'fournisseur_id' => 'sometimes|exists:fournisseurs,id',
            'admin_id' => 'sometimes|exists:users,id'
        ]);

        $approvisionnement->update($validated);

        return response()->json(
            $approvisionnement->load([
                'fournisseur', 
                'admin', 
                'detailApprovisionnements.produit'
            ])
        );
    }

    /**
     * Ajouter un détail à un approvisionnement existant
     */
    public function addDetail(Request $request, $id)
    {
        $approvisionnement = Approvisionnement::find($id);

        if (!$approvisionnement) {
            return response()->json(['message' => 'Approvisionnement non trouvé'], 404);
        }

        $validated = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1'
        ]);

        $detail = DetailApprovisionnement::create([
            'approv_id' => $approvisionnement->id,
            'produit_id' => $validated['produit_id'],
            'quantite' => $validated['quantite']
        ]);

        return response()->json(
            $detail->load('produit'),
            201
        );
    }

    /**
     * Mettre à jour un détail d'approvisionnement
     */
    public function updateDetail(Request $request, $id, $detailId)
    {
        $detail = DetailApprovisionnement::where('approv_id', $id)
                    ->where('id', $detailId)
                    ->first();

        if (!$detail) {
            return response()->json(['message' => 'Détail non trouvé'], 404);
        }

        $validated = $request->validate([
            'produit_id' => 'sometimes|exists:produits,id',
            'quantite' => 'sometimes|integer|min:1'
        ]);

        $detail->update($validated);

        return response()->json($detail->load('produit'));
    }

    /**
     * Supprimer un détail d'approvisionnement
     */
    public function deleteDetail($id, $detailId)
    {
        $detail = DetailApprovisionnement::where('approv_id', $id)
                    ->where('id', $detailId)
                    ->first();

        if (!$detail) {
            return response()->json(['message' => 'Détail non trouvé'], 404);
        }

        $detail->delete();

        return response()->json(['message' => 'Détail supprimé avec succès']);
    }

    /**
     * Supprimer un approvisionnement et tous ses détails
     */
    public function destroy($id)
    {
        $approvisionnement = Approvisionnement::find($id);

        if (!$approvisionnement) {
            return response()->json(['message' => 'Approvisionnement non trouvé'], 404);
        }

        try {
            DB::beginTransaction();

            // Supprimer les détails d'abord
            DetailApprovisionnement::where('approv_id', $id)->delete();
            
            // Supprimer l'approvisionnement
            $approvisionnement->delete();

            DB::commit();

            return response()->json(['message' => 'Approvisionnement supprimé avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les approvisionnements par fournisseur
     */
    public function getByFournisseur($fournisseurId)
    {
        $approvisionnements = Approvisionnement::with([
            'fournisseur', 
            'admin', 
            'detailApprovisionnements.produit'
        ])
        ->where('fournisseur_id', $fournisseurId)
        ->get();

        return response()->json($approvisionnements);
    }
}