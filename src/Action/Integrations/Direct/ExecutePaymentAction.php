<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

use Sylius\Component\Core\Model\PaymentInterface;

use Sbarbat\SyliusSagepayPlugin\SagepayDirectApi;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayResponse;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayStatusType;

use Sbarbat\SyliusSagepayPlugin\Action\Api\DirectApiAwareAction;
use Sbarbat\SyliusSagepayPlugin\Request\Api\ExecutePayment;

final class ExecutePaymentAction extends DirectApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = SagepayDirectApi::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $payment = $request->getFirstModel();
        $order = $payment->getOrder();

        $billingAddress = $order->getBillingAddress();
        if($billingAddress->getCompany() != null) {
            $billingStreet = $billingAddress->getCompany() . ' ' . $billingAddress->getStreet();
        } else {
            $billingStreet = $billingAddress->getStreet();
        }

        $shippingAddress = $order->getShippingAddress();
        if($shippingAddress->getCompany() != null) {
            $shippingStreet = $shippingAddress->getCompany() . ' ' . $shippingAddress->getStreet();
        } else {
            $shippingStreet = $shippingAddress->getStreet();
        }

        $description = 'Payment for order #' . $order->getNumber();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api->getApiEndpoint()  . "transactions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '{' .
                                    '"transactionType": "Payment",' .
                                    '"paymentMethod": {' .
                                    '    "card": {' .
                                    '        "merchantSessionKey": "' . $model['merchant-session-key'] . '",' .
                                    '        "cardIdentifier": "' . $model['card-identifier'] . '"' .
                                    '    }' .
                                    '},' .
                                    '"vendorTxCode": "' . $model['vendorTxCode'] . '",' .
                                    '"amount": ' . $model['amount'] . ',' .
                                    '"currency": "' . $this->api->getOption('currency') . '",' .
                                    '"description": "' . $description . '",' .
                                    '"apply3DSecure": "UseMSPSetting",' .
                                    '"customerFirstName": "' . $order->getCustomer()->getFirstname() . '",' .
                                    '"customerLastName": "' . $order->getCustomer()->getLastname() . '",' .
                                    '"billingAddress": {' .
                                    '    "address1": "' . $billingStreet .' ' . '",' .
                                    '    "city": "' . $billingAddress->getCity() . '",' .
                                    '    "postalCode": "' . $billingAddress->getPostcode() . '",' .
                                    '    "country": "' . $billingAddress->getCountryCode() . '"' .
                                    '},' .
                                    '"shippingDetails": {' .
                                    '    "recipientFirstName": "' . $shippingAddress->getFirstName() . '",' .
                                    '    "recipientLastName": "' . $shippingAddress->getLastName() . '",' .
                                    '    "shippingAddress1": "' . $shippingStreet . '",'.
                                    '    "shippingCity": "' . $shippingAddress->getCity() . '",'.
                                    '    "shippingPostalCode": "' . $shippingAddress->getPostcode() . '",'.
                                    '    "shippingCountry": "' . $shippingAddress->getCountryCode() . '"'.
                                    '},' .
                                    '"entryMethod": "Ecommerce"' .
                                '}',
            CURLOPT_HTTPHEADER => array(
                "Authorization: " . $this->api->getBasicAuthenticationHeader(),
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ),
        ));
 
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);

        $model['payment_response'] = $response;
        $model['payment_error'] = $err;
  
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ExecutePayment &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
