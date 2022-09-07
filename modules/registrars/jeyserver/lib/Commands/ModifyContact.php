<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use BadMethodCallException;

class ModifyContact extends CommandBase
{

    protected array $contact;

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $contact
     */
    public function __construct(array $params, array $contact)
    {
        parent::__construct($params);

        $this->contact = $contact;
    }

    public function execute(): void
    {
        throw new BadMethodCallException('JeyServer: currently can not modify contact (coming soon!)');
    }
}
