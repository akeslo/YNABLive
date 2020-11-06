<?php

class amexTransaction
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

      //Set Merchant
      $merchantPattern = "/(?<=large purchase notifications online.)(.*)(?=\\\$)/";
      if (preg_match($merchantPattern, $message, $matches)) {
         $this->merchant = ucwords(strtolower($matches[0]));
      }

      //Set Account
      $accountPattern = "/(?<=PLATINUM CARDACCOUNT ENDING: )\d{5}/";
      if (preg_match($accountPattern, $message, $matches)) {
         $this->account = $matches[0];
      }

      //Set Date
      $datePattern = "/\w{3,4} \d{2}, \d{4}/";
      if (preg_match($datePattern, $message, $matches)) {
         $orgDate = $matches[0];
         $this->date = date("Y-m-d", strtotime($orgDate));
      }

    }

    private function cleanString(string $replace, string $text): string {
      return str_replace($replace,"",$text);
    }

}
