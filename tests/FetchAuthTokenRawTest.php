<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\PixPayment\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\PixPayment\Responses\TokenResponse;
use BrokeYourBike\PixPayment\Interfaces\ConfigInterface;
use BrokeYourBike\PixPayment\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class FetchAuthTokenRawTest extends TestCase
{
    /** @test */
    public function it_can_prepare_request(): void
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://example.com/');
        $mockedConfig->method('getClientId')->willReturn('john');
        $mockedConfig->method('getClientSecret')->willReturn('p@ssword');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "access_token": "**",
                "token_type": "Bearer",
                "expires_in": 3600,
                "scope": "default"
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->once()->andReturn($mockedResponse);

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);
        $requestResult = $api->fetchAuthTokenRaw();

        $this->assertInstanceOf(TokenResponse::class, $requestResult);
        $this->assertEquals('**', $requestResult->access_token);
    }
}
