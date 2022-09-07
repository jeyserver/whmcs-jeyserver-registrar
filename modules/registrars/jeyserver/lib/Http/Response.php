<?php

namespace WHMCS\Module\Registrar\Jeyserver\Http;

class Response
{
    private int $statusCode;
    private ?string $primaryIP;
    /**
     * @var array<string, string>
     */
    private array $headers = [];
    private ?string $body;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(int $status = 200, array $headers = [])
    {
        $this->setStatusCode($status);
        $this->setHeaders($headers);
    }
    public function setStatusCode(int $status)
    {
        $this->statusCode = $status;
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function setHeader(string $name, string $value): void
    {
        $this->headers[strtolower($name)] = $value;
    }
    public function getHeader(string $name): ?string
    {
        $name = strtolower($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }
    public function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }
    public function setBody(string $body)
    {
        $this->body = $body;
    }
    public function getBody(): ?string
    {
        return $this->body;
    }
    public function json(): ?array
    {
        if (is_string($this->getBody())) {
            $json = json_decode($this->getBody(), true);
            if ($json !== false) {
                return $json;
            }
        }
        return null;
    }
    public function setPrimaryIP(?string $ip): void
    {
        $this->primaryIP = $ip;
    }
    public function getPrimaryIP(): ?string
    {
        return $this->primaryIP;
    }
}
