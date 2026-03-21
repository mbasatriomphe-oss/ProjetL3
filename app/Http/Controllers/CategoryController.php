<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Afficher la liste des catégories
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Liste des catégories récupérée avec succès',
            'data' => $categories
        ], Response::HTTP_OK);
    }

    /**
     * Créer une nouvelle catégorie
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), Category::$rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Création de la catégorie
        $category = Category::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès',
            'data' => $category
        ], Response::HTTP_CREATED);
    }

    /**
     * Afficher une catégorie spécifique
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Catégorie récupérée avec succès',
            'data' => $category
        ], Response::HTTP_OK);
    }

    /**
     * Mettre à jour une catégorie spécifique
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validation des données pour la mise à jour
        $validator = Validator::make($request->all(), Category::updateRules($id));

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Mise à jour de la catégorie
        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Catégorie mise à jour avec succès',
            'data' => $category
        ], Response::HTTP_OK);
    }

    /**
     * Supprimer une catégorie spécifique
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès'
        ], Response::HTTP_OK);
    }

    /**
     * Rechercher des catégories par nom
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terme de recherche invalide',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $searchTerm = $request->q;
        
        $categories = Category::where('nom', 'like', '%' . $searchTerm . '%')
                        ->orWhere('description', 'like', '%' . $searchTerm . '%')
                        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Résultats de la recherche',
            'data' => $categories
        ], Response::HTTP_OK);
    }

    /**
     * Rechercher une catégorie par nom exact (utile pour les autocomplétions)
     * 
     * @param  string  $nom
     * @return \Illuminate\Http\Response
     */
    public function searchByName($nom)
    {
        $categories = Category::where('nom', 'like', '%' . $nom . '%')->get();

        return response()->json([
            'success' => true,
            'message' => 'Résultats de la recherche par nom',
            'data' => $categories
        ], Response::HTTP_OK);
    }

    /**
     * Rechercher des catégories avec pagination
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchPaginated(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètres de recherche invalides',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $searchTerm = $request->q;
        $perPage = $request->get('per_page', 15);

        $categories = Category::where('nom', 'like', '%' . $searchTerm . '%')
                        ->orWhere('description', 'like', '%' . $searchTerm . '%')
                        ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Résultats de la recherche paginés',
            'data' => $categories
        ], Response::HTTP_OK);
    }

    /**
     * Récupérer les catégories récentes
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recent(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $categories = Category::orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Catégories récentes récupérées avec succès',
            'data' => $categories
        ], Response::HTTP_OK);
    }

    /**
     * Compter le nombre total de catégories
     * 
     * @return \Illuminate\Http\Response
     */
    public function count()
    {
        $count = Category::count();

        return response()->json([
            'success' => true,
            'message' => 'Nombre de catégories récupéré avec succès',
            'data' => ['total' => $count]
        ], Response::HTTP_OK);
    }
}