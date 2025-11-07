<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionReceiptReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today();

        // Fecha de inicio: Lunes de la semana pasada
        $startDate = $today->copy()->subWeek()->startOfWeek(Carbon::MONDAY);

        // Fecha de fin: Domingo de la semana actual
        $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Generar todas las fechas del rango (14 días)
        $allDates = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $allDates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        $sql = <<<SQL
        SELECT
            COALESCE(o.IPROD, r.IPROD)                                   AS "Item Number",
            COALESCE(o.ICLAS, r.ICLAS)                                   AS "Item Class",
            COALESCE(o.PDDTE, CAST(r.TTDTE AS INTEGER))                  AS "Date",
            o.VENDOR                                                     AS "Vendor Number",
            o.VNDNAM                                                     AS "Vendor Name",
            COALESCE(o.WWRKC, r.WWRKC)                                   AS "Work Center Number",
            COALESCE(o.WDESC, r.WDESC)                                   AS "Work Center Name",
            COALESCE(o."Total Quantity Ordered", 0)                      AS "Ordered Qty",
            COALESCE(r."Total Transaction Quantity", 0)                  AS "Received Qty"
        FROM (
            SELECT
                A.VENDOR,
                A.VNDNAM,
                L.WWRKC,
                L.WDESC,
                I.IPROD,
                I.ICLAS,
                H.PDDTE,
                SUM(H.PQORD) AS "Total Quantity Ordered"
            FROM LX834F01.HPO H
            JOIN LX834F01.AVM A ON H.PVEND = A.VENDOR
            LEFT JOIN LX834F01.IIM I ON H.PPROD = I.IPROD
            LEFT JOIN LX834F01.FRT F ON I.IPROD = F.RPROD
            LEFT JOIN LX834F01.LWK L ON F.RWRKC = L.WWRKC
            WHERE TRIM(H.PCLAS) IN ('P0','P1','T1','T2','T3','S1')
            AND H.PDDTE BETWEEN ? AND ?
            GROUP BY A.VENDOR, A.VNDNAM, L.WWRKC, L.WDESC, I.IPROD, I.ICLAS, H.PDDTE
        ) o
        FULL OUTER JOIN (
            SELECT
                L.WWRKC,
                L.WDESC,
                I.IPROD,
                I.ICLAS,
                T.TTDTE,
                SUM(T.TQTY) AS "Total Transaction Quantity"
            FROM LX834F01.ITH T
            LEFT JOIN LX834F01.IIM I ON T.TPROD = I.IPROD
            LEFT JOIN LX834F01.FRT F ON I.IPROD = F.RPROD
            LEFT JOIN LX834F01.LWK L ON F.RWRKC = L.WWRKC
            WHERE TRIM(T.TTYPE) IN ('U','U3')
            AND T.TTDTE BETWEEN ? AND ?
            GROUP BY L.WWRKC, L.WDESC, I.IPROD, I.ICLAS, T.TTDTE
        ) r
            ON o.IPROD = r.IPROD
        AND o.PDDTE = CAST(r.TTDTE AS INTEGER)
        ORDER BY "Item Number" ASC, "Date" ASC
        SQL;

        $results = DB::connection('infor-live')->select($sql, [
            $startDate->format('Ymd'),
            $endDate->format('Ymd'),
            $startDate->format('Ymd'),
            $endDate->format('Ymd'),
        ]);

        // --- Procesar resultados ---
        $rows = [];
        foreach ($results as $row) {
            $itemNumber = trim($row->{"Item Number"} ?? $row->IPROD ?? '');
            $itemClass  = trim($row->{"Item Class"} ?? $row->ICLAS ?? '');
            $dateYmd    = (string) ($row->{"Date"} ?? $row->DATE ?? '');
            $vendorNo   = trim($row->{"Vendor Number"} ?? $row->VENDOR ?? '');
            $vendorName = trim($row->{"Vendor Name"} ?? $row->VNDNAM ?? '');
            $wrkNo      = trim($row->{"Work Center Number"} ?? $row->WWRKC ?? '');
            $wrkName    = trim($row->{"Work Center Name"} ?? $row->WDESC ?? '');

            $orderedQty  = (float) ($row->{"Ordered Qty"} ?? $row->TOTAL_QUANTITY_ORDERED ?? 0);
            $receivedQty = (float) ($row->{"Received Qty"} ?? $row->TOTAL_TRANSACTION_QUANTITY ?? 0);

            $percentage = $orderedQty > 0
                ? round(($receivedQty / $orderedQty) * 100, 2)
                : 0;

            // Convertir fecha de Ymd a Y-m-d
            $dateFormatted = null;
            if (preg_match('/^\d{8}$/', $dateYmd)) {
                try {
                    $dateFormatted = Carbon::createFromFormat('Ymd', $dateYmd)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $dateFormatted = null;
                }
            }

            $rows[] = [
                'item_number'       => $itemNumber,
                'item_class'        => $itemClass,
                'date_ymd'          => $dateYmd,
                'date_formatted'    => $dateFormatted,
                'vendor_number'     => $vendorNo,
                'vendor_name'       => $vendorName,
                'work_center_no'    => $wrkNo,
                'work_center_name'  => $wrkName,
                'ordered_qty'       => $orderedQty,
                'received_qty'      => $receivedQty,
                'percentage'        => $percentage,
            ];
        }

        // --- Crear estructura con TODAS las fechas ---
        $grouped = collect($allDates)->mapWithKeys(function ($date) use ($rows) {
            // Filtrar items de esta fecha
            $itemsForDate = collect($rows)->filter(function ($row) use ($date) {
                return $row['date_formatted'] === $date;
            });

            // Totales del día
            $orderedTotal  = $itemsForDate->sum('ordered_qty');
            $receivedTotal = $itemsForDate->sum('received_qty');
            $percentageTotal = $orderedTotal > 0 ? round(($receivedTotal / $orderedTotal) * 100, 2) : null;

            // Agrupar items por número de parte
            $itemsByPart = $itemsForDate->groupBy('item_number')->map(function ($perPart, $part) {
                return [
                    'item_number'      => $part,
                    'item_class'       => $perPart->first()['item_class'],
                    'vendor_number'    => $perPart->first()['vendor_number'],
                    'vendor_name'      => $perPart->first()['vendor_name'],
                    'work_center_no'   => $perPart->first()['work_center_no'],
                    'work_center_name' => $perPart->first()['work_center_name'],
                    'ordered_qty'      => $perPart->sum('ordered_qty'),
                    'received_qty'     => $perPart->sum('received_qty'),
                    'percentage'       => $perPart->sum('ordered_qty') > 0
                        ? round(($perPart->sum('received_qty') / $perPart->sum('ordered_qty')) * 100, 2)
                        : 0,
                ];
            })->values();

            return [
                $date => [
                    'date'             => $date,
                    'ordered_total'    => $orderedTotal,
                    'received_total'   => $receivedTotal,
                    'percentage_total' => $percentageTotal,
                    'items'            => $itemsByPart,
                ]
            ];
        });

        $final = $grouped->values();

        return view('test', ['final' => $final]);
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
