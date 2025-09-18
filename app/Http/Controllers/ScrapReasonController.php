<?php

namespace App\Http\Controllers;

use App\Models\ScrapReason;
use App\Models\ScrapCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScrapReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $scrapReasons = ScrapReason::with('category')
            ->when($search, function ($query, $search) {
                return $query->where('code', 'like', "%{$search}%")
                           ->orWhere('name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%")
                           ->orWhereHas('category', function ($q) use ($search) {
                               $q->where('name', 'like', "%{$search}%");
                           });
            })
            ->orderBy('code')
            ->paginate(10);

        return view('scrap-reasons.index', compact('scrapReasons', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $scrapCategories = ScrapCategory::orderBy('name')->get();
        return view('scrap-reasons.create', compact('scrapCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:scrap_reasons,code'
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'scrap_category_id' => 'required|exists:scrap_categories,id'
        ]);

        try {
            ScrapReason::create($validated);
            return redirect()->route('scrap-reasons.index')
                ->with('success', 'Razón de scrap creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la razón: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScrapReason $scrapReason)
    {
        $scrapCategories = ScrapCategory::orderBy('name')->get();
        return view('scrap-reasons.edit', compact('scrapReason', 'scrapCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScrapReason $scrapReason)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('scrap_reasons')->ignore($scrapReason->id)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'scrap_category_id' => 'required|exists:scrap_categories,id'
        ]);

        try {
            $scrapReason->update($validated);
            return redirect()->route('scrap-reasons.index')
                ->with('success', 'Razón de scrap actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la razón: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScrapReason $scrapReason)
    {
        try {
            // Verificar si tiene registros de scrap asociados
            if ($scrapReason->scrapRecords()->count() > 0) {
                return redirect()->route('scrap-reasons.index')
                    ->with('error', 'No se puede eliminar la razón porque tiene registros de scrap asociados.');
            }

            $scrapReason->delete();
            return redirect()->route('scrap-reasons.index')
                ->with('success', 'Razón de scrap eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('scrap-reasons.index')
                ->with('error', 'Error al eliminar la razón: ' . $e->getMessage());
        }
    }
}
