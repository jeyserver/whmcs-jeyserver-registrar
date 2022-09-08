<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Module\Registrar\Jeyserver\Exceptions\RunTimeException;

class StatusDomain extends CommandBase
{
    const JEYSERVER_STATUS_ACTIVE = 1;
    const JEYSERVER_STATUS_DEACTIVE = 1;
    protected int $status;

    protected bool $isLocked;

    protected int $expireDate;


    /**
     * @param array<string, mixed> $params
     * @throws Exception
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->execute();
    }

    public function execute(): void
    {
        $this->setResponse($this->api->getClient()->post('api/update', [
            'form_params' => [
                'api' => 1,
                'domain' => $this->params['domain'],
                'maxAge' => 172800, // 2 days
            ],
        ]));
        /** @var array{service:array{is_lock:bool,datend:int,status:int}} */
        $result = $this->getResult();
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not get status of domain! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
        $this->isLocked = $result['service']['is_lock'];
        $this->expireDate = (int) $result['service']['datend'];
        $this->status = (int) $result['service']['status'];
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function getExpireDate(): int
    {
        return $this->expireDate;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
