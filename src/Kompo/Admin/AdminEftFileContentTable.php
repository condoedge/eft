<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Models\Eft\EftLine;
use App\Models\Eft\EftFile;
use Kompo\Table;

class AdminEftFileContentTable extends Table
{
    public $itemsWrapperClass = 'overflow-y-auto mini-scroll';
    public $style = 'max-height: 95vh';
    public $class = 'px-6 table-sm';
    public $id = 'admin-eft-file-content';

    protected $eftFileId;
    protected $eftFile;
    protected $showCheckboxes;

    public $perPage = 50;

    public function created()
    {
        $this->eftFileId = $this->prop('eft_file_id');
        $this->eftFile = EftFile::findOrFail($this->eftFileId);

        $this->showCheckboxes = $this->eftFile->accepted_at && !$this->eftFile->completed_at;
    }

    public function query()
    {
        return EftLine::where('eft_file_id', $this->eftFileId)->with('team');
    }

    public function top()
    {
        return _Rows(
            _FlexBetween(
                _Rows(
                    _Html('eft-eft-file-content', null)->class('text-3xl font-semibild'),
                    !$this->showCheckboxes ? null : _Button('Add error batch')->config(['withCheckedItemIds' => true])->selfUpdate('markCausedErrorModal')->inModal(),
                ),
                _Panel(
                    $this->getEftTotals(),
                )->id('eft-content-totals-panel'),
            )->class('p-6'),
        );
    }

    public function headers()
    {
        return [
            !$this->showCheckboxes ? _Th() : _CheckAllItems()->class('w-8'),
            _Th('eft-counterparty'),
            _Th('eft-date'),
            _Th('eft-display-name'),
            _Th('eft-amount'),
            //_Th('eft-record'),
            _Th('eft-caused-error?'),
        ];
    }

    public function render($eftLine)
    {
    	return _TableRow(
            !$this->showCheckboxes || !$eftLine->line_amount || $eftLine->caused_error ? _Html() : 
                _Checkbox()->class('mb-0 child-checkbox')->emit('checkItemId', ['id' => $eftLine->id]),
            _Html($eftLine->line_display),
            _Html($eftLine->line_date),
            _Html($eftLine->used_name),
            _Html($eftLine->line_amount),
            /*_Html($eftLine->record)
                ->class('text-xs text-gray-500 w-64 h-8 hover:h-auto overflow-hidden')
                ->style('word-break: break-all'),*/
            
            _Html($eftLine->error_reason)
                ->class($eftLine->caused_error ? 'text-danger' : ''),
        );
    }

    public function markCausedErrorModal()
    {
        return new AdminEftLineErrorModal($this->eftFileId, [
            'eft_line_ids' => request('itemIds'),
        ]);
    }

    public function getEftTotals()
    {
        $p = $this->query()->linePassing()->sum('line_amount');
        $e = $this->query()->causingErrors()->sum('line_amount');

        return _Rows(
            _LabelTotalsEft('Total passed', $p),
            _LabelTotalsEft('Total errors', $e),
            _LabelTotalsEft('All file', $p + $e)->class('mb-4'),
            $this->eftFile->completed_at ? 
                _Html($this->eftFile->completed_at->format('Y-m-d H:i'))->icon('icon-check') : 
                _Button('eft-complete?')->selfUpdate('markEftCompleted')->inModal(),
        )->class('card-gray-100 p-4');
    }

    public function markEftCompleted()
    {
        return new AdminEftCompletionModal($this->eftFileId);
    }
}
