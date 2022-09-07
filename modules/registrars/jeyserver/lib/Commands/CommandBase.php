<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use ReflectionClass;
use WHMCS\Module\Registrar\Jeyserver\APIClient;

abstract class CommandBase
{
    public APIClient $api;
    /**
     * @var array<string, mixed>
     */
    public array $params;


    private ?array $result = null;
    /**
     * @var array<string>
     */
    private array $errors = [];

    /**
     * @param array<string, mixed> $params
     * @throws Exception
     */
    public function __construct(array $params)
    {
        file_put_contents("/tmp/whmcs-jeyserver-registrar-command-base.log", print_r($params, true) . "\n\n" . str_repeat('-', 50) . "\n\n", FILE_APPEND);

        $this->api = new APIClient($params);
        $this->params = $params;
        if (isset($params["sld"]) && isset($params["tld"])) {
            $this->domainName = $params["sld"] . "." . $params["tld"];
        }
    }


    /**
     * @return void
     * @throws Exception
     */
    abstract public function execute(): void;

    public function setResult(?array $result): void
    {
        $this->result = $result;
    }

    /**
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->result['status'] ?? false;
    }

    /**
     * @return string
     */
    public function getErrors(): array
    {
        return $this->result['error'];
    }
}
