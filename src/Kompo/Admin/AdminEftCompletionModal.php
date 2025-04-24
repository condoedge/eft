<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftCompletionModal extends Modal
{
	protected $_Title = 'eft-eft-pick-completion';

	public $model = EftFile::class;

	public function handle()
	{
		$this->model->markCompleted(request('completed_date'), request('completed_amount'));
	}
	
	public function body()
	{
		return _Rows(
			_Date('eft-completed_date')->name('completed_date'),
			//_InputNumber('eft-completed-amount-confirmation')->name('completed_amount'),
			_SubmitButton('eft-completed')->closeModal()->refresh('admin-eft-files-table'),
		);
	}

	public function rules()
	{
		return [
			//'completed_amount' => 'required',
			'completed_date' => 'required',
		];
	}
}
