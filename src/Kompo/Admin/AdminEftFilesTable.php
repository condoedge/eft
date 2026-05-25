<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Models\Eft\EftFile;
use Kompo\Table;

class AdminEftFilesTable extends Table
{
    public $containerClass = 'container-fluid';
    public $id = 'admin-eft-files-table';

    protected $tableTitle = 'eft-eft-files';

    public function query()
    {
        return $this->eftFileModel()::orderByDesc('file_creation_no')->with('eftLines');
    }

    public function top()
    {
        $monitorTable = $this->monitorTable();

        return _Rows(
            _FlexBetween(
                _Html($this->tableTitle)->pageTitle()->class('mb-4'),
                _Flex4(
                    !$monitorTable ? null :
                        _Button('eft-display-next-transfers')->outlined()->toggleId('transfers-to-load-table'),
                        _Button('eft-create-file')->icon(_sax('add',18))
                            ->selfCreate('getGenerateEftFileModal')->inModal()
                )->class('mb-4'),
            ),
            !$monitorTable ? null :
                _Rows($monitorTable)->class('mb-4')->id('transfers-to-load-table')
        );
    }

    protected function monitorTable()
    {
        //To override in app
    }

    public function headers()
    {
        return [
            _Th('eft-debit-or-credit'),
            _Th('eft-file-creation-no'),
            _Th('eft-date'),
            _Th('eft-filename'),
            _Th('eft-number-transfers'),
            _Th('eft-amount'),
            _Th('eft-download'),
            _Th('eft-confirm-transaction'),
            _Th('eft-confirm-acceptance'),
            _Th('eft-confirm-completion'),
            _Th(),
        ];
    }

    public function render($eftFile)
    {
        return _TableRow(
            _Html($eftFile->credit_or_debit),
            _Html($eftFile->file_creation_no),
            _Html($eftFile->run_date),
            _Html($eftFile->filename),
            _Html($eftFile->eftLines->count() - 2),
            _Currency($eftFile->eftLines->sum('line_amount')),
            $this->downloadCell($eftFile),
            $this->depositCell($eftFile),
            $this->acceptanceCell($eftFile),
            $this->completionCell($eftFile),
            _Delete($eftFile),
        )->selfGet('getEftFileContentModal', ['id' => $eftFile->id])->inModal();
    }

    protected function downloadCell($eftFile)
    {
        return _Link()->icon('download')->href('eft-file.download', ['id' => $eftFile->id])->inNewTab();
    }

    protected function depositCell($eftFile)
    {
        return $eftFile->deposited_at
            ? _Html($eftFile->deposited_at->format('Y-m-d H:i'))
            : _Button('?')->selfPost('markDeposited', ['id' => $eftFile->id])->browse();
    }

    protected function acceptanceCell($eftFile)
    {
        if ($eftFile->accepted_at) {
            return _Html($eftFile->accepted_at->format('Y-m-d H:i'));
        }

        if ($eftFile->rejected_at) {
            return _Html($eftFile->rejected_at->format('Y-m-d H:i'))->icon('icon-times');
        }

        return _Flex2(
            _Button()->icon('icon-check')->selfUpdate('getAcceptationModal', ['id' => $eftFile->id])->inModal(),
            _Button()->icon('icon-times')->selfPost('markRejected', ['id' => $eftFile->id])->browse(),
        );
    }

    protected function completionCell($eftFile)
    {
        if ($eftFile->completed_at) {
            return _Rows(
                _Html($eftFile->completed_at->format('Y-m-d H:i')),
                _Currency($eftFile->completed_amount)->class('text-sm font-bold'),
            );
        }

        return _Button('eft-complete?')->selfUpdate('getCompletionModal', ['id' => $eftFile->id])->inModal();
    }

    protected function eftFileModel(): string
    {
        return EftFile::class;
    }

    protected function generateFormModal(): string
    {
        return AdminEftFileGenerateForm::class;
    }

    protected function contentTableModal(): string
    {
        return AdminEftFileContentTable::class;
    }

    protected function acceptanceModal(): string
    {
        return AdminEftAcceptedModal::class;
    }

    public function getGenerateEftFileModal()
    {
        $class = $this->generateFormModal();

        return new $class();
    }

    public function getEftFileContentModal($id)
    {
        $class = $this->contentTableModal();

        return new $class([
            'eft_file_id' => $id,
        ]);
    }

    public function markDeposited($id)
    {
        $eftFile = $this->eftFileModel()::findOrFail($id);
        $eftFile->markDeposited();
    }

    public function getAcceptationModal($id)
    {
        $class = $this->acceptanceModal();

        return new $class($id);
    }

    public function markRejected($id)
    {
        $eftFile = $this->eftFileModel()::findOrFail($id);
        $eftFile->markRejected();
    }

    public function getCompletionModal($id)
    {
        $class = $this->contentTableModal();

        return new $class([
            'eft_file_id' => $id,
        ]);
    }
}
