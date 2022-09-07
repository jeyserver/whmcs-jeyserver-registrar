<?php

namespace WHMCS\Module\Registrar\Jeyserver\Http\Exceptions;

use Exception;
use WHMCS\Module\Registrar\Jeyserver\IException;
use WHMCS\Module\Registrar\Jeyserver\Http\Request;
use WHMCS\Module\Registrar\Jeyserver\Http\Response;

class ResponseException extends Exception implements IException
{
    private Request $request;
    private Response $response;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct('An error in http request to: [' . $request->getURL() . '] http status code: [' . $response->getStatusCode() . ']');
        $this->request = $request;
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function __toString(): string
    {
        return '[ResponseException]' .
            ' request: ' . json_encode($this->request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
            ' response: ' . json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
