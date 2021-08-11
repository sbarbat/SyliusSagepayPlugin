<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form;

use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayResponse;
use Sbarbat\SyliusSagepayPlugin\Lib\SagepayStatusType;
use Sbarbat\SyliusSagepayPlugin\Action\Api\FormApiAwareAction;

class StatusAction extends FormApiAwareAction
{
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

        if(isset($_GET['crypt'])) {
            $sagepayResponse = (new SagepayResponse($this->api))->parseResponse();

            $this->resolvePaymentStatus($sagepayResponse, $request);
            $details = array_merge($details, $sagepayResponse->getArrayForDetails());
        } else {
            $request->markNew();
        }

        $payment->setDetails($details);
    }

    /**
     * @param string $authResult
     * @param GetStatusInterface $request
     */
    private function resolvePaymentStatus(SagepayResponse $response, GetStatusInterface $request): void
    {
        switch ($response->getStatus()) {
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
