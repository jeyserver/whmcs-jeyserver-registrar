<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use RunTimeException;
use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\Jeyserver\Features\Contact;
use WHMCS\Module\Registrar\Jeyserver\Helpers\AdditionalFields;

class LockTransfer extends CommandBase
{
    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $response = $this->api->getClient()->post('api/lock', [
            'form_params' => [
                'api' => 1,
                'domain' => $this->params['domain'],
            ],
        ]);
        $result = $response->json();
        $this->setResult($result);
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not register domain! (' . json_encode($result) . ')');
        }
        if (isset($result['is_lock']) and !$result['is_lock']) {
            throw new RunTimeException('[JeyServer]: something is wrong in lock transfer from ourside! (' . json_encode($result) . ')');
        }
    }
}
