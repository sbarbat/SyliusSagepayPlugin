<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayRequest;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

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
        assert($order instanceof OrderInterface);

        $request = new SagepayRequest($this);

        $request->addQuery('VendorTxCode', $payment->getDetails()['txCode']);
        $request->addQuery('Amount', (string) $payment->getAmount() / 100);
        $request->addQuery('Description', 'Payment for order #'. $order->getNumber());
        $request->addQuery('Currency', $payment->getCurrencyCode());

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
