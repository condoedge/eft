<?php

namespace Condoedge\Eft\Models;

use App\Models\Eft\EftFile;
use Kompo\Model as KompoModel;
use App\Models\Teams\Team;

class EftLine extends KompoModel
{
    public const EFT_STATUS_PENDING = 1;
    public const EFT_STATUS_SUCCEEDED = 10;
    public const EFT_STATUS_FAILED = 20;

    /* RELATIONSHIPS */
    public function eftFile()
    {
        return $this->belongsTo(EftFile::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function counterpartyable()
    {
        return $this->morphTo();
    }
    
    /* ATTRIBUTES */
    public function getLineDisplayAttribute()
    {
        return $this->counterpartyable?->name;
    }
    

    /* CALCULATED FIELDS */
    

    /* SCOPES */
    

    /* ELEMENTS */


    /* ACTIONS */
    public function setCounterparty($line)
    {
        //Override in app
    }

    public function postCreateActions($line)
    {
        //Override in app
    }

}
