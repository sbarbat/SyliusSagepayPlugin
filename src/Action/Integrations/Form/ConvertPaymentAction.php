<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sbarbat\SyliusSagepayPlugin\Action\Api\FormApiAwareAction;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class ConvertPaymentAction extends FormApiAwareAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['txCode'] = $this->api->getTransactionCode($order, $payment);
        $details['amount'] = $payment->getAmount();
        $details['customerEmail'] = $order->getCustomer()->getEmail();
        $details['customerId'] = $order->getCustomer()->getId();
        $details['customerLocale'] = $order->getLocaleCode();
        $details['countryCode'] = null !== $order->getShippingAddress() ? $order->getShippingAddress()->getCountryCode() : null;
        $details['currencyCode'] = $order->getCurrencyCode();

        $request->setResult((array) $details);
    }

    public function supports($request)
    {
        return             $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' === $request->getTo()
        ;
    }
}
