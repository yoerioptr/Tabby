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
     * @var array<string, int>
     */
    private array $cursors = [];

    /**
     * Raw responses, either a single response returned for every request or a
     * map keyed by request endpoint. An endpoint can map to a list of responses
     * that are consumed in order on every subsequent call.
     *
     * @param array<string, mixed> $rawResponse
     */
    public function __construct(private readonly array $rawResponse)
    {
    }

    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        $responseClass = $request->getResponseClass();

        return new $responseClass($this->payload($request->getEndpoint()));
    }

    public function setCredentials(CredentialsType $credentials): void
    {
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $endpoint): array
    {
        if (!array_key_exists($endpoint, $this->rawResponse)) {
            return $this->rawResponse;
        }

        $responses = $this->rawResponse[$endpoint];

        if (!array_is_list($responses)) {
            return $responses;
        }

        $index = $this->cursors[$endpoint] ?? 0;
        $this->cursors[$endpoint] = $index + 1;

        return $responses[$index] ?? ($responses[count($responses) - 1] ?? []);
    }
}
