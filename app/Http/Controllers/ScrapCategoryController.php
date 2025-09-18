<?php

namespace App\Http\Controllers;

use App\Models\ScrapCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScrapCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $scrapCategories = ScrapCategory::when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('scrap-categories.index', compact('scrapCategories', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('scrap-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:scrap_categories,name'
            ],
            'description' => 'nullable|string|max:500'
        ]);

        try {
            ScrapCategory::create($validated);
            return redirect()->route('scrap-categories.index')
                ->with('success', 'Categoría de scrap creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la categoría: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScrapCategory $scrapCategory)
    {
        return view('scrap-categories.edit', compact('scrapCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScrapCategory $scrapCategory)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('scrap_categories')->ignore($scrapCategory->id)
            ],
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $scrapCategory->update($validated);
            return redirect()->route('scrap-categories.index')
                ->with('success', 'Categoría de scrap actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la categoría: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScrapCategory $scrapCategory)
    {
        try {
            // Verificar si tiene razones de scrap asociadas
            if ($scrapCategory->scrapReasons()->count() > 0) {
                return redirect()->route('scrap-categories.index')
                    ->with('error', 'No se puede eliminar la categoría porque tiene razones de scrap asociadas.');
            }

            $scrapCategory->delete();
            return redirect()->route('scrap-categories.index')
                ->with('success', 'Categoría de scrap eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('scrap-categories.index')
                ->with('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
    }
}
