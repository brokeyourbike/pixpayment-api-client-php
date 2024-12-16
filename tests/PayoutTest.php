<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\PixPayment\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\PixPayment\Responses\PayoutResponse;
use BrokeYourBike\PixPayment\Interfaces\TransactionInterface;
use BrokeYourBike\PixPayment\Interfaces\ConfigInterface;
use BrokeYourBike\PixPayment\Enums\TransactionStatusEnum;
use BrokeYourBike\PixPayment\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class PayoutTest extends TestCase
{
    /** @test */
    public function it_can_prepare_request(): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "beneficiary": {
                    "lastName": "beneficiary_lastname",
                    "firstName": "beneficiary_firstname",
                    "msisdn": "beneficiary_msisdn",
                    "country": "SN",
                    "address": "beneficiary_address"
                },
                "accountNumber": "account_number",
                "partnerCode": "PIX",
                "operator": "orange",
                "amount": 500,
                "msisdn": "sender_msisdn",
                "currency": "XOF",
                "country": "SN",
                "channel": "web",
                "paymentId": "PIX-123456789",
                "status": "InProgress",
                "transactionType": "TRANSFER"
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->once()->andReturn($mockedResponse);

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mockedCache->method('has')->willReturn(true);
        $mockedCache->method('get')->willReturn('secure-token');

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $requestResult = $api->payout($transaction);
        $this->assertInstanceOf(PayoutResponse::class, $requestResult);
        $this->assertEquals(TransactionStatusEnum::IN_PROGRESS->value, $requestResult->status);
    }
}
