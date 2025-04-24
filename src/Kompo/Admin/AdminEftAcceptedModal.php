<?php

namespace Condoedge\Eft\Kompo\Admin;

use App\Kompo\Common\Modal;
use App\Models\Eft\EftFile;

class AdminEftAcceptedModal extends Modal
{
	protected $_Title = 'eft-eft-pick-acceptation';

	public $model = EftFile::class;

	public function handle()
	{
		$this->model->markAccepted(request('accepted_at'));
	}
	
	public function body()
	{
		return _Rows(
			_Date('eft-bank-transaction-date')->name('accepted_at'),
			_SubmitButton('eft-accepted'),
		);
	}

	public function rules()
	{
		return [
			'accepted_at' => 'required',
		];
	}
}
