<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct;

use Payum\Core\ApiAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Sbarbat\SyliusSagepayPlugin\SagepayDirectApi;
use Sylius\Component\Core\Model\PaymentInterface;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayResponse;

use Payum\Core\Exception\RequestNotSupportedException;

use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayStatusType;
use Sbarbat\SyliusSagepayPlugin\Sanitizers\NameSanitizer;

use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Sbarbat\SyliusSagepayPlugin\Request\Api\ExecutePayment;
use Sbarbat\SyliusSagepayPlugin\Sanitizers\AddressSanitizer;
use Sbarbat\SyliusSagepayPlugin\Sanitizers\SanitizerInterface;
use Sbarbat\SyliusSagepayPlugin\Action\Api\DirectApiAwareAction;

final class ExecutePaymentAction extends DirectApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    const US_CODE = "US";

    /** @var SanitizerInterface */
    private $nameSanitizer;

    /** @var SanitizerInterface */
    private $addressSanitizer;

    public function __construct()
    {
        $this->apiClass = SagepayDirectApi::class;
        $this->nameSanitizer = new NameSanitizer();
        $this->addressSanitizer = new AddressSanitizer();
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
        if ($billingAddress->getCompany() != null) {
            $billingStreet = $billingAddress->getCompany() . ' ' . $billingAddress->getStreet();
        } else {
            $billingStreet = $billingAddress->getStreet();
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress->getCompany() != null) {
            $shippingStreet = $shippingAddress->getCompany() . ' ' . $shippingAddress->getStreet();
        } else {
            $shippingStreet = $shippingAddress->getStreet();
        }

        $description = 'Payment for order #' . $order->getNumber();

        $request = [
            "entryMethod" => "Ecommerce",
            "transactionType" => "Payment",
            "paymentMethod" => [
                "card" => [
                    "merchantSessionKey" => $model['merchant-session-key'],
                    "cardIdentifier" => $model['card-identifier']
                ]
            ],
            "vendorTxCode" => $model['vendorTxCode'],
            "amount" => $model['amount'],
            "currency" => $payment->getCurrencyCode(),
            "description" => $description,
            "apply3DSecure" => "UseMSPSetting",
            "customerFirstName" => $this->nameSanitizer->sanitize(((!empty($order->getCustomer()->getFirstname())) ? $order->getCustomer()->getFirstname() : $billingAddress->getFirstName())),
            "customerLastName" => $this->nameSanitizer->sanitize(((!empty($order->getCustomer()->getLastname())) ? $order->getCustomer()->getLastname() : $billingAddress->getLastName())),
            "billingAddress" => [
                "address1" => $this->addressSanitizer->sanitize($billingStreet),
                "city" => $billingAddress->getCity(),
                "postalCode" => $billingAddress->getPostcode(),
                "country" => $billingAddress->getCountryCode(),
            ],
            "shippingDetails" => [
                "recipientFirstName" => $this->nameSanitizer->sanitize($shippingAddress->getFirstName()),
                "recipientLastName" => $this->nameSanitizer->sanitize($shippingAddress->getLastName()),
                "shippingAddress1" => $this->addressSanitizer->sanitize($shippingStreet),
                "shippingCity" => $shippingAddress->getCity(),
                "shippingPostalCode" => $shippingAddress->getPostcode(),
                "shippingCountry" => $shippingAddress->getCountryCode(),
            ]
        ];
          
        if (static::US_CODE === $shippingAddress->getCountryCode()) {
            $request["shippingDetails"]["shippingState"] =  $shippingAddress->getProvinceCode();
        }
          
        if (static::US_CODE === $billingAddress->getCountryCode()) {
            $request["billingAddress"]["state"] =  $billingAddress->getProvinceCode();
        }
 
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api->getApiEndpoint()  . "transactions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($request),
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
