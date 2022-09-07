<?php

/**
 * WHMCS JeyServer Registrar Module
 *
 * @author JeyServer Development Team <info@jeyserver.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Exception;
use WHMCS\Carbon;
use WHMCS\Database\Capsule;
use WHMCS\Domain\Registrar\Domain;
use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;
use WHMCS\Exception\Module\InvalidConfiguration;
use WHMCS\Module\Registrar\Jeyserver\Commands\AddDomain;
use WHMCS\Module\Registrar\Jeyserver\Commands\CheckDomains;
use WHMCS\Module\Registrar\Jeyserver\Commands\LockTransfer;
use WHMCS\Module\Registrar\Jeyserver\Commands\ChangeDNS;
use WHMCS\Module\Registrar\Jeyserver\Commands\UnlockTransfer;
use WHMCS\Module\Registrar\Jeyserver\Commands\RenewDomain;
use WHMCS\Module\Registrar\Jeyserver\Commands\StatusAccount;
use WHMCS\Module\Registrar\Jeyserver\Commands\StatusDomain;
use WHMCS\Module\Registrar\Jeyserver\Commands\StatusDomainTransfer;
use WHMCS\Module\Registrar\Jeyserver\Commands\TransferDomain;
use WHMCS\Module\Registrar\Jeyserver\Helpers\Contact;
use WHMCS\Module\Registrar\Jeyserver\APIClient;
use WHMCS\Module\Registrar\Jeyserver\Http;

const JEYSERVER_VERSION = "1.0.0";

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @param array<string, mixed> $params
 * @return array<string, mixed>
 */
function jeyserver_getConfigArray(array $params): array
{
    $additionalfieldsFilePath = ROOTDIR . '/resources/domains/additionalfields.php';
    $additionalfieldsCode = "if (is_file('{$additionalfieldsFilePath}')) {\n\trequire_once '{$additionalfieldsFilePath}';\n}";

    if (!is_file($additionalfieldsFilePath)) {
        file_put_contents($additionalfieldsFilePath, "<?php\n\n" . $additionalfieldsCode . "\n\n");
    } elseif (is_readable($additionalfieldsFilePath)) {
        $additionalfieldsFileContent = file_get_contents($additionalfieldsFilePath);
        if (
            $additionalfieldsFileContent !== false and
            stripos($additionalfieldsFileContent, 'jeyserver/lib/additionalfields.php') === false and
            is_writable($additionalfieldsFilePath)
        ) {
            file_put_contents($additionalfieldsFilePath, $additionalfieldsFileContent . "\n\n" . $additionalfieldsCode . "\n\n");
        }
    }

    $msgRegister = "Don't have a JeyServer Account yet? Get one here:"
        . " <a target=\"_blank\" href=\"https://www.jeyserver.com/fa/userpanel/register\">www.jeyserver.com/fa/userpanel</a>";

    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'JeyServer (v' . JEYSERVER_VERSION . ')',
        ],
        'Description' => [
            'Type' => 'System',
            'Value' => $msgRegister . $msgUpdate,
        ],
        'jeyserver_api_key' => [
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your jeyserver API key here!',
        ],
    ];
}

/**
 * Validate config array
 */
function jeyserver_config_validate($params): void
{
    if (!isset($params['jeyserver_api_key']) or !$params['jeyserver_api_key']) {
        throw new InvalidConfiguration('You Should Give API Key!');
    }
    if (!is_string($params['jeyserver_api_key'])) {
        throw new InvalidConfiguration('The API Key Should Be String!');
    }
}

/**
 * Check Domain Availability.
 *
 * Determine if a domain or group of domains are available for
 * registration or transfer.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, string>|ResultsList<SearchResult> An ArrayObject based collection of \WHMCS\Domains\DomainLookup\SearchResult results
 * @throws Exception Upon domain availability check failure.
 * @see \WHMCS\Domains\DomainLookup\ResultsList
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 * @see \WHMCS\Domains\DomainLookup\SearchResult
 */
function jeyserver_CheckAvailability(array $params)
{
    return new ResultsList();
    die;

    /*

    $status = SearchResult::STATUS_NOT_REGISTERED;
        break;
    case 211:
        $status = SearchResult::STATUS_REGISTERED;
        break;
    default:
        $status = SearchResult::STATUS_TLD_NOT_SUPPORTED;

    */

    /*
    try {
        $tldsToInclude = $params['tldsToInclude'];
        $results = new ResultsList();

        foreach (array_chunk($tldsToInclude, 32) as $tlds) {
            $checkDomains = new CheckDomains($params, $tlds);
            $checkDomains->execute();
            $i = 0;
            foreach ($checkDomains->getResults() as $searchResult) {
                switch (substr($checkDomains->api->properties["DOMAINCHECK"][$i++], 0, 3)) {
                    case 210:
                        $status = SearchResult::STATUS_NOT_REGISTERED;
                        break;
                    case 211:
                        $status = SearchResult::STATUS_REGISTERED;
                        break;
                    default:
                        $status = SearchResult::STATUS_TLD_NOT_SUPPORTED;
                }
                $searchResult->setStatus($status);
                $results->append($searchResult);
            }
        }
        return $results;
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
    */
}


/**
 * Register a domain.
 *
 * Attempt to register a domain with the domain registrar.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain registration order
 * * When a pending domain registration order is accepted
 * * Upon manual request by an admin user
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @throws Exception
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_RegisterDomain(array $params): array
{
    $register = new AddDomain($params);
    try {
        $register->execute();
        return ['success' => true];
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Renew a domain.
 *
 * Attempt to renew/extend a domain for a given number of years.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain renewal order
 * * When a pending domain renewal order is accepted
 * * Upon manual request by an admin user
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_RenewDomain(array $params): array
{
    try {
        $renew = new RenewDomain($params);
        $renew->execute();
        return ['success' => true];
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Fetch current nameservers.
 *
 * This function should return an array of nameservers for a given domain.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_GetNameservers(array $params): array
{
    $dnses = dns_get_record($params['domain'], DNS_NS);

    if ($dnses === false) {
        return ['error' => 'can not get dnses of the domain'];
    }

    $result = [];
    $index = 1;
    foreach ($records as $record) {
        $result["ns" . ($index++)] = $record['target'];
    }
    return $result;
}

/**
 * Save nameserver changes.
 *
 * This function should submit a change of nameservers request to the
 * domain registrar.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_SaveNameservers(array $params): array
{
    try {
        $domain = new ChangeDNS($params);
        $domain->execute();
        return ['success' => true];
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Get the current WHOIS Contact Information.
 *
 * Should return a multi-level array of the contacts and name/address
 * fields that be modified.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_GetContactDetails(array $params): array
{
    $result = [];

    $contactJson = Capsule::table('tbldomainsadditionalfields')
        ->where('domainid', '=', $params['domainid'])
        ->where('name', 'jeyserver_contact_information')
        ->value('value');
    if ($contactJson) {
        $result['Registrant'] = [];

        $contact = json_decode($contactJson, true);
        if ($contact === false) {
            return ['error' => 'JeyServer: Can not get contact information!'];
        }
        foreach ($contact as $key => $value) {
            $result['Registrant'][ucfirst($key)] = $value;
        }
    }
    return $result;
}

/**
 * update WHOIS Contact Information.
 *
 * currently, jeyserver does not support change whois info from api!
 * Should return a multi-level array of the contacts and name/address
 * fields that be modified.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_SaveContactDetails()
{
    return ['error' => 'JeyServer: Currently, can not edit contact information (coming soon)!'];
}

/**
 * Get registrar lock status.
 *
 * Also known as Domain Lock or Transfer Lock status.
 *
 * @param array<string, mixed> $params common module parameters
 * @return string|array<string, string> Lock status or error message
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_GetRegistrarLock(array $params)
{
    try {
        $command = new StatusDomain($params);
        $command->execute();
        return $command->isLocked() ? "locked" : "unlocked";
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Set registrar lock status.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_SaveRegistrarLock(array $params): array
{
    try {
        $lock = true;
        if (isset($this->params['lockenabled'])) {
            $lock = $this->params['lockenabled'] == "locked";
        } elseif (isset($this->params['TransferLock'])) {
            $lock = $this->params["TransferLock"] == "on";
        }

        $command = $lock ? new LockTransfer($params) : new UnlockTransfer($params);
        $domain->execute();
        return ['success' => 'success'];
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Get DNS Records for DNS Host Record Management.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed> DNS Host Records
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_GetDNS(array $params): array
{
    try {
        $dnses = dns_get_record($params['domain'], DNS_NS);

        if ($dnses === false) {
            return ['error' => 'can not get dnses of the domain'];
        }

        $result = [];
        $index = 1;
        foreach ($records as $record) {
            $result["ns" . ($index++)] = $record['target'];
        }
        return $result;
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Update DNS Host Records.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_SaveDNS(array $params): array
{
    try {
        $command = new ChangeDNS($params);
        $command->execute();
        return [];
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Sync Domain Status & Expiration Date.
 *
 * Domain syncing is intended to ensure domain status and expiry date
 * changes made directly at the domain registrar are synced to WHMCS.
 * It is called periodically for a domain.
 *
 * @param array<string, mixed> $params common module parameters
 * @return array<string, mixed>
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 */
function jeyserver_Sync(array $params): array
{
    try {
        $command = new StatusDomain($params);
        $command->execute();
        return [
            'active' => $domain->getStatus() == StatusDomain::JEYSERVER_STATUS_ACTIVE,
            'expired' => $domain->getExpireDate() < time(),
            'expirydate' => Carbon::createFromFormat('Y-m-d H:i:s', $domain->getExpireDate())->toDateString()
        ];
    } catch (Exception $ex) {
        return ['error' => $ex->getMessage()];
    }
}

/**
 * Client Area Output.
 *
 * This function renders output to the domain details interface within
 * the client area. The return should be the HTML to be output.
 *
 * @return string|null HTML Output
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */
function jeyserver_ClientArea(array $params): ?string
{
    return <<<HTML
    
    HTML;
}

/**
 * Returns customer account details such as amount, currency, deposit etc.
 *
 * @return array<string, mixed>
 */
function jeyserver_getAccountDetails(): array
{
    try {
        $statusAccount = new StatusAccount([]);
        $statusAccount->execute();
        return [
            "success" => true,
            "amount" => $statusAccount->getCredit(),
            "currency" => $statusAccount->getCurrencyTitle(),
        ];
    } catch (Exception $e) {
        return [
            "success" => false
        ];
    }
}
