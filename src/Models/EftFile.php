<?php

namespace Condoedge\Eft\Models;

use Kompo\Model as KompoModel;
use App\Models\Eft\EftLine as EftLine;

class EftFile extends Model
{
    protected $sequencNo = 0;
    protected $totalAmount = 0;
    protected $totalTransactions = 0;

    /* RELATIONSHIPS */
    public function eftLines()
    {
        return $this->hasMany(EftLine::class);
    }
    
    /* ATTRIBUTES */
    

    /* CALCULATED FIELDS */
    public function getEftFileContent()
    {
        return $this->eftLines()->pluck('record')->implode("\n");
    }

    

    /* SCOPES */
    public function scopeNotTestFile($query)
    {
        $query->where(fn($q) => $q->whereNull('test_file')->orWhere('test_file', 0));
    }

    public function scopeIsTestFile($query)
    {
        $query->where('test_file', 1);
    }
    

    /* ELEMENTS */


    /* ACTIONS */
    public function delete()
    {
        if ($this->deposited_at) {
            abort(403, __('Cannot delete a deposited file'));
        }
        
        $this->preDeleteActions();

        $this->eftLines()->delete();

        parent::delete();
    }

    public function preDeleteActions()
    {
        //Override in app
    }

    public function markDeposited()
    {
        $this->notifyReceivers();

        $this->deposited_at = now();
        $this->save();
    }

    public function notifyReceivers()
    {
        //Override in app        
    }

    public static function createEftFile($date, $testFile)
    {
        $file = new EftFile();
        $file->run_date = $date;
        $file->test_file = $testFile;
        $file->filename = $file->getFileName();
        $file->file_creation_no = $testFile ? '0000' : sprintf("%04d", $file->getMaxFileCreationNo() + 1);
        $file->save();

        $file->createEftLinesInDb();

    }

    public function getLinesToInclude()
    {
        return collect(); //Override in app  
    }

    public function getFileName()
    {
        $prefix = $this->test_file ? 'tt' : 'tf';

        return $prefix.'03800'.substr(config('eft.user_no'), 0, 5).'.txt';
    }

    public function getMaxFileCreationNo()
    {
        $query = EftFile::orderByDesc('file_creation_no');

        if ($this->isTestFile()) {
            $query = $query->isTestFile();
        } else {
            $query = $query->notTestFile();
        }

        return $query->value('file_creation_no');
    }

    public function createEftLinesInDb()
    {
        $linesToInclude = $this->getLinesToInclude();

        return collect([$this->createHeader()])
            ->concat(
                $linesToInclude
                    ->map(fn($line) => $this->createRecord($line))
                    ->filter()
            )
            ->push($this->createFooter())
            ->map(fn($row) => implode('', $row));
    }

    protected function createHeader()
    {
        $lineArr = [
            'A',
            $this->getSequenceNo(),
            config('eft.user_no'),
            $this->file_creation_no,
            $this->makeDateField($this->run_date),
            config('eft.bank_code'),
            str_repeat(' ', 20),
            'CAD',
            str_repeat(' ', 1406),
        ];

        $this->createEftLine($lineArr);

        return $lineArr;
    }

    protected function getCounterpartyFromLine($line)
    {
        return; //Override in app
    }

    protected function getCounterpartyNameFromLine($line)
    {
        return; //Override in app
    }

    protected function getCounterpartyIdFromLine($line)
    {
        return; //Override in app
    }

    protected function getBankFromLine($line)
    {
        return; //Override in app
    }

    protected function getAmountFromLine($line)
    {
        return; //Override in app
    }

    protected function createRecord($line)
    {
        $bank = $this->getBankFromLine($line);
        $lineAmount = $this->getAmountFromLine($line);

        if (!$bank || !$bank->institution || !$bank->branch || !$bank->account_number) {
            return;
        }

        $amount = $this->test_file ? 1 : round($lineAmount * 100);

        $this->totalAmount += $amount;
        $this->totalTransactions += 1;

        $uniqid = uniqid();

        $lineArr = [
            'C',
            $this->getSequenceNo(),
            config('eft.user_no').$this->file_creation_no,
            '430', //Misc. Payments 
            $this->makeNumberField($amount, 10),
            $this->makeDateField($this->run_date),
            $this->getInstitutionBranch($bank->institution, $bank->branch), 
            $this->getAccountNo($bank->account_number),
            str_repeat('0', 22),
            str_repeat('0', 3),
            $this->sanitizeString(config('eft.user_shortname'), 15),
            $this->sanitizeString($this->getCounterpartyNameFromLine($line), 30),
            $this->sanitizeString(config('eft.user_longname'), 30),
            config('eft.user_no'),
            sprintf("%019d", $uniqid),
            $this->getInstitutionBranch(config('eft.credit_institution'), config('eft.credit_transit')),
            $this->getAccountNo(config('eft.credit_accountno')),
            str_repeat(' ', 15), //GENERAL INFORMATION
            str_repeat(' ', 22),
            str_repeat(' ', 2),
            str_repeat('0', 11),
            str_repeat(' ', 1200),
        ];

        $this->createEftLine($lineArr, $line, $uniqid);

        return $lineArr;
    }

    protected function createFooter()
    {
        $lineArr = [
            'Z',
            $this->getSequenceNo(),
            config('eft.user_no').$this->file_creation_no,
            str_repeat('0', 14),
            str_repeat('0', 8),
            $this->makeNumberField($this->totalAmount, 14),
            $this->makeNumberField($this->totalTransactions, 8),
            str_repeat('0', 14),
            str_repeat('0', 8),
            str_repeat('0', 14),
            str_repeat('0', 8),
            str_repeat(' ', 1352),
        ];

        $this->createEftLine($lineArr);

        return $lineArr;

    }


    public function createEftLine($lineArr, $line = null, $uniqid = null)
    {
        $eftLine = new EftLine();
        $eftLine->record = implode('', $lineArr);
        $eftLine->line_date = $this->run_date;
        $eftLine->line_slug = $uniqid;        
        $eftLine->line_amount = $line ? $this->getAmountFromLine($line) : null;
        $eftLine->team_id = $line ? $this->getCounterpartyIdFromLine($line) : null;

        $this->eftLines()->save($eftLine);

        $eftLine->postCreateActions($line);
    }

    /* SPECIFIC UTILITIES */
    protected function getSequenceNo()
    {
        $this->sequencNo += 1;

        return sprintf("%09d", $this->sequencNo);;
    }

    protected function makeNumberField($number, $characters)
    {
        return sprintf("%0".$characters."d", round($number));
    }

    protected function makeDateField($date)
    {
        return carbon($date)->format('0yz'); //date format 0YYDDD where DDD is the number of days passed this year
    }

    protected function sanitizeString($text, $characters)
    {
        $text = preg_replace('/[^ \w-]/', '', replaceAccents($text));

        return strlen($text) > $characters ? substr($text, 0, $characters) : str_pad($text, $characters);
    }

    protected function getInstitutionBranch($institution, $branch)
    {
        return '0'.substr($institution, 0, 3).substr($branch, 0, 5);
    }

    protected function getAccountNo($accountNo)
    {
        return str_pad($accountNo, 12, " ");
    }
}
