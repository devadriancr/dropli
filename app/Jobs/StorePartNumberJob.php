<?php

namespace App\Jobs;

use App\Models\ItemClass;
use App\Models\PartNumber;
use App\Models\Project;
use App\Models\StandardPack;
use App\Models\WorkCenter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StorePartNumberJob implements ShouldQueue
{
    use Queueable;

    protected $partNumber;
    protected $partName;
    protected $itemClass;
    protected $project;
    protected $isObsolete;
    protected $standardPack;
    protected $quantityStandardPack;

    /**
     * Create a new job instance.
     */
    public function __construct($partNumber, $partName, $itemClass, $project, $isObsolete, $standardPack, $quantityStandardPack)
    {
        $this->partNumber =  $partNumber;
        $this->partName =  $partName;
        $this->itemClass =  $itemClass;
        $this->project =  $project;
        $this->isObsolete = $isObsolete;
        $this->standardPack =  $standardPack;
        $this->quantityStandardPack = $quantityStandardPack;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $partNumber = PartNumber::query()->where([['number', $this->partNumber], ['name', $this->partName]])->first();
        $itemClass = ItemClass::query()->where('abbreviation', $this->itemClass)->first();
        $standardPack = StandardPack::query()->where('name', $this->standardPack)->first();

        if ($partNumber !== null) {
            $partNumber->update([
                'number' => $this->partNumber,
                'name' => $this->partName,
                'item_class_id' => $itemClass->id,
                'standard_pack_id' => $standardPack ? $standardPack->id : null,
                 'standard_pack_quantity' => $this->quantityStandardPack ?? null,
                'is_obsolete' => ($this->isObsolete == "OBSOLETE") ? true : false,
            ]);
        } else {
            $partNumber = PartNumber::create([
                'number' => $this->partNumber,
                'name' => $this->partName,
                'item_class_id' => $itemClass->id,
                'standard_pack_id' => $standardPack ? $standardPack->id : null,
                'standard_pack_quantity' => $this->quantityStandardPack ?? null,
                'is_obsolete' => ($this->isObsolete == "OBSOLETE") ? true : false,
            ]);
        }

        $routingMaster = DB::connection('infor-live')
            ->table('LX834F01.FRT')
            ->select([
                'LX834F01.LWK.WWRKC AS workNumber',
                'LX834F01.LWK.WDESC AS workName',
                'LX834F01.FRT.RLAB AS productionRate',
            ])
            ->join('LX834F01.IIM', 'LX834F01.IIM.IPROD', '=', 'LX834F01.FRT.RPROD')
            ->join('LX834F01.LWK', 'LX834F01.LWK.WWRKC', '=', 'LX834F01.FRT.RWRKC')
            ->where('LX834F01.IIM.IPROD', '=', $partNumber->number)
            ->first();

        if ($routingMaster !== null) {
            $workCenter = WorkCenter::query()->where([['number', trim($routingMaster->workNumber)], ['name', trim($routingMaster->workName)]])->first();

            if ($workCenter !== null) {
                $partNumber->update([
                    'work_center_id' => $workCenter->id,
                    'production_rate' => $routingMaster->productionRate
                ]);
            } else {
                Log::warning("StorePartNumberJob.- No se encontró WorkCenter con number: {$routingMaster->workNumber} y name: {$routingMaster->workName}");
            }
        }

        switch ($this->project) {
            case '1':
            case '10':
            case '12':
            case '123':
            case '13':
            case '2':
            case '20':
            case '23':
            case '3':
            case '3Y':
            case '4':
            case '45':
            case '47':
            case '5':
            case '56':
            case '57':
            case '6':
            case '7':
            case '710':
            case '79':
            case '8':
            case '811':
            case '9':
                $project = Project::where('code', $this->project)->pluck('id')->toArray();
                $partNumber->projects()->sync($project);
                break;
            default:
                // Log::notice("StorePartNumberJob.- Sin Proyecto No. Parte : " . $this->partNumber . ", Proyecto : " . $this->project);
                break;
        }
    }
}
