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

class SagepayFormApi extends SagepayApi
{

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

}
