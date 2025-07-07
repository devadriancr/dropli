<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZCC extends Model
{
    protected $connection = 'infor-live';
    protected $table = 'LX834F01.ZCC';

    protected $fillable = [
        'CCID',
        'CCTABL',
        'CCCODE',
        'CCLANG',
        'CCALTC',
        'CCDESC',
        'CCSDSC',
        'CCNOT1',
        'CCNOT2',
        'CCUDC1',
        'CCUDC2',
        'CCUDC3',
        'CCRESV',
        'CCENDT',
        'CCENTM',
        'CCENUS',
        'CCMNDT',
        'CCMNTM',
        'CCMNUS',
        'CCALCR',
        'CCDESR',
        'CCSDSR',
        'CCNOTR',
        'CCREVR'
    ];
}
