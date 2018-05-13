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

class SagepayResponse
{
    protected $api;

    protected $status;
    protected $statusDetail;
    protected $vendorTxCode;
    protected $amount;
    protected $txAuthNo;
    protected $declineCode;

    /**
     * @param array               $options
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(SagepayFormApi $api)
    {
        $this->api = $api;
    }

    public function parseResponse()
    {
        if(!isset($_GET['crypt'])) {
            throw new SagepayApiException('No crypt return');
        }
        $array = SagepayUtil::decrypt($_GET['crypt'], $this->api->getFormEncryptionPassword());

        $this->setStatus($array['Status'])
             ->setStatusDetail($array['StatusDetail'])
             ->setVendorTxCode($array['VendorTxCode'])
             ->setAmount($array['Amount'])
             ->setTxAuthNo($array['TxAuthNo'])
             ->setDeclineCode($array['DeclineCode']);

        return $this;
    }

    public function isComplete()
    {
        if($this->status == SagepayTransactionType::COMPLETE)
            return true;
        return false;
    }
    
    /**
     * Get the value of declineCode
     */ 
    public function getDeclineCode()
    {
        return $this->declineCode;
    }

    /**
     * Set the value of declineCode
     *
     * @return  self
     */ 
    public function setDeclineCode($declineCode)
    {
        $this->declineCode = $declineCode;

        return $this;
    }

    /**
     * Get the value of txAuthNo
     */ 
    public function getTxAuthNo()
    {
        return $this->txAuthNo;
    }

    /**
     * Set the value of txAuthNo
     *
     * @return  self
     */ 
    public function setTxAuthNo($txAuthNo)
    {
        $this->txAuthNo = $txAuthNo;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of statusDetail
     */ 
    public function getStatusDetail()
    {
        return $this->statusDetail;
    }

    /**
     * Set the value of statusDetail
     *
     * @return  self
     */ 
    public function setStatusDetail($statusDetail)
    {
        $this->statusDetail = $statusDetail;

        return $this;
    }

    /**
     * Get the value of vendorTxCode
     */ 
    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    /**
     * Set the value of vendorTxCode
     *
     * @return  self
     */ 
    public function setVendorTxCode($vendorTxCode)
    {
        $this->vendorTxCode = $vendorTxCode;

        return $this;
    }

    /**
     * Get the value of amount
     */ 
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @return  self
     */ 
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
