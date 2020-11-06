<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require_once "ynabAPI.php";
require_once "discoverTransaction.php";
require_once "amexTransaction.php";
require_once "chaseTransaction.php";

//CONFIGURE ME
$hostname = '{imap.gmail.com:993/debug/imap/ssl}Inbox'; //IMAP Hostname - Prepopulated for GMAIL
$username = 'Username'; //IMAP Username
$password = 'Password'; //IMAP Password
$ynabAccessToken = ""; //YNAB Developer Access Token - This can be obtained from Ynab --> Account Settings --> Developer Settings --> Personal Access Tokens

if (isset($_SERVER['REMOTE_ADDR'])) {
  //if ($_SERVER['REMOTE_ADDR'] != "") {
    die("Unauthorized");
  //}
}

///////////////////////////
//// 🅳🅾 🅽🅾🆃 🅴🅳🅸🆃 ///
/////////////////////////

// Initial connection to the inbox
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

// Grabs any e-mail that is not read
$emails = imap_search($inbox,'UNSEEN');

if($emails) {
  foreach($emails as $email_number) {

    $message = strip_tags(imap_fetchbody($inbox,$email_number,2));
    if ($message == "" || strpos( $message, "Discover" ) !== false) { // no attachments is the usual cause of this
      $message = imap_fetchbody($inbox, $email_number, 1);
    }
    //echo $message;

    //Mark Message Unread - For Testing
    //$unread = imap_clearflag_full($inbox, $email_number, "\\Seen");

    //Determine Provider or Discard
    if ( strpos($message, "Discover")  !== false ) {
      //Get a Discover Card Object from the message
      $transaction = new discoverTransaction($message);
    } elseif (strpos( $message, "American Express" )  !== false ) {
      $transaction = new amexTransaction($message);
    } elseif (strpos( $message, "Chase" )  !== false ) {
      $transaction = new chaseTransaction($message);
    }

    //Post to Ynab if Transaction is Found

    if (isset($transaction)) {
      echo "<br /><b>Amount: </b>".$transaction->amount."<br />";
      echo "<b>Date: </b>".$transaction->date."<br />";
      echo "<b>Merchant: </b>".$transaction->merchant."<br />";
      echo "<b>Account: </b>".$transaction->account."<br />";

      $ynab = new YnabApi($ynabAccessToken);

      //Search all accounts for matching account based on notes field and grab account id
      $budgetListData = $ynab->getBudgets();
      foreach ($budgetListData as $budgetList) {
        $budgetID = $budgetList['id']; //YNAB Budget ID
        $accountListData = $ynab->getAccounts($budgetID);
        foreach ($accountListData as $accountList) {
          if ($accountList['note'] == "$transaction->account") {
            echo "<b>Account Name: </b>".$accountList['name'];
            $ynab->addTransaction($budgetID, $accountList['id'], $transaction->date, intval($transaction->amount*-1000), $transaction->merchant);
          }
        }
      }


      echo "<br /><br />";

    } // end of transaction

  }// end foreach loop
} // end if($emails)
?>
