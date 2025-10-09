<?php

namespace Condoedge\Eft\Models;

use App\Models\Eft\EftFile;
use Kompo\Model as KompoModel;
use App\Models\Teams\Team;

class EftLine extends KompoModel
{
     //EFT statuses not used anywhere yet
    public const EFT_STATUS_PENDING = 1;
    public const EFT_STATUS_SUCCEEDED = 10;
    public const EFT_STATUS_FAILED = 20;

    public const ERROR_OPTION_REJECT = 1;
    public const ERROR_OPTION_RETURN = 2;

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

    public function getUsedNameAttribute()
    {
        return substr($this->record, 104, 30);
    }
    

    /* CALCULATED FIELDS */
    public static function errorOptions()
    {
        return [
            static::ERROR_OPTION_REJECT => __('eft-rejected'),
            static::ERROR_OPTION_RETURN => __('eft-returned'),
        ];
    }
    

    /* SCOPES */
    public function scopeCausingErrors($query)
    {
        $query->where('caused_error', 1);
    }

    public function scopeLinePassing($query)
    {
        $query->where(fn($q) => $q->where('caused_error', 0)->orWhereNull('caused_error'));
    }
    

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
