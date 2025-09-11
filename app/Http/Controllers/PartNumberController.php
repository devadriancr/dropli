<?php

namespace App\Http\Controllers;

use App\Models\PartNumber;
use App\Models\Attribute;
use Illuminate\Http\Request;

class PartNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $partNumbers = PartNumber::with([
            'itemClass',
            'standardPack',
            'workCenter.area.department'
        ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    // Buscar en campos directos de PartNumber
                    $q->where('part_numbers.number', 'like', "%{$search}%")
                        ->orWhere('part_numbers.name', 'like', "%{$search}%");

                    // Buscar en relaciones
                    $q->orWhereHas('itemClass', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('abbreviation', 'like', "%{$search}%");
                    })
                        ->orWhereHas('workCenter', function ($q) use ($search) {
                            $q->where('number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhereHas('area', function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                                        ->orWhereHas('department', function ($q) use ($search) {
                                            $q->where('name', 'like', "%{$search}%")
                                                ->orWhere('code', 'like', "%{$search}%");
                                        });
                                });
                        })
                        ->orWhereHas('standardPack', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('projects', function ($q) use ($search) {
                            $q->where('code', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('number', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('part-numbers.index')->with([
            'partNumbers' => $partNumbers,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PartNumber $partNumber)
    {
        // Obtener atributos únicos existentes con sus tipos de datos
        $existingAttributes = Attribute::where('attributable_type', PartNumber::class)
            ->select('attribute_key', 'data_type')
            ->distinct()
            ->get()
            ->groupBy('attribute_key')
            ->map(function ($items) {
                return $items->first()->data_type;
            });

        return view('part-numbers.edit')->with([
            'partNumber' => $partNumber,
            'existingAttributes' => $existingAttributes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PartNumber $partNumber)
    {
        // Validar los datos
        $request->validate([
            'attributes.*.value' => 'required',
            'attributes.*.data_type' => 'required|in:string,integer,double,boolean,array',
            'new_attributes.*.key' => 'sometimes|required',
            'new_attributes.*.value' => 'sometimes|required',
            'new_attributes.*.data_type' => 'sometimes|required|in:string,integer,double,boolean,array',
        ]);

        // Procesar atributos existentes (incluyendo los nuevos que se agregaron desde existentes)
        if ($request->has('attributes')) {
            foreach ($request->attributes as $attributeData) {
                if (isset($attributeData['key']) && isset($attributeData['value'])) {
                    $partNumber->setCustomAttributeValue(
                        $attributeData['key'],
                        $attributeData['value'],
                        $attributeData['data_type']
                    );
                }
            }
        }

        // Procesar nuevos atributos (creados desde cero)
        if ($request->has('new_attributes')) {
            foreach ($request->new_attributes as $attributeData) {
                if (!empty($attributeData['key']) && isset($attributeData['value'])) {
                    $partNumber->setCustomAttributeValue(
                        $attributeData['key'],
                        $attributeData['value'],
                        $attributeData['data_type']
                    );
                }
            }
        }

        // Eliminar atributos marcados para eliminación
        if ($request->has('delete_attributes')) {
            foreach ($request->delete_attributes as $attributeKey) {
                if (!empty($attributeKey)) {
                    $partNumber->attributes()
                        ->where('attribute_key', $attributeKey)
                        ->delete();
                }
            }
        }

        return redirect()->route('part-numbers.index')
            ->with('success', 'Atributos actualizados correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get all unique attribute keys used by part numbers
     */
    public static function getUniqueAttributeKeys()
    {
        return Attribute::where('attributable_type', PartNumber::class)
            ->distinct()
            ->pluck('attribute_key')
            ->sort()
            ->values();
    }
}
