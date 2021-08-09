<?php
declare(strict_types=1);
namespace Twikey\Api\Gateway;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Twikey\Api\Callback\InvoiceCallback;
use Twikey\Api\TwikeyException;

class InvoiceGateway extends BaseGateway
{
    /**
     * @throws TwikeyException
     * @throws ClientExceptionInterface
     */
    public function create($data, $lang = 'en')
    {
        $response = $this->request('POST', "/creditor/invoice", ['form_params' => $data], $lang);
        $server_output = $this->checkResponse($response, "Creating a new invoice!");
        return json_decode($server_output);
    }

    /**
     * Note this is rate limited
     * @throws TwikeyException
     * @throws ClientExceptionInterface
     */
    public function get($id, $lang = 'en')
    {
        $response = $this->request('GET', sprintf("/creditor/invoice/%s", $id), [], $lang);
        $server_output = $this->checkResponse($response, "Verifying a invoice ");
        return json_decode($server_output);
    }

    /**
     * Read until empty
     * @throws TwikeyException
     * @throws ClientExceptionInterface
     */
    public function feed(InvoiceCallback $callback, $lang = 'en')
    {
        $count = 0;
        do {
            $response = $this->request('GET', "/creditor/invoice", [], $lang);
            $server_output = $this->checkResponse($response, "Retrieving invoice feed!");
            $invoices = json_decode($server_output);
            foreach ($invoices as $invoice){
                $count++;
                $callback->handle($invoice);
            }
        }
        while(count($invoices) > 0);
        return $count;
    }
}
