<?php

namespace App\Http\Controllers;

use App\Models\Unite;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UniteController extends Controller
{
    /**
     * Récupérer toutes les unités
     */
    public function getAll()
    {
        try {
            $unites = Unite::all();

            return response()->json([
                'status_message' => 'Unités récupérées avec succès',
                'data' => [
                    'items' => $unites,
                    'total' => $unites->count()
                ],
                'status_code' => 200
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                'status_message' => $exception->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Récupérer une unité spécifique
     */
    public function getOne($id)
    {
        try {
            $unite = Unite::find($id);

            if (!$unite) {
                return response()->json([
                    'status_message' => 'Unité non trouvée',
                    'data' => null,
                    'status_code' => 404
                ], 404);
            }

            return response()->json([
                'status_message' => 'Unité récupérée avec succès',
                'data' => $unite,
                'status_code' => 200
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                'status_message' => $exception->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Créer une nouvelle unité
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'description' => 'required|string',
                'symbole' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'errors' => $validator->errors(),
                    'status_code' => 422
                ], 422);
            }

            $unite = Unite::create($request->all());

            return response()->json([
                'status_message' => 'Unité créée avec succès',
                'data' => $unite,
                'status_code' => 201
            ], 201);

        } catch (Exception $exception) {
            return response()->json([
                'status_message' => $exception->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Mettre à jour une unité existante
     */
    public function update(Request $request, $id)
    {
        try {
            $unite = Unite::find($id);

            if (!$unite) {
                return response()->json([
                    'status_message' => 'Unité non trouvée',
                    'data' => null,
                    'status_code' => 404
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'symbole' => 'sometimes|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'errors' => $validator->errors(),
                    'status_code' => 422
                ], 422);
            }

            $unite->update($request->all());

            return response()->json([
                'status_message' => 'Unité mise à jour avec succès',
                'data' => $unite,
                'status_code' => 200
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                'status_message' => $exception->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Supprimer une unité
     */
    public function delete($id)
    {
        try {
            $unite = Unite::find($id);

            if (!$unite) {
                return response()->json([
                    'status_message' => 'Unité non trouvée',
                    'data' => null,
                    'status_code' => 404
                ], 404);
            }

            $unite->delete();

            return response()->json([
                'status_message' => 'Unité supprimée avec succès',
                'data' => null,
                'status_code' => 200
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                'status_message' => $exception->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Rechercher des unités par nom ou symbole
     */
    public function search(Request $request)
    {
        try {
            $query = Unite::query();

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('nom', 'like', "%{$search}%")
                      ->orWhere('symbole', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            $unites = $query->get();

            return response()->json([
                'status_message' => 'Recherche effectuée avec succès',
                'data' => [
                    'items' => $unites,
                    'total' => $unites->count()
                ],
                'status_code' => 200
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                'status_message' => $exception->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }
}