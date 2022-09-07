<?php

namespace WHMCS\Module\Registrar\Jeyserver;

use WHMCS\Module\Registrar\Jeyserver\Http\{Client, ClientFactory};
use WHMCS\Domain\Registrar\Domain;
use Exception;

class APIClient
{
    public string $command;

    /**
     * @var array<string, mixed>
     */
    public array $params = [];

    /**
     * @var Client
     */
    public ?Client $client = null;

    /**
     * @param array<string, mixed> $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        if (!function_exists("getregistrarconfigoptions")) {
            include implode(DIRECTORY_SEPARATOR, [ROOTDIR, "includes", "registrarfunctions.php"]);
        }

        $this->params = array_replace_recursive(
            \getregistrarconfigoptions("jeyserver") ?? [],
            $params
        );
    }

    /**
     * @param array<string,mixed> $options
     */
    public function getClient(array $options = []): Client
    {
        if (!$this->client) {
            $headers = [];
            if (isset($GLOBALS["CONFIG"]["Version"]) and is_string($GLOBALS["CONFIG"]["Version"])) {
                $headers['User-Agent'] = 'WHMCS(' . $GLOBALS["CONFIG"]["Version"] . ')-JeyServer-Registrar(' . JEYSERVER_VERSION . ')';
            } else {
                $headers['User-Agent'] = 'WHMCS-JeyServer-Registrar(' . JEYSERVER_VERSION . ')';
            }
            if (isset($GLOBALS["CONFIG"]["SystemURL"]) and is_string($GLOBALS["CONFIG"]["SystemURL"])) {
                $headers['Referer'] = $GLOBALS["CONFIG"]["SystemURL"];
            }
            if (isset($GLOBALS["CONFIG"]["CompanyName"]) and is_string($GLOBALS["CONFIG"]["CompanyName"])) {
                $headers['X-WHMCS-COMPANY-NAME'] = $GLOBALS["CONFIG"]["CompanyName"];
            }
            $this->client = ClientFactory::getClient(array_replace_recursive(
                array(
                    'headers' => $headers,
                    'base_uri' => 'http://jey.jey/fa/userpanel/domains/',
                    'auth' => 'bearer ' . $this->params['jeyserver_api_key'],
                    'query' => array(
                        'api' => 1,
                        'ajax' => 1,
                    ),
                ), $options
            ));
        }
        return $this->client;
    }
}
