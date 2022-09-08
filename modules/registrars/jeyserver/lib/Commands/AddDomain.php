<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\Jeyserver\Helpers\Contact;
use WHMCS\Module\Registrar\Jeyserver\Exceptions\RunTimeException;

class AddDomain extends CommandBase
{
    private ?string $handleId = null;

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $this->handleId = Contact::getOrCreateContact($this->params, $this->params);

        if (!$this->handleId) {
            throw new RunTimeException('can not create contact on jeyserver!');
        }
        Capsule::table('tbldomainsadditionalfields')->insert(array(
            "domainid" => $this->params['domainid'],
            "name" => 'jeyserver_panel_id',
            "value" => $this->handleId,
        ));

        $dnses = [];
        for ($i = 1; $i <= 5; $i++) {
            if (empty($this->params["ns{$i}"])) {
                continue;
            }
            $dnses[] = [
                'hostname' => $this->params["ns{$i}"],
            ];
        }

        $this->setResponse($this->api->getClient()->post('api/register', [
            'form_params' => [
                "api" => 1,
                "name" => $this->params['sld'],
                "tld" => $this->params['tld'],
                "panel" => $this->handleId,
                "period" => $this->params['regperiod'],
                "dnses" => $dnses,
            ],
        ]));

        $result = $this->getResult();
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not register domain! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
    }
}
