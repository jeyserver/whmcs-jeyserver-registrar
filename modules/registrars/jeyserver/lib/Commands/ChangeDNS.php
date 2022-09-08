<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\Jeyserver\Exceptions\RunTimeException;

class ChangeDNS extends CommandBase
{
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

        $this->setResponse($this->api->getClient()->post('api/changeDns', [
            'form_params' => [
                'api' => 1,
                'domain' => $this->params['domain'],
                'dnses' => $dnses,
            ],
        ]));

        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not chnage dns of domain! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
    }
}
