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

class SagepayFormApi
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
    protected function doRequest($method, array $fields)
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
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://test.sagepay.com/gateway/service/vspform-register.vsp' : 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
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
    
    /**
     * @param  array $params
     *
     * @return array
     */
    public function preparePayment($request, ArrayObject $model, PaymentInterface $payment): SagepayRequest
    {
        $afterUrl = $request->getToken()->getAfterUrl();
        $order = $payment->getOrder();
        $customer = $order->getCustomer();

        $request = new SagepayRequest($this);

        $request->addQuery('VendorTxCode', $payment->getDetails()['txCode']);
        $request->addQuery('Amount', (string) $payment->getAmount() / 100);
        $request->addQuery('Description', 'Payment for order #'. $order->getNumber());

        $request->addQuery('SuccessURL', $afterUrl);
        $request->addQuery('FailureURL', $afterUrl);

        $request->setBillingAddress($order->getBillingAddress());
        $request->setShippingAddress($order->getShippingAddress());

        return $request;
    }

    public function getFormEncryptionPassword()
    {
        return $this->options['sandbox'] ? $this->options['encryptionPasswordTest'] : $this->options['encryptionPasswordLive'];
    }

    public function getTransactionCode(OrderInterface $order, PaymentInterface $payment)
    {
        return $order->getNumber() . '_' . $payment->getId() . '_' . time();
    }
}
