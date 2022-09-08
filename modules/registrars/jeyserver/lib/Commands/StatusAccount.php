<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Module\Registrar\Jeyserver\Exceptions\RunTimeException;

class StatusAccount extends CommandBase
{
    protected ?float $credit = null;
    protected ?string $currencyTitle = null;

    public function execute(): void
    {
        $this->setResponse($this->api->getClient()->get('fa/userpanel/profile/view', [
            'query' => [
                'api' => 1,
                'ajax' => 1,
            ],
        ]));
        /** @var array{credit:float,currency:string} */
        $result = $this->getResult();
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not get status of account! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
        $this->credit = (float) $result['credit'];
        $this->currencyTitle = (string) $result['currency'];
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function getCurrencyTitle(): ?string
    {
        return $this->currencyTitle;
    }
}
