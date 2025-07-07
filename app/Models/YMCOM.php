<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YMCOM extends Model
{
    protected $connection = 'infor-live';
    protected $table = 'LX834FU01.YMCOM';

    protected $fillable = [
        'MCFPRO',
        'MCFCLS',
        'MCCPRO',
        'MCCCLS',
        'MCQREQ',
        'MCCCTM',
        'MCCUSR',
        'MCCCDT'
    ];
}
