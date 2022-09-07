<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use RunTimeException;
use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\Jeyserver\Features\Contact;
use WHMCS\Module\Registrar\Jeyserver\Helpers\AdditionalFields;

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

        $response = $this->api->getClient()->post('api/changeDns', [
            'form_params' => [
                'api' => 1,
                'domain' => $this->params['domain'],
                'dnses' => $dnses,
            ],
        ]);
        $result = $response->json();
        $this->setResult($result);
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not chnage dns of domain! (' . json_encode($result) . ')');
        }
    }
}
