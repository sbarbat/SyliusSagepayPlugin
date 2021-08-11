<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;

class CancelAction implements ActionInterface
{
    /**
     * @param Cancel $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        throw new \LogicException('Not implemented');
    }

    public function supports($request)
    {
        return $request instanceof Cancel &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
