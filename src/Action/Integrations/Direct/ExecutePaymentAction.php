<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sbarbat\SyliusSagepayPlugin\Action\Api\DirectApiAwareAction;
use Sbarbat\SyliusSagepayPlugin\Request\Api\ExecutePayment;
use Sbarbat\SyliusSagepayPlugin\Sanitizers\AddressSanitizer;
use Sbarbat\SyliusSagepayPlugin\Sanitizers\NameSanitizer;
use Sbarbat\SyliusSagepayPlugin\Sanitizers\SanitizerInterface;

final class ExecutePaymentAction extends DirectApiAwareAction
{
    public const US_CODE = 'US';

    /**
     * @var SanitizerInterface
     */
    private $nameSanitizer;

    /**
     * @var SanitizerInterface
     */
    private $addressSanitizer;

    public function __construct()
    {
        parent::__construct();
        $this->nameSanitizer = new NameSanitizer();
        $this->addressSanitizer = new AddressSanitizer();
    }

    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $payment = $request->getFirstModel();
        $order = $payment->getOrder();

        $billingAddress = $order->getBillingAddress();
        if (null !== $billingAddress->getCompany()) {
            $billingStreet = $billingAddress->getCompany().' '.$billingAddress->getStreet();
        } else {
            $billingStreet = $billingAddress->getStreet();
        }

        $shippingAddress = $order->getShippingAddress();
        if (null !== $shippingAddress->getCompany()) {
            $shippingStreet = $shippingAddress->getCompany().' '.$shippingAddress->getStreet();
        } else {
            $shippingStreet = $shippingAddress->getStreet();
        }

        $description = 'Payment for order #'.$order->getNumber();

        $request = [
            'entryMethod' => 'Ecommerce',
            'transactionType' => 'Payment',
            'paymentMethod' => [
                'card' => [
                    'merchantSessionKey' => $model['merchant-session-key'],
                    'cardIdentifier' => $model['card-identifier'],
                ],
            ],
            'vendorTxCode' => $model['vendorTxCode'],
            'amount' => $model['amount'],
            'currency' => $payment->getCurrencyCode(),
            'description' => $description,
            'apply3DSecure' => 'UseMSPSetting',
            'customerFirstName' => $this->nameSanitizer->sanitize($billingAddress->getFirstName()),
            'customerLastName' => $this->nameSanitizer->sanitize($billingAddress->getLastName()),
            'billingAddress' => [
                'address1' => $this->addressSanitizer->sanitize($billingStreet),
                'city' => $billingAddress->getCity(),
                'postalCode' => $billingAddress->getPostcode(),
                'country' => $billingAddress->getCountryCode(),
            ],
            'shippingDetails' => [
                'recipientFirstName' => $this->nameSanitizer->sanitize($shippingAddress->getFirstName()),
                'recipientLastName' => $this->nameSanitizer->sanitize($shippingAddress->getLastName()),
                'shippingAddress1' => $this->addressSanitizer->sanitize($shippingStreet),
                'shippingCity' => $shippingAddress->getCity(),
                'shippingPostalCode' => $shippingAddress->getPostcode(),
                'shippingCountry' => $shippingAddress->getCountryCode(),
            ],
        ];

        if (static::US_CODE === $shippingAddress->getCountryCode()) {
            $request['shippingDetails']['shippingState'] = $this->api->getOption(
                'stateCodeAbbreviated'
            ) ? $shippingAddress->getAbbreviation() : $shippingAddress->getProvinceCode();
        }

        if (static::US_CODE === $billingAddress->getCountryCode()) {
            $request['billingAddress']['state'] = $this->api->getOption(
                'stateCodeAbbreviated'
            ) ? $billingAddress->getAbbreviation() : $billingAddress->getProvinceCode();
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->api->getApiEndpoint().'transactions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($request),
            CURLOPT_HTTPHEADER => [
                'Authorization: '.$this->api->getBasicAuthenticationHeader(),
                'Cache-Control: no-cache',
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $model['payment_response'] = $response;
        $model['payment_error'] = $err;

        return $model;
    }

    public function supports($request)
    {
        return $request instanceof ExecutePayment &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
