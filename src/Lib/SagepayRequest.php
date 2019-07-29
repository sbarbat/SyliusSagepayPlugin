<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

use Sbarbat\SyliusSagepayPlugin\SagepayFormApi;
use Sbarbat\SyliusSagepayPlugin\Action\AuthorizeAction;
use Sbarbat\SyliusSagepayPlugin\Action\CancelAction;
use Sbarbat\SyliusSagepayPlugin\Action\ConvertPaymentAction;
use Sbarbat\SyliusSagepayPlugin\Action\CaptureAction;
use Sbarbat\SyliusSagepayPlugin\Action\NotifyAction;
use Sbarbat\SyliusSagepayPlugin\Action\RefundAction;
use Sbarbat\SyliusSagepayPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

use Sylius\Component\Core\Model\AddressInterface;

class SagepayRequest
{
    protected $api;
    protected $query = [];
    protected $request = [];

    protected $queryMandatoryValues = [
        'VendorTxCode',
        'Amount',
        'Currency',
        'Description',
        'BillingSurname',
        'BillingFirstnames',
        'BillingAddress1',
        'BillingCity',
        'BillingPostCode',
        'BillingCountry',
        // 'BillingPhone',
        'DeliverySurname',
        'DeliveryFirstnames',
        'DeliveryAddress1',
        'DeliveryCity',
        'DeliveryPostCode',
        'DeliveryCountry',
        // 'DeliveryPhone',
        'SuccessURL',
        'FailureURL',
    ];

    protected $querySupportedValues = [
        'VendorTxCode',
        'Amount',
        'Currency',
        'Description',
        'SuccessURL',
        'FailureURL',
        'BillingSurname',
        'BillingFirstnames',
        'BillingAddress1',
        'BillingAddress2',
        'BillingCity',
        'BillingPostCode',
        'BillingCountry',
        'BillingState',
        'BillingPhone',
        'DeliverySurname',
        'DeliveryFirstnames',
        'DeliveryAddress1',
        'DeliveryAddress2',
        'DeliveryCity',
        'DeliveryPostCode',
        'DeliveryCountry',
        'DeliveryState',
        'DeliveryPhone',
        'CustomerName',
        'CustomerEMail',
        'Profile'
    ];

    /**
     * @param array               $options
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(SagepayFormApi $api)
    {
        $this->api = $api;

        $this->request['Vendor'] = $this->api->getOptions()['vendorName'];
        $this->request['VPSProtocol'] = $this->api->getOptions()['protocolVersion'];
        $this->request['TxType'] = SagepayTransactionType::PAYMENT;

        $this->addQuery('Currency', $this->api->getOption('currency'));
    }

    public function addQuery($key, $value): void
    {
        if (!in_array($key, $this->querySupportedValues)) {
            throw new SagepayApiException('Value ['.$key.'] not supported');
        }

        $this->query[$key] = $value;
    }

    public function setBillingAddress(AddressInterface $address): void
    {
        $this->setAddress('Billing', $address);
    }

    public function setShippingAddress(AddressInterface $address): void
    {
        $this->setAddress('Delivery', $address);
    }

    public function getRequest()
    {
        $this->validateQuery();
        $this->request['Crypt'] = SagepayUtil::encrypt($this->query, $this->api->getFormEncryptionPassword()); 

        return $this->request;
    }

    protected function validateQuery(): void
    {
        if ("US" === $this->query['BillingCountry']) {
            $this->queryMandatoryValues[] = 'BillingState';
        }

        foreach($this->queryMandatoryValues as $key) {
            if (!isset($this->query[$key]) || isset($this->query[$key]) && !$this->validateField($this->query[$key])) {
                throw new SagepayApiException($key . ' must be in the query');
            }
        }

        foreach($this->query as $key => $value) {
            if (null == $value) {
                unset($this->query[$key]);
            }
        }
    }

    protected function validateField($value): bool
    {
        return $value != null;
    }

    protected function setAddress($prefix, AddressInterface $address): void
    {
        $this->addQuery($prefix . 'Surname', $address->getLastName());
        $this->addQuery($prefix . 'Firstnames', $address->getFirstName());

        if (null == $address->getCompany()) {
            $this->addQuery($prefix . 'Address1', $address->getStreet());
        } else {
            $this->addQuery($prefix . 'Address1', $address->getCompany());
            $this->addQuery($prefix . 'Address2', $address->getStreet());
        }

        $this->addQuery($prefix . 'City', $address->getCity());
        $this->addQuery($prefix . 'State', $address->getProvinceCode());
        $this->addQuery($prefix . 'PostCode', $address->getPostcode());
        $this->addQuery($prefix . 'Country', $address->getCountryCode());
        $this->addQuery($prefix . 'Phone', $address->getPhoneNumber());
    }

}
