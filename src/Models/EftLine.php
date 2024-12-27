<?php

namespace Condoedge\Eft\Models;

use App\Models\Finance\EftFile;
use Kompo\Model as KompoModel;
use App\Models\Teams\Team;

class EftLine extends Model
{
    /* RELATIONSHIPS */
    public function eftFile()
    {
        return $this->belongsTo(EftFile::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
    
    /* ATTRIBUTES */
    

    /* CALCULATED FIELDS */
    

    /* SCOPES */
    

    /* ELEMENTS */


    /* ACTIONS */
    public function postCreateActions($line)
    {
        //Override in app
    }

}
