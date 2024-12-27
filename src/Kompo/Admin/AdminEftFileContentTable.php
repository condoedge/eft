<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Models\Eft\EftLine;
use App\Kompo\Common\Table;

class AdminEftFileContentTable extends Table
{
    protected $eftFileId;

    public function created()
    {
        $this->eftFileId = $this->prop('eft_file_id');
    }

    public function query()
    {
        return EftLine::where('eft_file_id', $this->eftFileId)->with('campaign', 'team');
    }

    public function headers()
    {
        return [
            _Th('finance.eft-team'),
            _Th('finance.eft-campaign'),
            _Th('finance.eft-date'),
            _Th('finance.eft-amount'),
            _Th(),
        ];
    }

    public function render($eftLine)
    {
    	return _TableRow(
            _Html($eftLine->team?->name),
            _Html($eftLine->campaign?->name),
            _Html($eftLine->line_date),
            _Html($eftLine->line_amount),
            _Html($eftLine->record)
                ->class('text-xs text-gray-500 w-64 h-8 hover:h-auto overflow-hidden')
                ->style('word-break: break-all'),
        );
    }
}
