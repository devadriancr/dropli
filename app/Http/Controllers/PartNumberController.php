<?php

namespace App\Http\Controllers;

use App\Models\PartNumber;
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
