<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;

class StatusAccount extends CommandBase
{
    protected ?float $credit = null;
    protected ?string $currencyTitle = null;

    public function execute(): void
    {
        $response = $this->api->getClient()->get('fa/userpanel/profile/view', [
            'query' => [
                'api' => 1,
                'ajax' => 1,
            ],
        ]);
        $result = $response->json();
        $this->setResult($result);
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not get status of account! (' . json_encode($result) . ')');
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
