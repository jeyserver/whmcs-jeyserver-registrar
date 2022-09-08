<?php
namespace WHMCS\Module\Registrar\Jeyserver\Exceptions;

use WHMCS\Module\Registrar\Jeyserver\IException;

class BadMethodCallException extends \BadMethodCallException implements IException
{
}