<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Module\Registrar\Jeyserver\Http\Exceptions\ResponseException;

class AddIRContact extends CommandBase
{
    protected const IR_REGISTRAR_ID = 2;
    protected const GENERAL_REGISTRAR_ID = 4;

    private array $contact = [];

    private ?string $handle = null;
    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $contact
     */
    public function __construct(array $params, array $contact)
    {
        parent::__construct($params);

        $this->contact = $contact;
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $response = $this->api->getClient()->post('api/create-panel', [
            'form_params' => [
                'registrar' => self::REGISTRAR_ID,
                'sysnic_handler' => $this->contact['IRNic-Handler'],
            ],
        ]);
        $result = $response->json();
        $this->setResult($result);

        if (!$this->wasSuccessful()) {
            throw new RunTimeException('can not add contact in jeyserver!');
        }
        $this->handle = $result['panel'];
    }

    public function getContactHandle(): ?string
    {
        return $this->handle;
    }
}
