<?php

namespace WHMCS\Module\Registrar\Jeyserver\Http;

class Curl implements Handler
{
    public function fire(Request $request, array $options): Response
    {
        $ch = curl_init($request->getURL());
        $fh = null;
        $header = '';
        $body = '';

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($request->getMethod() != "GET") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
            $reqBody = $request->getBody();
            if (is_string($reqBody) or is_array($reqBody)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getBody());
            }
        }
        if ($request->getMethod() == 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        if (isset($options['timeout']) and $options['timeout'] > 0) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
        }
        if (isset($options['connect_timeout']) and $options['connect_timeout'] > 0) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['connect_timeout']);
        }
        if (isset($options['allow_redirects'])) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $options['allow_redirects']);
        }
        if (isset($options['cookies'])) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $options['cookies']);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $options['cookies']);
        }
        if (isset($options['ssl_verify'])) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $options['ssl_verify']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $options['ssl_verify'] ? 2 : 0);
        }
        if (isset($options['proxy'])) {
            if (isset($options['proxy']['type'])) {
                switch ($options['proxy']['type']) {
                    case 'socks4':
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                        break;
                    case 'socks5':
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                        break;
                    case 'http':
                    case 'https':
                        break;
                }
            }
            curl_setopt($ch, CURLOPT_PROXY, $options['proxy']['hostname'] . ":" . $options['proxy']['port']);
            if (isset($options['proxy']['username'], $options['proxy']['password']) and $options['proxy']['username']) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $options['proxy']['username'] . ':' . $options['proxy']['password']);
            }
        }
        if ($outgoingIP = $request->getOutgoingIP()) {
            curl_setopt($ch, CURLOPT_INTERFACE, $outgoingIP);
        }
        $headers = array();
        foreach ($request->getHeaders() as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (isset($options['save_as'])) {
            $fh = fopen($options['save_as'], 'w');
            $waitForHeader = true;
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$waitForHeader, &$body, &$header, $fh, $options) {
                if ($waitForHeader) {
                    $body .= $data;
                    if (!isset($options['proxy']) and !isset($options['allow_redirects'])) {
                        if (strpos($body, "\r\n\r\n") !== false) {
                            $parts = $this->getParts($body);
                            $header .= $parts[0];
                            $body = $parts[1];
                            $waitForHeader = false;
                        }
                    }
                    if (strlen($body) > 10240) {
                        $waitForHeader = false;
                        $parts = $this->getParts($body);
                        $header .= $parts[0];
                        $body = $parts[1];
                    }
                    if (!$waitForHeader and $body) {
                        fwrite($fh, $body);
                        $body = '';
                    }
                } else {
                    return fwrite($fh, $data);
                }
                return strlen($data);
            });
        }

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($fh) {
            if (isset($options['save_as']) and $body) {
                if ($waitForHeader) {
                    $parts = $this->getParts($body);
                    $header .= $parts[0];
                    $body = $parts[1];
                    unset($parts);
                }
                fwrite($fh, $body);
                $body = '';
            }
            fclose($fh);
        }
        if (!isset($options['save_as'])) {
            list($header, $body) = $this->getParts($result);
        }
        $header = $this->decodeHeader($header);
        $response = new Response($info['http_code'], $header);
        $response->setPrimaryIP($info['primary_ip'] ? $info['primary_ip'] : null);
        if (isset($options['save_as'])) {
            $response->setFile($options['save_as']);
        } else {
            $response->setBody($body);
        }
        return $response;
    }

    private function getParts(string $result): array
    {
        if (strpos($result, "\r\n\r\n") === false and !preg_match("/^HTTP\/\d+\.\d+ \d+ .*/i", $result)) {
            return ['', $result];
        }
        $parts = explode("\r\n\r\n", $result, 2);
        $bodyParts = $this->getParts($parts[1]);
        if ($bodyParts[0]) {
            $parts = $bodyParts;
        }
        return $parts;
    }
    private function decodeHeader(string $header): array
    {
        $result = array();
        $lines = explode("\r\n", $header);
        $length = count($lines);
        for ($x = 1; $x < $length; $x++) {
            $line = explode(":", $lines[$x], 2);
            $result[$line[0]] = isset($line[1]) ? ltrim($line[1]) : '';
        }
        return $result;
    }
}
