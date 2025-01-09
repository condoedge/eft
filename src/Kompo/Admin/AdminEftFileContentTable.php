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

    protected $eftFileId;
    protected $eftFile;

    public $perPage = 50;

    public function created()
    {
        $this->eftFileId = $this->prop('eft_file_id');
        $this->eftFile = EftFile::findOrFail($this->eftFileId);
    }

    public function query()
    {
        return EftLine::where('eft_file_id', $this->eftFileId)->with('team');
    }

    public function top()
    {
        return _Rows(
            _FlexBetween(
                _Html('eft-eft-file-content', null)->class('text-3xl font-semibild'),
                _Panel(
                    $this->getEftTotals(),
                )->id('eft-content-totals-panel'),
            )->class('p-6'),
        );
    }

    public function headers()
    {
        return [
            _Th('eft-counterparty'),
            _Th('eft-date'),
            _Th('eft-display-name'),
            _Th('eft-amount'),
            //_Th('eft-record'),
            _Th('eft-caused-error?'),
            _Th('eft-error-reason'),
        ];
    }

    public function render($eftLine)
    {
    	return _TableRow(
            _Html($eftLine->line_display),
            _Html($eftLine->line_date),
            _Html($eftLine->used_name),
            _Html($eftLine->line_amount),
            /*_Html($eftLine->record)
                ->class('text-xs text-gray-500 w-64 h-8 hover:h-auto overflow-hidden')
                ->style('word-break: break-all'),*/
            _Checkbox()->name('caused_error')->class('mb-0')
                ->selfPost('markCausedError', ['id' => $eftLine->id])->inPanel('eft-content-totals-panel')
                ->value($eftLine->caused_error),
            _Input()->name('error_reason')->class('mb-0')
                ->selfPost('markErrorReason', ['id' => $eftLine->id])->inPanel('eft-content-totals-panel')
                ->value($eftLine->error_reason),
        );
    }

    public function markCausedError($id)
    {
        $eftLine = EftLine::findOrFail($id);
        $eftLine->caused_error = request('caused_error') ? 1 : null;
        $eftLine->save();

        return $this->getEftTotals();
    }

    public function markErrorReason($id)
    {
        $eftLine = EftLine::findOrFail($id);
        $eftLine->error_reason = request('error_reason');
        $eftLine->save();

        return $this->getEftTotals();
    }

    protected function getTotalErrors()
    {
        return $this->query()->causingErrors()->sum('line_amount');
    }

    protected function getTotalPassed()
    {
        return $this->query()->linePassing()->sum('line_amount');
    }

    public function getEftTotals()
    {
        $p = $this->getTotalPassed();
        $e = $this->getTotalErrors();

        return _Rows(
            $this->labelTotal('Total passed', $p),
            $this->labelTotal('Total errors', $e),
            $this->labelTotal('All file', $p + $e)->class('mb-4'),
            $this->eftFile->completed_at ? 
                _Html($this->eftFile->completed_at->format('Y-m-d H:i'))->icon('icon-check') : 
                _Button('eft-complete?')->selfPost('markEftCompleted')->closeModal()->browse('admin-eft-files-table'),
        )->class('card-gray-100 p-4');
    }

    public function markEftCompleted()
    {
        $this->eftFile->checkAmountIsMatchingCompletedAmount($this->eftFile->completed_amount);

        $this->eftFile->markCompleted();
    }

    protected function labelTotal($label, $total)
    {
        return _FlexBetween(
            _Html($label)->class('font-semibold'),
            _Currency($total)->class('ml-2'),
        );
    }
}
