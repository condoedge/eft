<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftCompletionModal extends Modal
{
	protected $_Title = 'eft-eft-pick-completion';

	public $model = EftFile::class;
	
	public function body()
	{
		return _Rows(
			_Date('eft-bank-transaction-date')->name('completed_date'),
			_InputNumber('eft-completed-amount-confirmation')->name('completed_amount'),
			_FlexBetween(
				_Button('eft-completed-fully')->submit('markCompletedFully'),
				_Button('eft-completed-with-rejections')->outlined()->submit('markCompletedWithRejections')->inModal(),
			)->class('space-x-4'),
		);
	}

	public function markCompletedFully()
	{
		$this->model->checkAmountIsMatchingCompletedAmount(request('completed_amount'));

		$this->model->markCompletedFully(request('completed_date'), request('completed_amount'));
	}

	public function markCompletedWithRejections()
	{
		$this->model->markCompletedWithRejections(request('completed_date'), request('completed_amount'));

		return new AdminEftFileContentTable([
			'eft_file_id' => $this->model->id,
		]);
	}

	public function rules()
	{
		return [
			'completed_amount' => 'required',
			'completed_date' => 'required',
		];
	}
}
