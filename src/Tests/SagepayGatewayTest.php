<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Tests;

use Sbarbat\SyliusSagepayPlugin\SagepayGatewayFactory;

class SagepayFormTest extends \PHPUnit\Framework\TestCase 
{

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new SagepayGatewayFactory();

        $gateway = $factory->create(array('vendorName' => 'winebuyers'));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new SagepayGatewayFactory();
        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('payum.default_options', $config);

        $this->assertEquals(array(
            'sandbox' => true,
            'currency' => 'GBP',
            'vendorName' => '',
            'protocolVersion' => 3.00
        ), $config['payum.default_options']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new SagepayGatewayFactory();
        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('sagepay', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Sagepay', $config['payum.factory_title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The vendorName fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new SagepayGatewayFactory();
        $factory->create();
    }
}