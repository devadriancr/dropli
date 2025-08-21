<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FSO extends Model
{
    protected $connection = 'infor-live';
    protected $table = 'LX834F01.FSO';

    protected $fillable = [
        'SPROD',
        'SQREQ',
        'SRDTE',
        'SOCNO'
    ];

    public static function getPartNumberByOrder(string $orderNumber): ?string
    {
        $partNumber = FSO::query()->selectRaw('TRIM(SPROD) AS part_number')->where('SORD', $orderNumber)->value('PART_NUMBER');

        return $partNumber ? trim($partNumber) : null;
    }
}
