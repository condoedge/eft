<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Models\Eft\EftLine;
use Kompo\Table;

class AdminEftFileContentTable extends Table
{
    protected $eftFileId;

    public function created()
    {
        $this->eftFileId = $this->prop('eft_file_id');
    }

    public function query()
    {
        return EftLine::where('eft_file_id', $this->eftFileId)->with('team');
    }

    public function headers()
    {
        return [
            _Th('eft-counterparty'),
            _Th('eft-date'),
            _Th('eft-amount'),
            _Th('eft-record'),
            _Th('eft-caused-error?'),
            _Th('eft-error-reason'),
        ];
    }

    public function render($eftLine)
    {
    	return _TableRow(
            _Html($eftLine->line_display),
            _Html($eftLine->line_date),
            _Html($eftLine->line_amount),
            _Html($eftLine->record)
                ->class('text-xs text-gray-500 w-64 h-8 hover:h-auto overflow-hidden')
                ->style('word-break: break-all'),
            _Checkbox()->name('caused_error')->selfPost('markCausedError', ['id' => $eftLine->id])
                ->value($eftLine->caused_error),
            _Input()->name('error_reason')->selfPost('markErrorReason', ['id' => $eftLine->id])
                ->value($eftLine->error_reason),
        );
    }

    public function markCausedError($id)
    {
        $eftLine = EftLine::findOrFail($id);
        $eftLine->caused_error = request('caused_error');
        $eftLine->save();
    }

    public function markErrorReason($id)
    {
        $eftLine = EftLine::findOrFail($id);
        $eftLine->error_reason = request('error_reason');
        $eftLine->save();
    }
}
