<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayUtil;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayRequest;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\OrderInterface;

abstract class SagepayApi
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, $path, array $fields = [])
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://pi-test.sagepay.com/api/v1/' : 'https://pi-live.sagepay.com/api/v1/';
    }

    /**
     * @return string
     */
    public function getOffsiteEndpoint()
    {
        return $this->options['sandbox'] ? 'https://test.sagepay.com/gateway/service/vspform-register.vsp' : 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
    }

    public function getOption(string $option)
    {
        return $this->options[$option];
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getTransactionCode(OrderInterface $order, PaymentInterface $payment)
    {
        return $order->getNumber() . '_' . $payment->getId() . '_' . time();
    }
}
