<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftLine;
use App\Models\Eft\EftFile;

class AdminEftLineErrorModal extends Modal
{
	protected $_Title = 'eft-write-error';

	public $model = EftFile::class;

	public function created()
	{
		$this->eftLineIds = explode(',', $this->prop('eft_line_ids') ?: '');
		$this->eftLines = EftLine::whereIn('id', $this->eftLineIds)->get();
	}

	public function handle()
	{
		$this->model->runActionsOnErrorLines($this->eftLines, request('error_at'));

		$this->eftLines->each(function ($eftLine) {
			$eftLine->caused_error = 1;
			$eftLine->error_reason = request('error_reason');
			$eftLine->save();
		});
	}

	public function body()
	{
		return _Rows(

            _Rows(
            	_LabelTotalsEft('Total errors', $this->eftLines->sum('line_amount')),
            )->class('card-gray-100 p-4'),

			_Date('eft-error-date')->name('error_at'),
			_Input('eft-error-reason')->name('error_reason'),
			_SubmitButton()->refresh('admin-eft-file-content'),
		);
	}

	public function rules()
	{
		return [
			'error_at' => 'required',
			'error_reason' => 'required',
		];
	}
}
