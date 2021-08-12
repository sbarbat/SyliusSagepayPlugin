<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sbarbat\SyliusSagepayPlugin\Action\Api\FormApiAwareAction;
use Sbarbat\SyliusSagepayPlugin\Provider\AmountProvider;
use Sylius\Component\Addressing\Provider\ProvinceNamingProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class CaptureAction extends FormApiAwareAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @var ProvinceNamingProviderInterface
     */
    private $provinceNamingProvider;
    /**
     * @var AmountProvider
     */
    private $amountProvider;

    public function __construct(
        ProvinceNamingProviderInterface $provinceNamingProvider,
        AmountProvider $amountProvider
    ) {
        parent::__construct();
        $this->provinceNamingProvider = $provinceNamingProvider;
        $this->amountProvider = $amountProvider;
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $payment = $request->getFirstModel();

        throw new HttpPostRedirect($this->api->getOffsiteEndpoint(), $this->api->preparePayment(
            $request,
            $model,
            $payment,
            $this->provinceNamingProvider,
            $this->amountProvider
        )->getRequest());
    }

    public function supports($request)
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}
