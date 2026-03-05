<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\models\PaymentGatewayNew;
use App\models\Transactions;


class PaystackSettlementCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:paystack-settlement-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
   public function handle()
{
    try {

        // $secret = config('services.paystack.secret');

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ VERIFY PENDING TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        $pendingTxns = Transactions::where('trans_status', 0)->get();
        $paymentGateway = PaymentGatewayNew::where('pg_name', 'paystack')->first();
        $paystackKey = $paymentGateway->test_salt;

        foreach ($pendingTxns as $txn) {

            if (empty($txn->reference)) continue;

            $verifyUrl = "https://api.paystack.co/transaction/verify/" . $txn->reference;

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $verifyUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . $paystackKey,
                    "Cache-Control: no-cache",
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            if (!$response) continue;

            $result = json_decode($response, true);

            if (isset($result['data']['status']) && $result['data']['status'] == 'success') {

                $txn->trans_status = 1;
                $txn->paystack_response = json_encode($result['data']);
                $txn->save();

                \Log::info("Transaction Verified: " . $txn->reference);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ FETCH SETTLEMENT DATA
        |--------------------------------------------------------------------------
        */

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/settlement",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $paystackKey,
                "Cache-Control: no-cache",
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {
            \Log::error('Settlement API Failed');
            return;
        }

        $data = json_decode($response, true);

        if (!isset($data['status']) || $data['status'] != true) {
            \Log::error('Settlement API Invalid Response');
            return;
        }

        foreach ($data['data'] as $settlement) {

            DB::table('paystack_settlements')->updateOrInsert(
                ['settlement_id' => $settlement['id']],
                [
                    'amount' => $settlement['total_amount'],
                    'status' => $settlement['status'],
                    'settlement_date' => $settlement['settlement_date'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        \Log::info('Paystack Sync Cron Completed');

    } catch (\Exception $e) {

        \Log::error('Paystack Sync Cron Error: ' . $e->getMessage());
    }
}

}
