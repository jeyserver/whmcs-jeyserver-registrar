<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Module\Registrar\Jeyserver\Exceptions\RunTimeException;

class RenewDomain extends CommandBase
{
    private \WHMCS\Domain\Domain $domain;

    /**
     * @param array<string, mixed> $params
     * @throws Exception
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->domain = \WHMCS\Domain\Domain::find($params['domainid']);
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $this->setResponse($this->api->getClient()->post('api/renew', [
            'form_params' => [
                'api' => 1,
                'domain' => $this->params['domain'],
                'period' => $this->params['regperiod'],
            ],
        ]));

        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not renew domain! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
    }
}
