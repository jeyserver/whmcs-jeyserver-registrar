<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\Jeyserver\Exceptions\RunTimeException;

class AddIRContact extends CommandBase
{
    protected const REGISTRAR_ID = 2;

    /** @var array<string,mixed> */
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
        if (!isset($this->params['additionalfields']['IRNIC-Handle']) or
            !preg_match('/^[a-z]{2}[0-9]{2,5}-irnic$/i', $this->params['additionalfields']['IRNIC-Handle'])
        ) {
            throw new RunTimeException('the IRNIC-Handle is invalid! (' . $this->params['additionalfields']['IRNIC-Handle'] . ')');
        }

        Capsule::table('tbldomainsadditionalfields')->insert(array(
            'domainid' => $this->params['domainid'],
            'name' => 'jeyserver_contact_information',
            'value' => json_encode([
                'IRNIC-Handle' => $this->params['additionalfields']['IRNIC-Handle'],
            ]),
        ));

        $this->setResponse($this->api->getClient()->post('api/create-panel', [
            'form_params' => [
                'registrar' => self::REGISTRAR_ID,
                'sysnic_handler' => $this->params['additionalfields']['IRNIC-Handle'],
            ],
        ]));

        /** @var array{panel:string} */
        $result = $this->getResult();
        if (!$this->wasSuccessful()) {
            throw new RunTimeException('can not add contact in jeyserver! (' . ((string)$this->getResponse()->getBody()) . ')');
        }
        $this->handle = $result['panel'];
    }

    public function getContactHandle(): ?string
    {
        return $this->handle;
    }
}
