<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sbarbat\SyliusSagepayPlugin\Action\Api\DirectApiAwareAction;
use Sylius\Component\Core\Model\PaymentInterface;

class CaptureAction extends DirectApiAwareAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $payment = $request->getFirstModel();

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);

        if ('POST' === $getHttpRequest->method) {
            if (isset($getHttpRequest->request['get_merchant_session']) && (
                'true' === $getHttpRequest->request['get_merchant_session']
                || true === $getHttpRequest->request['get_merchant_session']
            )) {
                throw new HttpResponse($this->api->getMerchantSessionKey());
            }

            $model['card-identifier'] = $getHttpRequest->request['card-identifier'];
            $model['merchant-session-key'] = $getHttpRequest->request['merchant-session-key'];

            return;
        }

        $template = $this->api->getOption('payum.sagepay.template.layout');
        $this->gateway->execute($renderTemplate = new RenderTemplate($template, [
            'model' => $model,
            'payment' => $payment,
            'sagepayJs' => $this->api->getApiEndpoint().'js/sagepay.js',
            'merchantSessionRoute' => $this->api->getOption('payum.sagepay.merchant_session_route_name'),
            'token' => $request->getToken(),
            'actionUrl' => $request->getToken() ? $request->getToken()
                ->getTargetUrl() : null,
        ]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    public function supports($request)
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}
