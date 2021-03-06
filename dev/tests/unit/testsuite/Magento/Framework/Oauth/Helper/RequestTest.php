<?php
/**
 * Test WebAPI authentication helper.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth\Helper;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Oauth\Helper\Request */
    protected $oauthRequestHelper;

    /** @var \Magento\Framework\App\Response\Http */
    protected $response;

    protected function setUp()
    {
        $this->oauthRequestHelper = new \Magento\Framework\Oauth\Helper\Request();
        $this->response = $this->getMock(
            'Magento\Framework\HTTP\PhpEnvironment\Response',
            ['setHttpResponseCode'],
            [],
            '',
            false
        );
    }

    protected function tearDown()
    {
        unset($this->oauthRequestHelper, $this->response);
    }

    /**
     * @dataProvider dataProviderForPrepareErrorResponseTest
     */
    public function testPrepareErrorResponse($exception, $expected)
    {
        $this->response
            ->expects($this->once())
            ->method('setHttpResponseCode')
            ->with($expected[1]);

        $errorResponse = $this->oauthRequestHelper->prepareErrorResponse($exception, $this->response);
        $this->assertEquals(['oauth_problem' => $expected[0]], $errorResponse);
    }

    public function dataProviderForPrepareErrorResponseTest()
    {
        return [
            [
                new \Magento\Framework\Oauth\OauthInputException('msg'),
                ['msg', \Magento\Framework\Oauth\Helper\Request::HTTP_BAD_REQUEST],
            ],
            [
                new \Exception('msg'),
                ['internal_error&message=msg', \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR]
            ],
            [
                new \Exception(),
                [
                    'internal_error&message=empty_message',
                    \Magento\Framework\Oauth\Helper\Request::HTTP_INTERNAL_ERROR
                ]
            ]
        ];
    }

    /**
     * @dataProvider hostsDataProvider
     */
    public function testGetRequestUrl($url, $host)
    {
        $httpRequestMock = $this->getMock(
            'Magento\Framework\App\Request\Http',
            ['getHttpHost', 'getScheme', 'getRequestUri'],
            [],
            '',
            false
        );

        $httpRequestMock->expects($this->any())->method('getHttpHost')->will($this->returnValue($host));
        $httpRequestMock->expects($this->any())->method('getScheme')->will($this->returnValue('http'));
        $httpRequestMock->expects($this->any())->method('getRequestUri')->will($this->returnValue('/'));

        $this->assertEquals($url, $this->oauthRequestHelper->getRequestUrl($httpRequestMock));
    }

    public function hostsDataProvider()
    {
        return  [
            'hostWithoutPort' => [
                'url' => 'http://localhost/',
                'host' => 'localhost'
            ],
            'hostWithPort' => [
                'url' => 'http://localhost:81/',
                'host' => 'localhost:81'
            ]
        ];
    }
}
