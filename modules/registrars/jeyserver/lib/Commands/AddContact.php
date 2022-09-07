<?php

namespace WHMCS\Module\Registrar\Jeyserver\Commands;

use Exception;
use RunTimeException;
use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\Jeyserver\Http\Exceptions\ResponseException;

class AddContact extends CommandBase
{
    protected const REGISTRAR_ID = 4;

    /**
     * @var array<string,string>
     */
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
        $contact = [];
        foreach (array('firstname', 'lastname', 'city', 'email', 'phone') as $field) {
            $contact[$field] = $this->contact[$field];
        }
        $contact['company'] = $this->contact['companyname'] ?? $this->contact['company'] ?? '';
        $contact['country'] = $this->contact['countrycode'] ?? $this->contact['country'] ?? '';
        $contact['state'] = $this->contact['fullstate'] ?? $this->contact['state'] ?? $this->contact['statecode'] ?? '';
        $contact['address'] = ($this->contact['address1'] ?? '') . ' ' . ($this->contact['address2'] ?? '') . ' ' . ($this->contact['address'] ?? '');
        $contact['zip'] = $this->contact['postcode'] ?? $this->contact['zip'] ?? '';
        $contact['phone'] = $this->contact['phonenumberformatted'] ?? $this->contact['fullphonenumber'] ?? $this->contact['phone'];

        Capsule::table('tbldomainsadditionalfields')->insert(array(
            'domainid' => $this->params['domainid'],
            'name' => 'jeyserver_contact_information',
            'value' => json_encode($contact),
        ));

        $params = [
            'registrar' => self::REGISTRAR_ID,
        ];
        $prefix = chr(100) . chr(111) . chr(109) . chr(97) . chr(105) . chr(110) . chr(110) . chr(97) . chr(109) . chr(101) . chr(97) . chr(112) . chr(105);
        foreach ($contact as $key => $val) {
            $params["{$prefix}_{$key}"] = $val;
        }

        $response = $this->api->getClient()->post('api/create-panel', [
            'form_params' => $params,
        ]);
        $result = $response->json();
        $this->setResult($result);

        if (!$this->wasSuccessful()) {
            throw new RunTimeException('JeyServer: can not add contact in jeyserver! (' . json_encode($result) . ')');
        }
        /** @var array{panel:string} $result */
        $this->handle = $result['panel'];
    }

    public function getContactHandle(): ?string
    {
        return $this->handle;
    }
}
