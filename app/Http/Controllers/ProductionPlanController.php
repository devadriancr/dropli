<?php

namespace App\Http\Controllers;

use App\Models\ProductionPlan;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $now = now();
        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $productionPlans = ProductionPlan::with([
            'partNumber.standardPack',
            'status',
            'shift'
        ])
            ->whereBetween('planned_date', [$startOfWeek, $endOfWeek])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('shop_order_number', 'like', "%$search%")
                        ->orWhere('planned_date', 'like', "%$search%")
                        ->orWhere('planned_quantity', 'like', "%$search%")
                        ->orWhereHas('partNumber', function ($q2) use ($search) {
                            $q2->where('number', 'like', "%$search%");
                        })
                        ->orWhereHas('shift', function ($q3) use ($search) {
                            $q3->where('abbreviation', 'like', "%$search%");
                        })
                        ->orWhereHas('status', function ($q4) use ($search) {
                            $q4->where('label', 'like', "%$search%");
                        });
                });
            })
            ->orderBy('planned_date', 'asc')
            ->orderBy(
                Shift::select('abbreviation')
                    ->whereColumn('shifts.id', 'production_plans.shift_id')
                    ->limit(1)
            )
            ->paginate(10)
            ->withQueryString();

        return view('production-plans.index', [
            'productionPlans' => $productionPlans,
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
