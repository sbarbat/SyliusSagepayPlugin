<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Controller;

use Payum\Core\Exception\LogicException;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;

class MerchantSessionController
{
    use GatewayAwareTrait;

    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @param Payum $payum
     * @param PaymentRepositoryInterface $paymentRepository
     */
    public function __construct(Payum $payum, PaymentRepositoryInterface $paymentRepository)
    {
        $this->payum = $payum;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function getKey(Request $request): Response
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        if (null === $paymentId = $request->request->get('id', null)) {
            throw new LogicException("A parameter id not be found.");
        }

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->findOneBy(['id' => $paymentId]);

        if (null === $payment) {
            throw new NotFoundHttpException("Payment not found ");
        }

        $gateway = $this->payum->getGateway($payment->getGatewayName());

        return new JsonResponse($token);
        // $gateway->execute(new Notify($token));

        // return new Response("[accepted]");
    }
}