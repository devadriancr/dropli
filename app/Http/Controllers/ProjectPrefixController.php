<?php

namespace App\Http\Controllers;

use App\Models\ProjectPrefix;
use Illuminate\Http\Request;

class ProjectPrefixController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $projectPrefixes = ProjectPrefix::with(['project'])
            ->join('projects', 'project_prefixes.project_id', '=', 'projects.id')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('project_prefixes.prefix_code', 'like', "%{$search}%")
                        ->orWhereHas('project', function ($q2) use ($search) {
                            $q2->where('projects.code', 'like', "%{$search}%")
                                ->orWhere('projects.model', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('projects.model', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('project-prefixes.index', [
            'projectPrefixes' => $projectPrefixes,
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
