<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use RunTimeException;
use WHMCS\Domain\Registrar\Domain;
use WHMCS\Module\Registrar\Jeyserver\Helpers\ZoneInfo;
use WHMCS\Module\Registrar\Jeyserver\Models\ZoneModel;
use Illuminate\Database\Capsule\Manager as DB;

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
        $response = $this->api->getClient()->post('api/renew', [
            'form_params' => [
                'api' => 1,
                'domain' => $this->params['domain'],
                'period' => $this->params['regperiod'],
            ],
        ]);

        $result = $response->json();
        $this->setResult($result);
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not renew domain! (' . json_encode($this->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ')');
        }
    }
}
