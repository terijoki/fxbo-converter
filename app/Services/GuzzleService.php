<?php

namespace App\Services;

use App\Contracts\SenderInterface;
use App\Exceptions\CurrencyConverterException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;

class GuzzleService implements SenderInterface
{
    public  ResponseInterface $response;
    public  string            $data;
    public  array             $arrayResponse;
    private Client            $guzzle;

    /**
     * GuzzleService constructor.
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->guzzle = new Client([
            'base_uri' => $baseUrl,
        ]);
    }

    /**
     * Set string data
     *
     * @return array
     */
    private function setData(): void
    {
        $this->data = $this->response
            ->getBody()
            ->getContents();
    }

    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Set data to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->arrayResponse;
    }

    private function toArrayResponse(): void
    {
        $array = json_decode($this->getData(), true);

        if ($array === null) {
            try {
                $array = (array) simplexml_load_string(
                    $this->getData(),
                    "SimpleXMLElement",
                    LIBXML_NOCDATA
                );
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                throw new CurrencyConverterException(__('exchanger.unrecognized_response'));
            }
        }

        $this->arrayResponse = $array;
    }

    /**
     * Function that calls `setData` after every method
     *
     * @param string $method
     * @param array $arguments
     * @return self
     */
    public function __call(string $method, array $arguments): self
    {
        try {
            call_user_func_array([$this,$method],$arguments);
        } catch (ClientException $exception) {
            throw new CurrencyConverterException($exception->getMessage());
        }

        $this->setData();

        return $this;
    }

    private function setRequestParams(
        string $method,
        ?array $data,
        ?array $headers
    ): ?array
    {
        $params = [];

        if ($data !== null) {
            $key = $method == 'GET'
                ? RequestOptions::QUERY
                : RequestOptions::JSON;
            $params[$key] = $data;
        }

        if ($headers !== null) {
            $params['headers'] = $headers;
        }

        return $params;
    }

    /**
     * Get response parameter
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name == 'json') {
            return $this->responseJson;
        }

        return $this->responseJson[$name] ?? null;
    }

    public function send(
        string $method,
        string $route,
        array $data = null,
        array $headers = null
    ): SenderInterface
    {
        $params = $this->setRequestParams(
            $method,
            $data,
            $headers
        );

        $this->response = $this->guzzle
            ->request(
                $method,
                $route,
                $params
            );
        $this->setData();
        $this->toArrayResponse();

        return $this;
    }
}
