<?php

class discoverTransaction
{
    public $amount;
    public $date;
    public $merchant;
    public $account;


    public function __construct(string $message)
    {
      //Set Amount
      $amountPattern = "/\d{1,6}\.{1}\d{2}/";
      if (preg_match($amountPattern, $message, $matches)) {
         $this->amount = $matches[0];
      }

      //Set Date
      $datePattern = "/\w{3,8} \d{2}, \d{4}/";
      if (preg_match($datePattern, $message, $matches)) {
         $orgDate = $matches[0];
         $this->date = date("Y-m-d", strtotime($orgDate));
      }

      //Set Merchant
      $merchantPattern = "/(?i)(?<=merchant:\s)[a-z ]*/";
      if (preg_match($merchantPattern, $message, $matches)) {
         $this->merchant = ucwords(strtolower($matches[0]));
      }

      //Set Account
      $accountPattern = "/(?i)(?<=number ending with )[0-9]*/";
      if (preg_match($accountPattern, $message, $matches)) {
         $this->account = $matches[0];
      }

    }

}
