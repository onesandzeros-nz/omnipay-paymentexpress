<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\PaymentExpress;

use Omnipay\GatewayTestCase;

class PxPayGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new PxPayGateway($this->httpClient, $this->httpRequest);

        $this->options = array(
            'amount' => 1000,
            'returnUrl' => 'https://www.example.com/return',
        );
    }

    public function testAuthorizeSuccess()
    {
        $this->setMockResponse($this->httpClient, 'PxPayPurchaseSuccess.txt');

        $response = $this->gateway->authorize($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNull($response->getGatewayReference());
        $this->assertNull($response->getMessage());
        $this->assertSame('https://sec.paymentexpress.com/pxpay/pxpay.aspx?userid=Developer&request=v5H7JrBTzH-4Whs__1iQnz4RGSb9qxRKNR4kIuDP8kIkQzIDiIob9GTIjw_9q_AdRiR47ViWGVx40uRMu52yz2mijT39YtGeO7cZWrL5rfnx0Mc4DltIHRnIUxy1EO1srkNpxaU8fT8_1xMMRmLa-8Fd9bT8Oq0BaWMxMquYa1hDNwvoGs1SJQOAJvyyKACvvwsbMCC2qJVyN0rlvwUoMtx6gGhvmk7ucEsPc_Cyr5kNl3qURnrLKxINnS0trdpU4kXPKOlmT6VacjzT1zuj_DnrsWAPFSFq-hGsow6GpKKciQ0V0aFbAqECN8rl_c-aZWFFy0gkfjnUM4qp6foS0KMopJlPzGAgMjV6qZ0WfleOT64c3E-FRLMP5V_-mILs8a', $response->getRedirectUrl());
        $this->assertSame('GET', $response->getRedirectMethod());
    }

    public function testAuthorizeFailure()
    {
        $this->setMockResponse($this->httpClient, 'PxPayPurchaseFailure.txt');

        $response = $this->gateway->authorize($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getGatewayReference());
        $this->assertSame('Invalid Key', $response->getMessage());
    }

    public function testPurchaseSuccess()
    {
        $this->setMockResponse($this->httpClient, 'PxPayPurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNull($response->getGatewayReference());
        $this->assertNull($response->getMessage());
        $this->assertSame('https://sec.paymentexpress.com/pxpay/pxpay.aspx?userid=Developer&request=v5H7JrBTzH-4Whs__1iQnz4RGSb9qxRKNR4kIuDP8kIkQzIDiIob9GTIjw_9q_AdRiR47ViWGVx40uRMu52yz2mijT39YtGeO7cZWrL5rfnx0Mc4DltIHRnIUxy1EO1srkNpxaU8fT8_1xMMRmLa-8Fd9bT8Oq0BaWMxMquYa1hDNwvoGs1SJQOAJvyyKACvvwsbMCC2qJVyN0rlvwUoMtx6gGhvmk7ucEsPc_Cyr5kNl3qURnrLKxINnS0trdpU4kXPKOlmT6VacjzT1zuj_DnrsWAPFSFq-hGsow6GpKKciQ0V0aFbAqECN8rl_c-aZWFFy0gkfjnUM4qp6foS0KMopJlPzGAgMjV6qZ0WfleOT64c3E-FRLMP5V_-mILs8a', $response->getRedirectUrl());
        $this->assertSame('GET', $response->getRedirectMethod());
    }

    public function testPurchaseFailure()
    {
        $this->setMockResponse($this->httpClient, 'PxPayPurchaseFailure.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getGatewayReference());
        $this->assertSame('Invalid Key', $response->getMessage());
    }

    public function testCompleteAuthorizeSuccess()
    {
        $this->httpRequest->query->replace(array('result' => 'abc123'));

        $this->setMockResponse($this->httpClient, 'PxPayCompletePurchaseSuccess.txt');

        $response = $this->gateway->completeAuthorize($this->options)
            ->setHttpRequest($this->httpRequest)
            ->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('0000000103f5dc65', $response->getGatewayReference());
        $this->assertSame('APPROVED', $response->getMessage());
    }

    public function testCompleteAuthorizeFailure()
    {
        $this->httpRequest->query->replace(array('result' => 'abc123'));

        $this->setMockResponse($this->httpClient, 'PxPayCompletePurchaseFailure.txt');

        $response = $this->gateway->completeAuthorize($this->options)
            ->setHttpRequest($this->httpRequest)
            ->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getGatewayReference());
        $this->assertSame('Length of the data to decrypt is invalid.', $response->getMessage());
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidResponseException
     */
    public function testCompleteAuthorizeInvalid()
    {
        $this->httpRequest->query->replace(array());

        $response = $this->gateway->completeAuthorize($this->options)
            ->setHttpRequest($this->httpRequest)
            ->send();
    }

    public function testCompletePurchaseSuccess()
    {
        $this->httpRequest->query->replace(array('result' => 'abc123'));

        $this->setMockResponse($this->httpClient, 'PxPayCompletePurchaseSuccess.txt');

        $response = $this->gateway->completePurchase($this->options)
            ->setHttpRequest($this->httpRequest)
            ->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('0000000103f5dc65', $response->getGatewayReference());
        $this->assertSame('APPROVED', $response->getMessage());
    }

    public function testCompletePurchaseFailure()
    {
        $this->httpRequest->query->replace(array('result' => 'abc123'));

        $this->setMockResponse($this->httpClient, 'PxPayCompletePurchaseFailure.txt');

        $response = $this->gateway->completePurchase($this->options)
            ->setHttpRequest($this->httpRequest)
            ->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getGatewayReference());
        $this->assertSame('Length of the data to decrypt is invalid.', $response->getMessage());
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidResponseException
     */
    public function testCompletePurchaseInvalid()
    {
        $this->httpRequest->query->replace(array());

        $response = $this->gateway->completePurchase($this->options)
            ->setHttpRequest($this->httpRequest)
            ->send();
    }
}
