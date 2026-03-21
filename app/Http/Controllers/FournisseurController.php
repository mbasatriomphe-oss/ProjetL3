<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::all();
        return response()->json($fournisseurs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:90',
            'adresse' => 'required|string|max:63',
            'ville' => 'required|string|max:50',
            'pays' => 'required|string|max:50',
            'contact' => 'required|string|max:50'
        ]);

        $fournisseur = Fournisseur::create($validated);
        return response()->json($fournisseur, 201);
    }

    public function show(Fournisseur $fournisseur)
    {
        return response()->json($fournisseur);
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $validated = $request->validate([
            'nom' => 'sometimes|string|max:90',
            'adresse' => 'sometimes|string|max:63',
            'ville' => 'sometimes|string|max:50',
            'pays' => 'sometimes|string|max:50',
            'contact' => 'sometimes|string|max:50'
        ]);

        $fournisseur->update($validated);
        return response()->json($fournisseur);
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();
        return response()->json(null, 204);
    }
}