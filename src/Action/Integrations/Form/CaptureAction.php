<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Addressing\Provider\ProvinceNamingProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sbarbat\SyliusSagepayPlugin\Action\Api\FormApiAwareAction;

class CaptureAction extends FormApiAwareAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /** @var ProvinceNamingProviderInterface */
    private $provinceNamingProvider;

    public function __construct(ProvinceNamingProviderInterface $provinceNamingProvider)
    {
        parent::__construct();
        $this->provinceNamingProvider = $provinceNamingProvider;
    }

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
        
        throw new HttpPostRedirect(
            $this->api->getOffsiteEndpoint(),
            $this->api->preparePayment($request, $model, $payment, $this->provinceNamingProvider)->getRequest()
        );
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
