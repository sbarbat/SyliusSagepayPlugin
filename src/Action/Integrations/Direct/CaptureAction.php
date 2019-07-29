<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

use Sylius\Component\Core\Model\PaymentInterface;

use Sbarbat\SyliusSagepayPlugin\SagepayFormApi;
use Sbarbat\SyliusSagepayPlugin\Action\Api\DirectApiAwareAction;
use Sbarbat\SyliusSagepayPlugin\Request\Api\ExecutePayment;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\ExecutePaymentAction;

class CaptureAction extends DirectApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $payment = $request->getFirstModel();
        
        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);

        if ($getHttpRequest->method == 'POST') {
            if (isset($getHttpRequest->request['get_merchant_session']) && true == $getHttpRequest->request['get_merchant_session']) {
                throw new HttpResponse($this->api->getMerchantSessionKey());
            }
            
            $model['card-identifier'] = $getHttpRequest->request['card-identifier'];
            $model['merchant-session-key'] = $getHttpRequest->request['merchant-session-key'];

            return;
        }
        
        $template = $this->api->getOption('payum.sagepay.template.layout');
        $this->gateway->execute($renderTemplate = new RenderTemplate($template, array(
            'model' => $model,
            'payment' => $payment,
            'sagepayJs' => $this->api->getApiEndpoint() . 'js/sagepay.js',
            'merchantSessionRoute' => $this->api->getOption('payum.sagepay.merchant_session_route_name'),
            'token' => $request->getToken(),
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        )));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}
