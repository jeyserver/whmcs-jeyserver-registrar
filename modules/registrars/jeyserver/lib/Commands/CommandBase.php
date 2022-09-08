<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use ReflectionClass;
use Psr\Http\Message\ResponseInterface;
use WHMCS\Module\Registrar\Jeyserver\APIClient;

abstract class CommandBase
{
    public APIClient $api;
    /**
     * @var array<string,mixed>
     */
    public array $params;

    private ?ResponseInterface $response = null;

    /**
     * @var array<string,mixed>|null
     */
    private ?array $result = null;

    /**
     * @param array<string, mixed> $params
     * @throws Exception
     */
    public function __construct(array $params)
    {
        $this->api = new APIClient($params);
        $this->params = $params;
    }


    /**
     * @return void
     * @throws Exception
     */
    abstract public function execute(): void;

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
        $this->result = json_decode((string)$response->getBody(), true);
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getResult(): ?array
    {
        return $this->result;
    }

    /**
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->result['status'] ?? false;
    }

    /**
     * @return array<mixed>
     */
    public function getErrors(): array
    {
        return $this->result['error'] ?? [];
    }
}
