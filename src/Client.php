<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\PixPayment;

use Psr\SimpleCache\CacheInterface;
use GuzzleHttp\ClientInterface;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\PixPayment\Responses\TokenResponse;
use BrokeYourBike\PixPayment\Responses\PayoutResponse;
use BrokeYourBike\PixPayment\Interfaces\TransactionInterface;
use BrokeYourBike\PixPayment\Interfaces\ConfigInterface;
use BrokeYourBike\HttpEnums\HttpMethodEnum;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class Client implements HttpClientInterface
{
    use HttpClientTrait;
    use ResolveUriTrait;
    use HasSourceModelTrait;

    private ConfigInterface $config;
    private CacheInterface $cache;

    public function __construct(ConfigInterface $config, ClientInterface $httpClient, CacheInterface $cache)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function authTokenCacheKey(): string
    {
        return get_class($this) . ':authToken:';
    }

    public function getAuthToken(): string
    {
        if ($this->cache->has($this->authTokenCacheKey())) {
            $cachedToken = $this->cache->get($this->authTokenCacheKey());
            if (is_string($cachedToken)) {
                return $cachedToken;
            }
        }

        $response = $this->fetchAuthTokenRaw();
        $this->cache->set($this->authTokenCacheKey(), $response->access_token, $response->expires_in);
        return (string) $response->access_token;
    }

    public function fetchAuthTokenRaw(): TokenResponse
    {
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->config->getClientId()}:{$this->config->getClientSecret()}"),
            ],
            \GuzzleHttp\RequestOptions::FORM_PARAMS => [
                'grant_type' => 'client_credentials',
            ],
        ];

        $response = $this->httpClient->request(
            HttpMethodEnum::POST->value,
            (string) $this->resolveUriFor(rtrim($this->config->getAuthUrl(), '/'), '/oauth2/token'),
            $options
        );

        return new TokenResponse($response);
    }

    public function payout(TransactionInterface $transaction): PayoutResponse
    {
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->getAuthToken()}",
            ],
            \GuzzleHttp\RequestOptions::JSON => [
                'beneficiary' => [
                    'lastName' => $transaction->getRecipientFirstName(),
                    'firstName' => $transaction->getRecipientLastName(),
                    'msisdn' => $transaction->getRecipientPhone(),
                    'country' => $transaction->getRecipientCountry(),
                    'address' => $transaction->getRecipientCountry(),
                ],
                'accountNumber' => $this->config->getAccountId(),
                'partnerCode' => $this->config->getPartnerCode(),
                'operator' => $transaction->getRecipientProvider(),
                'amount' => $transaction->getAmount(),
                'currency' => $transaction->getCurrency(),
                'country' => $transaction->getSenderCountry(),
                'msisdn' => $transaction->getSenderPhone(),
                'transactionType' => 'TRANSFER',
            ],
        ];

        if ($transaction instanceof SourceModelInterface){
            $options[\BrokeYourBike\HasSourceModel\Enums\RequestOptions::SOURCE_MODEL] = $transaction;
        }

        $response = $this->httpClient->request(
            HttpMethodEnum::POST->value,
            (string) $this->resolveUriFor(rtrim($this->config->getUrl(), '/'), '/api/1/payment'),
            $options
        );

        return new PayoutResponse($response);
    }

    public function status(string $paymentId): PayoutResponse
    {
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->getAuthToken()}",
            ],
        ];

        $response = $this->httpClient->request(
            HttpMethodEnum::GET->value,
            (string) $this->resolveUriFor(rtrim($this->config->getUrl(), '/'), "/api/1/status/{$paymentId}"),
            $options
        );

        return new PayoutResponse($response);
    }
}
