<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

use Sbarbat\SyliusSagepayPlugin\SagepayFormApi;

class SagepayResponse
{
    protected $api;

    protected $status;

    protected $statusDetail;

    protected $vendorTxCode;

    protected $amount;

    protected $txAuthNo;

    protected $declineCode;

    protected $AVSCV2;

    protected $vpsTxId;

    protected $addressResult;

    protected $postCodeResult;

    protected $cv2Result;

    protected $giftAid;

    protected $_3dSecureStatus;

    protected $cardType;

    protected $last4Digits;

    /**
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(SagepayFormApi $api)
    {
        $this->api = $api;
    }

    public function parseResponse()
    {
        if (! isset($_GET['crypt'])) {
            throw new SagepayApiException('No crypt return');
        }
        $array = SagepayUtil::decrypt($_GET['crypt'], $this->api->getFormEncryptionPassword());

        $this
            ->setVendorTxCode($array['VendorTxCode'])
            ->setVpsTxId(isset($array['VPSTxId']) ? $array['VPSTxId'] : null)
            ->setStatus($array['Status'])
            ->setStatusDetail($array['StatusDetail'])
            ->setAVSCV2(isset($array['AVSCV2']) ? $array['AVSCV2'] : null)
            ->setAddressResult(isset($array['AddressResult']) ? $array['AddressResult'] : null)
            ->setPostCodeResult(isset($array['PostCodeResult']) ? $array['PostCodeResult'] : null)
            ->setCv2Result(isset($array['CV2Result']) ? $array['CV2Result'] : null)
            ->setGiftAid(isset($array['GiftAid']) ? $array['GiftAid'] : null)
            ->set_3dSecureStatus(isset($array['3DSecureStatus']) ? $array['3DSecureStatus'] : null)
            ->setCardType(isset($array['CardType']) ? $array['CardType'] : null)
            ->setLast4Digits(isset($array['Last4Digits']) ? $array['Last4Digits'] : null)
            ->setLast4Digits(isset($array['Last4Digits']) ? $array['Last4Digits'] : null)
            ->setAmount($array['Amount'])
            ->setTxAuthNo(isset($array['TxAuthNo']) ? $array['TxAuthNo'] : null)
            ->setDeclineCode(isset($array['DeclineCode']) ? $array['DeclineCode'] : null)
        ;

        return $this;
    }

    public function getArrayForDetails()
    {
        $all = get_object_vars($this);
        unset($all['api']);

        return $all;
    }

    /**
     * Get the value of declineCode.
     */
    public function getDeclineCode()
    {
        return $this->declineCode;
    }

    /**
     * Set the value of declineCode.
     *
     * @param mixed $declineCode
     *
     * @return self
     */
    public function setDeclineCode($declineCode)
    {
        $this->declineCode = $declineCode;

        return $this;
    }

    /**
     * Get the value of txAuthNo.
     */
    public function getTxAuthNo()
    {
        return $this->txAuthNo;
    }

    /**
     * Set the value of txAuthNo.
     *
     * @param mixed $txAuthNo
     *
     * @return self
     */
    public function setTxAuthNo($txAuthNo)
    {
        $this->txAuthNo = $txAuthNo;

        return $this;
    }

    /**
     * Get the value of status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status.
     *
     * @param mixed $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of statusDetail.
     */
    public function getStatusDetail()
    {
        return $this->statusDetail;
    }

    /**
     * Set the value of statusDetail.
     *
     * @param mixed $statusDetail
     *
     * @return self
     */
    public function setStatusDetail($statusDetail)
    {
        $this->statusDetail = $statusDetail;

        return $this;
    }

    /**
     * Get the value of vendorTxCode.
     */
    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    /**
     * Set the value of vendorTxCode.
     *
     * @param mixed $vendorTxCode
     *
     * @return self
     */
    public function setVendorTxCode($vendorTxCode)
    {
        $this->vendorTxCode = $vendorTxCode;

        return $this;
    }

    /**
     * Get the value of amount.
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount.
     *
     * @param mixed $amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of vpsTxId.
     */
    public function getVpsTxId()
    {
        return $this->vpsTxId;
    }

    /**
     * Set the value of vpsTxId.
     *
     * @param mixed $vpsTxId
     *
     * @return self
     */
    public function setVpsTxId($vpsTxId)
    {
        $this->vpsTxId = $vpsTxId;

        return $this;
    }

    /**
     * Get the value of addressResult.
     */
    public function getAddressResult()
    {
        return $this->addressResult;
    }

    /**
     * Set the value of addressResult.
     *
     * @param mixed $addressResult
     *
     * @return self
     */
    public function setAddressResult($addressResult)
    {
        $this->addressResult = $addressResult;

        return $this;
    }

    /**
     * Get the value of postCodeResult.
     */
    public function getPostCodeResult()
    {
        return $this->postCodeResult;
    }

    /**
     * Set the value of postCodeResult.
     *
     * @param mixed $postCodeResult
     *
     * @return self
     */
    public function setPostCodeResult($postCodeResult)
    {
        $this->postCodeResult = $postCodeResult;

        return $this;
    }

    /**
     * Get the value of cv2Result.
     */
    public function getCv2Result()
    {
        return $this->cv2Result;
    }

    /**
     * Set the value of cv2Result.
     *
     * @param mixed $cv2Result
     *
     * @return self
     */
    public function setCv2Result($cv2Result)
    {
        $this->cv2Result = $cv2Result;

        return $this;
    }

    /**
     * Get the value of giftAid.
     */
    public function getGiftAid()
    {
        return $this->giftAid;
    }

    /**
     * Set the value of giftAid.
     *
     * @param mixed $giftAid
     *
     * @return self
     */
    public function setGiftAid($giftAid)
    {
        $this->giftAid = $giftAid;

        return $this;
    }

    /**
     * Get the value of _3dSecureStatus.
     */
    public function get_3dSecureStatus()
    {
        return $this->_3dSecureStatus;
    }

    /**
     * Set the value of _3dSecureStatus.
     *
     * @param mixed $_3dSecureStatus
     *
     * @return self
     */
    public function set_3dSecureStatus($_3dSecureStatus)
    {
        $this->_3dSecureStatus = $_3dSecureStatus;

        return $this;
    }

    /**
     * Get the value of cardType.
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * Set the value of cardType.
     *
     * @param mixed $cardType
     *
     * @return self
     */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;

        return $this;
    }

    /**
     * Get the value of last4Digits.
     */
    public function getLast4Digits()
    {
        return $this->last4Digits;
    }

    /**
     * Set the value of last4Digits.
     *
     * @param mixed $last4Digits
     *
     * @return self
     */
    public function setLast4Digits($last4Digits)
    {
        $this->last4Digits = $last4Digits;

        return $this;
    }

    /**
     * Get the value of AVSCV2.
     */
    public function getAVSCV2()
    {
        return $this->AVSCV2;
    }

    /**
     * Set the value of AVSCV2.
     *
     * @param mixed $AVSCV2
     *
     * @return self
     */
    public function setAVSCV2($AVSCV2)
    {
        $this->AVSCV2 = $AVSCV2;

        return $this;
    }
}
