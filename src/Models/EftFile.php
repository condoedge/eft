<?php

namespace Condoedge\Eft\Models;

use Kompo\Model as KompoModel;
use App\Models\Eft\EftLine as EftLine;

abstract class EftFile extends KompoModel
{
    protected $sequencNo = 0;
    protected $totalAmount = 0;
    protected $totalTransactions = 0;

    public const EFT_CREDIT = 'C';
    public const EFT_DEBIT = 'D';

    protected $casts = [
        'deposited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'completed_date' => 'datetime',
    ];

    /* RELATIONSHIPS */
    public function eftLines()
    {
        return $this->hasMany(EftLine::class);
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
    

    /* CALCULATED FIELDS */
    abstract public function getLinesToInclude();

    public function getCurrentEftLines()
    {
        return collect();
    }

    abstract protected function getCounterpartyNameFromLine($line);

    abstract protected function getCounterpartyIdFromLine($line);

    abstract protected function getBankFromLine($line);

    abstract protected function getAmountFromLine($line);

    abstract protected function getUniqIdForLine($line);

    public function getEftFileContent()
    {
        return $this->eftLines()->pluck('record')->implode("\n");
    }

    public function isCreditFile()
    {
        return $this->credit_or_debit == EftFile::EFT_CREDIT;
    }

    public function isDebitFile()
    {
        return $this->credit_or_debit == EftFile::EFT_DEBIT;
    }

    public function getFileName()
    {
        $prefix = ($this->return_institution == '006') ? 'tt_' : '';
        $prefix .= $this->test_file ? 'test_' : '';

        return $prefix.carbon($this->run_date)->format('Y_m_d').'-'.$this->file_creation_no.'.txt';
    }

    public function getMaxFileCreationNo()
    {
        return EftFile::orderByDesc('file_creation_no')->notTestFile()->value('file_creation_no');
    }
    

    /* ELEMENTS */


    /* ACTIONS */
    public function preDeleteActions()
    {
        //Override in app
    }

    public function notifyReceivers()
    {
        //Override in app        
    }

    public function releaseNeededLines()
    {
        //Override in app        
    }

    public function runActionsWhenDeposited()
    {
        //Override in app        
    }

    public function runActionsWhenAccepted()
    {
        //Override in app        
    }

    public function runActionsOnErrorLines($errorLines, $errorAt)
    {
        //Override in app        
    }

    public function runActionsWhenCompleted()
    {
        //Override in app        
    }

    public function deletable()
    {
        return auth()->user() && !$this->deposited_at && !$this->accepted_at && !$this->rejected_at && !$this->completed_at;
    }

    public function delete()
    {
        if (!$this->deletable()) {
            abort(403, __('eft-cannot-delete-a-deposited-file'));
        }
        
        $this->preDeleteActions();

        $this->eftLines()->delete();

        parent::delete();
    }

    public function markDeposited()
    {
        $this->deposited_at = now();
        $this->save();

        $this->runActionsWhenDeposited();
    }

    public function markAccepted($acceptedAt = null)
    {
        $this->notifyReceivers(); //We notify when EFT accepted

        $this->accepted_at = $acceptedAt ?: now();
        $this->save();

        $this->runActionsWhenAccepted();
    }

    public function markRejected()
    {
        $this->releaseNeededLines();

        $this->rejected_at = now();
        $this->save();
    }

    public function markCompleted($date)
    {
        $this->completed_date = $date;
        $this->completed_amount = $this->eftLines()->linePassing()->sum('line_amount');
        $this->completed_at = now();
        $this->save();

        $this->runActionsWhenCompleted();
    }

    public function checkAmountIsMatchingCompletedAmount($amount)
    {
        if (abs($this->eftLines()->linePassing()->sum('line_amount') - $amount) >= 0.01) {
            abort(403, __('eft-completed-amount-is-different-than-the-sum'));
        }   
    }

    public function createEftFileWithLines($runDate, $testFile, $fileCreationNo = null, $lines = null)
    {
        $this->run_date = $runDate;
        $this->test_file = $testFile;        
        $this->file_creation_no = $this->test_file ? '0000' : (
            $fileCreationNo ?: sprintf("%04d", $this->getMaxFileCreationNo() + 1)
        );

        $this->setEftConfig();

        $this->filename = $this->getFileName();
        $this->save();

        if ($lines && $this->eftLines()->count()) {
            $lines = $this->getCurrentEftLines()->concat($lines);
            $this->releaseNeededLines();
            $this->eftLines()->delete();
        }

        $this->createEftLinesInDb($lines);
    }

    protected function setEftConfig()
    {
        $this->credit_or_debit = $this->credit_or_debit ?: EftFile::EFT_CREDIT;

        $this->user_no = config('eft.user_no');
        $this->user_shortname = config('eft.user_shortname');
        $this->user_longname = config('eft.user_longname');

        $this->return_institution = config('eft.return_institution');
        $this->return_transit = config('eft.return_transit');
        $this->return_accountno = config('eft.return_accountno');

        $this->setBankCode();
    }

    protected function setBankCode()
    {
        $this->bank_code = $this->getBankCode();
    }

    protected function getBankCode()
    {
        return $this->return_institution.'10';
    }

    public function createEftLinesInDb($linesToInclude = null)
    {
        $linesToInclude = $linesToInclude ?: $this->getLinesToInclude();

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
            $this->user_no,
            $this->file_creation_no,
            $this->makeDateField($this->run_date),
            $this->bank_code,
            str_repeat(' ', 20),
            'CAD',
            str_repeat(' ', 1406),
        ];

        $this->createEftLine($lineArr);

        return $lineArr;
    }

    protected function createRecord($line)
    {
        $bank = $this->getBankFromLine($line);

        if (!$bank || !$bank->institution || !$bank->branch || !$bank->account_number) {
            return;
        }

        $amount = $this->test_file ? 1 : round($this->getAmountFromLine($line) * 100);

        if ($amount < 1) {
            return;
        }

        $this->totalAmount += $amount;
        $this->totalTransactions += 1;

        $uniqid = $this->getUniqIdForLine($line);

        $lineArr = [
            $this->isCreditFile() ? 'C' : 'D',
            $this->getSequenceNo(),
            $this->user_no.$this->file_creation_no,
            $this->isCreditFile() ? '430' : '450', //Misc. 
            $this->makeNumberField($amount, 10),
            $this->makeDateField($this->run_date),
            $this->getInstitutionBranch($bank->institution, $bank->branch), 
            $this->getAccountNo($bank->account_number),
            str_repeat('0', 22),
            str_repeat('0', 3),
            $this->sanitizeString($this->user_shortname, 15),
            $this->sanitizeString($this->getCounterpartyNameFromLine($line), 30),
            $this->sanitizeString($this->user_longname, 30),
            $this->user_no,
            sprintf("%019d", $uniqid),
            $this->getInstitutionBranch($this->return_institution, $this->return_transit),
            $this->getAccountNo($this->return_accountno),
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
            $this->user_no.$this->file_creation_no,
            $this->isCreditFile() ? str_repeat('0', 14) : $this->makeNumberField($this->totalAmount, 14),
            $this->isCreditFile() ? str_repeat('0', 8) : $this->makeNumberField($this->totalTransactions, 8),
            $this->isCreditFile() ? $this->makeNumberField($this->totalAmount, 14) : str_repeat('0', 14),
            $this->isCreditFile() ? $this->makeNumberField($this->totalTransactions, 8) : str_repeat('0', 8),
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

        $eftLine->setCounterparty($line);

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
        return '0'.carbon($date)->format('y').$this->makeNumberField(carbon($date)->format('z') + 1, 3); //date format 0YYDDD where DDD is the number of days passed this year
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
