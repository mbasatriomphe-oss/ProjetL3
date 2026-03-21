<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Photoproduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProduitController extends Controller
{
    /**
     * Liste tous les produits
     */
    public function index(Request $request)
    {
        try {
            $produits = Produit::all();
            
            foreach ($produits as $produit) {
                $produit->photos = Photoproduit::where('produit_id', $produit->id)->get();
            }
            
            return response()->json([
                'status_message' => 'Produits récupérés',
                'status_code' => 200,
                'data' => $produits
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
     * Affiche un produit spécifique
     */
    public function show(Request $request, $id)
    {
        try {
            $produit = Produit::find($id);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }
            
            $produit->photos = Photoproduit::where('produit_id', $produit->id)->get();
            
            return response()->json([
                'status_message' => 'Produit trouvé',
                'status_code' => 200,
                'data' => $produit
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
     * Enregistre un nouveau produit
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'description' => 'required|string',
                'prix' => 'required|integer|min:0',
                'categorie_id' => 'required|integer',
                'unite_id' => 'required|integer',
                'photos' => 'sometimes|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Créer le dossier s'il n'existe pas
            $path = storage_path('app/public/default/images');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Création du produit
            $produit = Produit::create([
                'nom' => $request->nom,
                'description' => $request->description,
                'prix' => $request->prix,
                'categorie_id' => $request->categorie_id,
                'unite_id' => $request->unite_id
            ]);

            // Traitement des photos
            $photos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if ($photo->isValid()) {
                        $result = $this->uploadPhoto($photo, $produit->id);
                        $photos[] = $result;
                    }
                }
            }

            return response()->json([
                'status_message' => 'Produit créé avec succès',
                'status_code' => 201,
                'data' => [
                    'produit' => $produit,
                    'photos' => $photos
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status_message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * ========== MÉTHODES DE GESTION DES PHOTOS ==========
     */

    /**
     * 1. AJOUTER DES PHOTOS à un produit existant (même sans photos)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPhotos(Request $request, $id)
    {
        try {
            // Vérifier si le produit existe
            $produit = Produit::find($id);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Validation des photos
            $validator = Validator::make($request->all(), [
                'photos' => 'required|array',
                'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Créer le dossier s'il n'existe pas
            $path = storage_path('app/public/default/images');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Traitement des photos
            $photos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if ($photo->isValid()) {
                        $result = $this->uploadPhoto($photo, $produit->id);
                        $photos[] = $result;
                    }
                }
            }

            // Récupérer toutes les photos du produit
            $toutesLesPhotos = Photoproduit::where('produit_id', $produit->id)->get();
            $photosUrls = $this->formatPhotosUrls($toutesLesPhotos);

            return response()->json([
                'status_message' => 'Photos ajoutées avec succès',
                'status_code' => 200,
                'data' => [
                    'produit_id' => $produit->id,
                    'produit_nom' => $produit->nom,
                    'photos_ajoutees' => $photos,
                    'total_photos' => count($toutesLesPhotos),
                    'toutes_les_photos' => $photosUrls
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
     * 2. SUPPRIMER UNE PHOTO spécifique
     * 
     * @param int $produitId
     * @param int $photoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePhoto($produitId, $photoId)
    {
        try {
            // Vérifier si le produit existe
            $produit = Produit::find($produitId);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Vérifier si la photo existe et appartient au produit
            $photo = Photoproduit::where('id', $photoId)
                ->where('produit_id', $produitId)
                ->first();

            if (!$photo) {
                return response()->json([
                    'status_message' => 'Photo non trouvée',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Supprimer le fichier physique
            $filePath = storage_path('app/public/default/images/' . $photo->nom_du_fichier);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Supprimer l'enregistrement
            $photo->delete();

            // Récupérer les photos restantes
            $photosRestantes = Photoproduit::where('produit_id', $produitId)->get();
            $photosUrls = $this->formatPhotosUrls($photosRestantes);

            return response()->json([
                'status_message' => 'Photo supprimée avec succès',
                'status_code' => 200,
                'data' => [
                    'produit_id' => $produitId,
                    'produit_nom' => $produit->nom,
                    'photo_supprimee_id' => $photoId,
                    'photos_restantes' => $photosUrls,
                    'total_photos_restantes' => count($photosRestantes)
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
     * 3. SUPPRIMER TOUTES LES PHOTOS d'un produit
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAllPhotos($id)
    {
        try {
            // Vérifier si le produit existe
            $produit = Produit::find($id);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Récupérer toutes les photos
            $photos = Photoproduit::where('produit_id', $id)->get();
            $count = count($photos);

            // Supprimer chaque photo (fichier + base)
            foreach ($photos as $photo) {
                $filePath = storage_path('app/public/default/images/' . $photo->nom_du_fichier);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $photo->delete();
            }

            return response()->json([
                'status_message' => 'Toutes les photos ont été supprimées',
                'status_code' => 200,
                'data' => [
                    'produit_id' => $id,
                    'produit_nom' => $produit->nom,
                    'photos_supprimees' => $count
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
     * 4. MODIFIER LES PHOTOS (remplacer toutes les photos)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePhotos(Request $request, $id)
    {
        try {
            // Vérifier si le produit existe
            $produit = Produit::find($id);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Validation des nouvelles photos
            $validator = Validator::make($request->all(), [
                'photos' => 'required|array',
                'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            // 1. Supprimer toutes les anciennes photos
            $anciennesPhotos = Photoproduit::where('produit_id', $id)->get();
            foreach ($anciennesPhotos as $photo) {
                $filePath = storage_path('app/public/default/images/' . $photo->nom_du_fichier);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $photo->delete();
            }

            // 2. Ajouter les nouvelles photos
            $path = storage_path('app/public/default/images');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $nouvellesPhotos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if ($photo->isValid()) {
                        $result = $this->uploadPhoto($photo, $produit->id);
                        $nouvellesPhotos[] = $result;
                    }
                }
            }

            return response()->json([
                'status_message' => 'Photos mises à jour avec succès',
                'status_code' => 200,
                'data' => [
                    'produit_id' => $id,
                    'produit_nom' => $produit->nom,
                    'anciennes_photos_supprimees' => count($anciennesPhotos),
                    'nouvelles_photos' => $nouvellesPhotos,
                    'total_photos' => count($nouvellesPhotos)
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
     * 5. RÉCUPÉRER TOUTES LES PHOTOS d'un produit
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPhotos($id)
    {
        try {
            $produit = Produit::find($id);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            $photos = Photoproduit::where('produit_id', $id)->get();
            $photosUrls = $this->formatPhotosUrls($photos);

            return response()->json([
                'status_message' => 'Photos récupérées avec succès',
                'status_code' => 200,
                'data' => [
                    'produit_id' => $id,
                    'produit_nom' => $produit->nom,
                    'photos' => $photosUrls,
                    'total' => count($photos)
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
     * 6. DÉFINIR UNE PHOTO PRINCIPALE
     * 
     * @param int $produitId
     * @param int $photoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function setMainPhoto($produitId, $photoId)
    {
        try {
            $produit = Produit::find($produitId);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            $photo = Photoproduit::where('id', $photoId)
                ->where('produit_id', $produitId)
                ->first();

            if (!$photo) {
                return response()->json([
                    'status_message' => 'Photo non trouvée',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Si vous avez un champ 'is_principal' dans votre table photoproduits
            // Sinon, vous pouvez stocker l'ID de la photo principale dans la table produits
            // Pour cet exemple, je suppose que vous avez un champ 'photo_principale_id' dans produits
            
            // $produit->photo_principale_id = $photoId;
            // $produit->save();

            return response()->json([
                'status_message' => 'Photo principale définie avec succès',
                'status_code' => 200,
                'data' => [
                    'produit_id' => $produitId,
                    'photo_principale_id' => $photoId,
                    'photo_url' => asset('storage/default/images/' . $photo->nom_du_fichier)
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
     * Met à jour un produit
     */
    public function update(Request $request, $id)
    {
        try {
            $produit = Produit::find($id);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'prix' => 'sometimes|integer|min:0',
                'categorie_id' => 'sometimes|integer',
                'unite_id' => 'sometimes|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_message' => 'Erreur de validation',
                    'status_code' => 422,
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('nom')) $produit->nom = $request->nom;
            if ($request->has('description')) $produit->description = $request->description;
            if ($request->has('prix')) $produit->prix = $request->prix;
            if ($request->has('categorie_id')) $produit->categorie_id = $request->categorie_id;
            if ($request->has('unite_id')) $produit->unite_id = $request->unite_id;
            
            $produit->save();

            return response()->json([
                'status_message' => 'Produit mis à jour avec succès',
                'status_code' => 200,
                'data' => $produit
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
     * Supprime un produit
     */
    public function destroy(Request $request, $id)
    {
        try {
            $produit = Produit::find($id);
            
            if (!$produit) {
                return response()->json([
                    'status_message' => 'Produit non trouvé',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            // Supprimer les photos
            $photos = Photoproduit::where('produit_id', $produit->id)->get();
            foreach ($photos as $photo) {
                $filePath = storage_path('app/public/default/images/' . $photo->nom_du_fichier);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $photo->delete();
            }

            $produit->delete();

            return response()->json([
                'status_message' => 'Produit supprimé avec succès',
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
     * Derniers produits
     */
    public function latest(Request $request)
    {
        try {
            $limit = $request->query('limit', 20);
            
            $produits = Produit::orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
            
            foreach ($produits as $produit) {
                $produit->photos = Photoproduit::where('produit_id', $produit->id)->get();
            }
            
            return response()->json([
                'status_message' => 'Produits récents récupérés',
                'status_code' => 200,
                'data' => [
                    'items' => $produits,
                    'total' => $produits->count()
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
     * Pagination et recherche
     */
    public function paginate(Request $request)
    {
        try {
            $search = $request->query('search');
            $categoryId = $request->query('categorie_id');
            $perPage = $request->query('per_page', 15);

            $query = Produit::query();

            if ($categoryId) {
                $query->where('categorie_id', $categoryId);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $produits = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            foreach ($produits as $produit) {
                $produit->photos = Photoproduit::where('produit_id', $produit->id)->get();
            }

            return response()->json([
                'status_message' => 'Produits récupérés',
                'status_code' => 200,
                'data' => [
                    'items' => $produits->items(),
                    'total' => $produits->total(),
                    'per_page' => $produits->perPage(),
                    'current_page' => $produits->currentPage(),
                    'last_page' => $produits->lastPage()
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
     * ========== MÉTHODES PRIVÉES ==========
     */

    /**
     * Upload d'une photo
     */
    private function uploadPhoto($file, $produitId)
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = storage_path('app/public/default/images');
        
        $file->move($path, $fileName);
        
        $photo = Photoproduit::create([
            'nom_du_fichier' => $fileName,
            'produit_id' => $produitId
        ]);

        return [
            'id' => $photo->id,
            'nom' => $fileName,
            'url' => asset('storage/default/images/' . $fileName)
        ];
    }

    /**
     * Formater les URLs des photos
     */
    private function formatPhotosUrls($photos)
    {
        $result = [];
        foreach ($photos as $photo) {
            $result[] = [
                'id' => $photo->id,
                'nom' => $photo->nom_du_fichier,
                'url' => asset('storage/default/images/' . $photo->nom_du_fichier)
            ];
        }
        return $result;
    }
}