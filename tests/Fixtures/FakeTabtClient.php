<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use Yoerioptr\TabtApiClient\Client\ClientInterface;
use Yoerioptr\TabtApiClient\Entries\CredentialsType;
use Yoerioptr\TabtApiClient\Request\RequestInterface;
use Yoerioptr\TabtApiClient\Response\ResponseInterface;

final class FakeTabtClient implements ClientInterface
{
    /**
     * @param array<string, mixed> $rawResponse
     */
    public function __construct(private readonly array $rawResponse)
    {
    }

    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        $responseClass = $request->getResponseClass();

        return new $responseClass($this->rawResponse);
    }

    public function setCredentials(CredentialsType $credentials): void
    {
    }
}
