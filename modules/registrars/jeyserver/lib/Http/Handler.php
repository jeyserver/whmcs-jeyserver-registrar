<?php

namespace WHMCS\Module\Registrar\Jeyserver\Http;

interface Handler
{
    /**
     * @param array<string, string> $options
     */
    public function fire(Request $request, array $options): Response;
}
