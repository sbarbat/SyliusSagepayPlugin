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
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\ExecutePaymentAction;

class StatusAction extends DirectApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
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
        $details = $payment->getDetails();

        if(($request->isNew() || $request->isUnknown()) && isset($model['payment_response'])) {
            $this->resolvePaymentStatus($request, $model['payment_response']);
        } else if(($request->isNew() || $request->isUnknown()) && isset($model['card-identifier'])) {
            $this->gateway->addAction(new ExecutePaymentAction());
            $executePaymentRequest = new ExecutePayment($payment);
            $executePaymentRequest->setModel($model);
            $this->gateway->execute($executePaymentRequest);
            $request->markNew();
        } else {
            $request->markNew();
        }

        if(null != $details) {
            $payment->setDetails($details);
        }
    }

    /**
     * @param string $authResult
     * @param GetStatusInterface $request
     */
    private function resolvePaymentStatus(GetStatusInterface $request, $response): void
    {
        $response = json_decode($response);

        if(!isset($response->status)) {
            $request->markCanceled();
            return;
        }

        switch (strtoupper($response->status)) {
            case null:
                $request->markNew();
                break;
            case SagepayStatusType::OK:
                $request->markCaptured();
                break;
            case SagepayStatusType::ABORT:
                $request->markCanceled();
                break;
            case SagepayStatusType::NOTAUTHED:
            case SagepayStatusType::REJECTED:
            case SagepayStatusType::ERROR:
            case SagepayStatusType::MALFORMED:
            case SagepayStatusType::INVALID:
                $request->markFailed();
                break;
            // case SagepayStatusType::CANCEL:
            //     $request->markSuspended();
            //     break;
            // case SagepayStatusType::REFUND:
            //     $request->markRefunded();
            //     break;
            default:
                $request->markUnknown();
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}

