<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Module\Registrar\Jeyserver\Exceptions\RunTimeException;

class UnlockTransfer extends CommandBase
{
    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $this->setResponse($this->api->getClient()->post('api/unlock', [
            'form_params' => [
                'api' => 1,
                'domain' => $this->params['domain'],
            ],
        ]));
        $result = $this->getResult();
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not register domain! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
        if (isset($result['is_lock']) and $result['is_lock']) {
            throw new RunTimeException('[JeyServer]: something is wrong in unlock transfer from ourside! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
    }
}
