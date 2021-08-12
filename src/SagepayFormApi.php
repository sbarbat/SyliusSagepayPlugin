<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayRequest;
use Sbarbat\SyliusSagepayPlugin\Provider\AmountProvider;
use Sylius\Component\Addressing\Provider\ProvinceNamingProviderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class SagepayFormApi extends SagepayApi
{
    /**
     * @var ProvinceNamingProviderInterface
     */
    private $provinceNamingProvider;

    /**
     * @param mixed $request
     *
     * @return array
     */
    public function preparePayment(
        $request,
        ArrayObject $model,
        PaymentInterface $payment,
        ProvinceNamingProviderInterface $provinceNamingProvider,
        AmountProvider $amountProvider
    ): SagepayRequest {
        $this->provinceNamingProvider = $provinceNamingProvider;
        $afterUrl = $request->getToken()
            ->getAfterUrl()
        ;
        $order = $payment->getOrder();
        assert($order instanceof OrderInterface);

        $request = new SagepayRequest($this);

        $request->addQuery('VendorTxCode', $payment->getDetails()['txCode']);
        $request->addQuery('Amount', $amountProvider->getAmount($payment));
        $request->addQuery('Description', 'Payment for order #'.$order->getNumber());
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

    public function getStateCode(AddressInterface $address)
    {
        return $this->options['stateCodeAbbreviated'] ? $this->provinceNamingProvider->getAbbreviation(
            $address
        ) : $address->getProvinceCode();
    }
}
