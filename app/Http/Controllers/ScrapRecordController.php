<?php

namespace App\Http\Controllers;

use App\Models\ScrapRecord;
use App\Models\PartNumber;
use App\Models\ScrapReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScrapRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $scrapRecords = ScrapRecord::with(['partNumber', 'scrapReason'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('partNumber', function ($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })->orWhereHas('scrapReason', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('scrap-records.index', compact('scrapRecords', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workCenterIds = Auth::user()->workCenters->pluck('id');
        $partNumbers = PartNumber::whereIn('work_center_id', $workCenterIds)
            ->orderBy('number')
            ->get();
        $scrapReasons = ScrapReason::orderBy('code')->get();

        return view('scrap-records.create', compact('partNumbers', 'scrapReasons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number_id' => 'required|exists:part_numbers,id',
            'scrap_reason_id' => 'required|exists:scrap_reasons,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            ScrapRecord::create($validated);

            return redirect()->route('scrap-records.index')
                ->with('success', 'Registro de scrap creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el registro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScrapRecord $scrapRecord)
    {
        $partNumbers = PartNumber::orderBy('number')->get();
        $scrapReasons = ScrapReason::orderBy('code')->get();

        return view('scrap-records.edit', compact('scrapRecord', 'partNumbers', 'scrapReasons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScrapRecord $scrapRecord)
    {
        $validated = $request->validate([
            'part_number_id' => 'required|exists:part_numbers,id',
            'scrap_reason_id' => 'required|exists:scrap_reasons,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $scrapRecord->update($validated);

            return redirect()->route('scrap-records.index')
                ->with('success', 'Registro de scrap actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el registro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScrapRecord $scrapRecord)
    {
        try {
            $scrapRecord->delete();

            return redirect()->route('scrap-records.index')
                ->with('success', 'Registro de scrap eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el registro: ' . $e->getMessage());
        }
    }
}
