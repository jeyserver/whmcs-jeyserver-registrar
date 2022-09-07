<?php

namespace WHMCS\Module\Registrar\Jeyserver\Http;

use TypeError;

class ClientFactory
{
    public static function getClient(array $options): Client
    {
        return new Client($options);
    }
}
