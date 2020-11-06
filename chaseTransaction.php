<?php

class chaseTransaction
{
    public $amount;
    public $date;
    public $merchant;
    public $account;


    public function __construct(string $message)
    {
      //Set Amount
      $amountPattern = "/\d{1,6}\.{1}\d{2}/";
      if (preg_match_all($amountPattern, $message, $matches)) {
         $this->amount = $matches[0][1];
      }

      //Set Date
      $datePattern = "/\w{3,4} \d{1,2}, \d{4}/";
      if (preg_match($datePattern, $message, $matches)) {
         $orgDate = $matches[0];
         $this->date = date("Y-m-d", strtotime($orgDate));
      }

      //Set Merchant
      $merchantPattern = "/(?<=at )(.*)(?= has been authorized on)/";
      if (preg_match($merchantPattern, $message, $matches)) {
         $this->merchant = ucwords(strtolower($matches[0]));
      }

      //Set Account
      $accountPattern = "/\d{4}\./";
      if (preg_match($accountPattern, $message, $matches)) {
         $this->account = substr($matches[0],0,(strlen($matches[0])-1));
      }

    }

}
