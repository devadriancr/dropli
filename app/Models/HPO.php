<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HPO extends Model
{
    protected $connection = 'infor-live';
    protected $table = 'LX834F01.HPO';

    protected $fillable = [
        'PORD',
        'PLINE',
        'PPROD',
        'PVEND',
        'PQORD',
        'PQREC',

    ];

    public static function getPartNumberByOrder(string $orderNumber): ?string
    {
        $purchaseOrderNumber = substr($orderNumber, 0, 8);
        $purchaseLineNumber = substr($orderNumber, 8, 12);

        $partNumber = HPO::query()->selectRaw('TRIM(PPROD) AS part_number')->where('PORD', $purchaseOrderNumber)->where('PLINE', $purchaseLineNumber)->value('PART_NUMBER');

        return $partNumber ? trim($partNumber) : null;
    }
}
