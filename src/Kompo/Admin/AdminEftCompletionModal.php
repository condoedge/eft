<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftCompletionModal extends Modal
{
	protected $_Title = 'eft.eft-pick-completion';

	public $model = EftFile::class;
	
	public function body()
	{
		return _Rows(
			_Date('eft.bank-transaction-date')->name('completed_date'),
			_InputNumber('eft.completed-amount-confirmation')->name('completed_amount'),
			_FlexBetween(
				_Button('eft.completed-fully')->submit('markCompletedFully'),
				_Button('eft.completed-with-rejections')->outlined()->submit('markCompletedWithRejections'),
			)->class('space-x-4'),
		);
	}

	public function markCompletedFully()
	{
		$this->checkAmountIsMatchingCompletedAmount();

		$this->model->markCompletedFully(request('completed_date'), request('completed_amount'));
	}

	public function markCompletedWithRejections()
	{
		$this->checkAmountIsMatchingCompletedAmount();

		$this->model->markCompletedWithRejections(request('completed_date'), request('completed_amount'));
	}

	protected function checkAmountIsMatchingCompletedAmount()
	{
		if (abs($this->model->eftLines()->whereNull('caused_error')->sum('line_amount') - request('completed_amount')) >= 0.01) {
			abort(403, __('error.eft-completed-amount-is-different-than-the-sum'));
		}	
	}

	public function rules()
	{
		return [
			'completed_amount' => 'required',
			'completed_date' => 'required',
		];
	}
}
