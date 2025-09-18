<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ECL extends Model
{
    protected $connection = 'infor-live';
    protected $table = 'LX834F01.ECL';

    protected $fillable = [
        'LORD',
        'LLINE',
        'LPROD',
        'LQORD',
        'LQALL',
        'LQSHP',
        'LUM',
        'LRDTE',
        'LSDTE',
        'CLIDNO',
        'CLCARD'
    ];

    public static function getFinalPartNumber(string $orderNumber): ?string
    {
        $year = Carbon::now()->format('y');

        $partNumber = ECL::query()->selectRaw('TRIM(LPROD) AS part_number')->where('CLIDNO', $year . $orderNumber)->value('PART_NUMBER');

        return $partNumber ? trim($partNumber) : null;
    }
}
