<?php
declare(strict_types=1);
namespace Twikey\Api\Gateway;

use Psr\Http\Client\ClientInterface;
use Twikey\Api\Callback\TransactionCallback;
use Twikey\Api\TwikeyException;

class TransactionGateway extends BaseGateway
{
    /**
     * @param $data
     * @return array|mixed|object
     * @throws TwikeyException
     */
    public function create($data, $lang = 'en')
    {
        $response = $this->request('POST', "/creditor/transaction", ['form_params' => $data], $lang);
        $server_output = $this->checkResponse($response, "Creating a new transaction!");
        return json_decode($server_output);
    }

    /**
     * Note: This is rate limited
     * @throws TwikeyException
     */
    public function get($txid, $ref, $lang = 'en')
    {
        if (empty($ref)) {
            $item = "id=" . $txid;
        } else {
            $item = "ref=" . $ref;
        }

        $response = $this->request('GET', sprintf("/creditor/transaction/detail?%s", $item), [], $lang);
        $server_output = $this->checkResponse($response, "Retrieving payments!");
        return json_decode($server_output);
    }

    /**
     * Read until empty
     * @throws TwikeyException
     */
    public function feed(TransactionCallback $callback, $lang = 'en')
    {
        $count = 0;
        do {
            $response = $this->request('GET', "/creditor/transaction", [], $lang);
            $server_output = $this->checkResponse($response, "Retrieving transaction feed!");
            $transactions = json_decode($server_output);
            foreach ($transactions->Entries as $tx){
                $count++;
                $callback->handle($tx);
            }
        }
        while(count($transactions->Entries) > 0);
        return $count;
    }

    /**
     * @throws TwikeyException
     */
    public function sendPending(int $ct, $lang = 'en')
    {
        $response = $this->request('POST', "/creditor/collect", ['form_params' => ["ct" => $ct]], $lang);
        $server_output = $this->checkResponse($response, "Retrieving transaction feed!");
        return json_decode($server_output);
    }
}
