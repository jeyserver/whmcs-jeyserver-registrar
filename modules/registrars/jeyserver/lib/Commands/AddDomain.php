<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use RunTimeException;
use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\Jeyserver\Helpers\Contact;

class AddDomain extends CommandBase
{
    private ?string $handleId = null;

    /**
     * @param array<string, mixed> $params
     * @throws Exception
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->handleId = Contact::getOrCreateContact($params, $params);
        if (!$this->handleId) {
            throw new RunTimeException('can not create contact on jeyserver!');
        }
        Capsule::table('tbldomainsadditionalfields')->insert(array(
            "domainid" => $params['domainid'],
            "name" => 'jeyserver_panel_id',
            "value" => $this->handleId,
        ));
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $dnses = [];
        for ($i = 1; $i <= 5; $i++) {
            if (empty($this->params["ns{$i}"])) {
                continue;
            }
            $dnses[] = [
                'hostname' => $this->params["ns{$i}"],
            ];
        }

        $response = $this->api->getClient()->post('api/register', [
            'form_params' => [
                "api" => 1,
                "name" => $this->params['sld'],
                "tld" => $this->params['tld'],
                "panel" => $this->handleId,
                "period" => $this->params['regperiod'],
                "dnses" => $dnses,
            ],
        ]);
        $result = $response->json();
        $this->setResult($result);
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not register domain! (' . json_encode($result) . ')');
        }
    }
}
