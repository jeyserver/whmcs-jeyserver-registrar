<?php

namespace WHMCS\Module\Registrar\Jeyserver\Helpers;

use Exception;
use WHMCS\Module\Registrar\Jeyserver\Commands\AddContact;
use WHMCS\Module\Registrar\Jeyserver\Commands\AddIRContact;

class Contact
{
    /**
     * @param array<string, mixed> $contactDetails
     * @param array<string, mixed> $params
     * @return string Contact Handle
     * @throws Exception
     */
    public static function getOrCreateContact(array $contactDetails, array $params): ?string
    {
        $addContact = $params['tld'] == 'ir' ?
            new AddIRContact($params, $contactDetails) :
            new AddContact($params, $contactDetails);
        $addContact->execute();
        return $addContact->getContactHandle();
    }
}
