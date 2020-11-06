<?php
//Shoutout to https://github.com/ashleyhindle/monzo-to-ynab for the majority of the below code

class YnabApi
{
    private $baseUrl = "https://api.youneedabudget.com/v1/";
    private $accessToken;
    private $curl;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->curl = curl_init();
        curl_setopt_array($this->curl, [
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$this->accessToken}"],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);
    }

    public function getBudgets(): array
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "budgets");

        $response = curl_exec($this->curl);
        if (!$response || curl_getinfo($this->curl, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception('Failed to get budgets: ' . curl_error($this->curl));
        }

        $budgets = json_decode($response, true)['data']['budgets'];
        return $budgets;
    }

    public function getAccounts(string $budgetId): array
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "budgets/{$budgetId}/accounts");

        $response = curl_exec($this->curl);
        if (!$response || curl_getinfo($this->curl, CURLINFO_HTTP_CODE) !== 200) {
            //throw new \Exception('Failed to get accounts: ' . curl_error($this->curl));
            $accounts = [];
            return $accounts;
        }

        $accounts = json_decode($response, true)['data']['accounts'];
        return $accounts;
    }

    public function addTransaction(string $budgetId, string $accountId, string $date, float $amount, string $payee, string $notes='ADDED BY YNAB API')
    {
        $transaction = ['transaction' => [
            'account_id' => $accountId,
            'date' => $date,
            'amount' => $amount,
            'payee_name' => $payee,
            'cleared' => 'uncleared',
            'memo' => $notes,
        ]];

        curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "budgets/{$budgetId}/transactions");
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($transaction));
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->accessToken}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($this->curl);
        if (!$response || curl_getinfo($this->curl, CURLINFO_HTTP_CODE) !== 201) {
            throw new \Exception('Failed to add YNAB transaction: ' . curl_error($this->curl) . '-' . $response);
        }

        $transaction = json_decode($response, true)['data']['transaction'];

        return $transaction;
    }
}
