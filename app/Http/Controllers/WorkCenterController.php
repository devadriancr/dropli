<?php

namespace App\Http\Controllers;

use App\Models\WorkCenter;
use Illuminate\Http\Request;

class WorkCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $workCenters = WorkCenter::with(['area'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('work_centers.name', 'like', "%{$search}%")
                        ->orWhere('work_centers.number', 'like', "%{$search}%")
                        ->orWhereHas('area', function ($q) use ($search) {
                            $q->where('areas.name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('number')
            ->paginate(10);

        return view('work-centers.index')->with([
            'workCenters' => $workCenters,
            'search' => $search
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
