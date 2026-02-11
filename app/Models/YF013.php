<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class YF013 extends Model
{
    protected $connection = 'infor-proto';
    protected $table = 'LX834FU02.YF013';

    protected $fillable = [
        'YFWRKC',
        'YFWRKN',
        'YFRDTE',
        'YFSHFT',
        'YFPPNO',
        'YFPROD',
        'YFSTIM',
        'YFETIM',
        'YFSDT',
        'YFEDT',
        'YFQPLA',
        'YFQPRO',
        'YFQSCR',
        'YFSCRE',
        'YFCRDT',
        'YFCRTM',
        'YFCRUS',
        'YFCRWS',
        'YFFIL1',
        'YFFIL2',
    ];

    /**
     *
     */
    public static function sendToInfor($productionPlan, $accumulatedScrap)
    {
        $now = Carbon::now();

        Log::debug('Sending data to Infor', [
            'YFWRKC' => $productionPlan->partNumber->workCenter->number ?? '',
            'YFWRKN' => $productionPlan->partNumber->workCenter->name ?? '',
            'YFRDTE' => $productionPlan->planned_date
                ? Carbon::parse($productionPlan->planned_date)->format('Ymd')
                : '',
            'YFSHFT' => $productionPlan->shift->abbreviation ?? '',
            'YFPPNO' => '',
            'YFSORD' => $productionPlan->shop_order_number ?? '',
            'YFPROD' => $productionPlan->partNumber->number ?? '',
            'YFSTIM' => '',
            'YFETIM' => '',
            'YFSDT' => '',
            'YFEDT' => '',
            'YFQPLA' => $productionPlan->planned_quantity ?: $productionPlan->produced_quantity,
            'YFQPRO' => $productionPlan->produced_quantity ?? 0,
            'YFQSCR' => $accumulatedScrap ?? 0,
            'YFSCRE' => ($accumulatedScrap ?? 0) == 0 ? '' : 'RJ',
            'YFCRDT' => $now->format('Ymd'),
            'YFCRTM' => $now->format('His'),
            'YFCRUS' => '',
        ]);

        return YF013::query()
            ->insert([
                'YFWRKC' => $productionPlan->partNumber->workCenter->number ?? '',
                'YFWRKN' => $productionPlan->partNumber->workCenter->name ?? '',
                'YFRDTE' => $productionPlan->planned_date
                    ? Carbon::parse($productionPlan->planned_date)->format('Ymd')
                    : '',
                'YFSHFT' => $productionPlan->shift->abbreviation ?? '',
                'YFPPNO' => '',
                'YFSORD' => $productionPlan->shop_order_number ?? '',
                'YFPROD' => $productionPlan->partNumber->number ?? '',
                'YFSTIM' => '',
                'YFETIM' => '',
                'YFSDT' => '',
                'YFEDT' => '',
                'YFQPLA' => $productionPlan->planned_quantity ?: $productionPlan->produced_quantity,
                'YFQPRO' => $productionPlan->produced_quantity ?? 0,
                'YFQSCR' => $accumulatedScrap ?? 0,
                'YFSCRE' => ($accumulatedScrap ?? 0) == 0 ? '' : 'RJ',
                'YFCRDT' => $now->format('Ymd'),
                'YFCRTM' => $now->format('His'),
                'YFCRUS' => '',
            ]);
    }

    /**
     *
     */
    public static function executeInforProcess()
    {
        try {
            $conn = odbc_connect("Driver={Client Access ODBC Driver (32-bit)};System=192.168.200.7;Uid=LXSECOFR;Pwd=LXSECOFR", "", "");

            if ($conn === false) {
                throw new Exception("Error al conectar con la base de datos Infor.");
            } else {
                Log::info("Conexión a Infor establecida correctamente en " . date('Y-m-d H:i:s'));
            }

            $query = "CALL LX834OU02.YSF013C";
            $result = odbc_exec($conn, $query);

            if ($result) {
                Log::info("LX834OU.YSF013C : La consulta se ejecutó con éxito en " . date('Y-m-d H:i:s'));
            } else {
                throw new Exception("LX834OU.YSF013C : Error en la consulta: " . odbc_errormsg($conn));
            }
        } catch (Exception $e) {
            Log::alert($e->getMessage());
        } finally {
            if (isset($conn)) {
                odbc_close($conn);
            }
        }
    }
}
