<?php

namespace WHMCS\Module\Registrar\Jeyserver\Http;

use TypeError;

class Client
{
    private static $defaultOptions = array(
        'base_uri' => null,
        'allow_redirects' => true,
        'auth' => null,
        'body' => null,
        'cookies' => true,
        'connect_timeout' => 0,
        'debug' => false,
        'delay' => 0,
        'form_params' => null,
        'headers' => array(),
        'http_errors' => true,
        'json' => null,
        'multipart' => null,
        'proxy' => null,
        'query' => null,
        'ssl_verify' => true,
        'timeout' => 0,
        'save_as' => null,
        'outgoing_ip' => null,
    );

    protected array $options = array();

    public function __construct(array $options = array())
    {
        $this->options = array_replace_recursive(self::$defaultOptions, $options);
    }

    public function request(string $method, string $URI, array $options = array()): Response
    {
        $thisOptions = array_replace($this->options, $options);

        if ($thisOptions['auth']) {
            if (!isset($thisOptions['headers']['authorization'])) {
                if (is_array($thisOptions['auth'])) {
                    if (isset($thisOptions['auth']['username'])) {
                        if (isset($thisOptions['auth']['password'])) {
                            $thisOptions['headers']['authorization'] = 'Basic ' . base64_encode($thisOptions['auth']['username'] . ':' . $thisOptions['auth']['password']);
                        }
                    }
                } else {
                    $thisOptions['headers']['authorization'] = $thisOptions['auth'];
                }
            }
        }
        if ($thisOptions['json']) {
            $thisOptions['headers']['content-type'] = 'application/json; charset=UTF-8';
            if (!$thisOptions['body']) {
                $thisOptions['body'] = json_encode($thisOptions['json'], JSON_UNESCAPED_UNICODE);
            }
        }
        if ($thisOptions['form_params']) {
            $thisOptions['headers']['content-type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            if (!$thisOptions['body']) {
                $thisOptions['body'] = http_build_query($thisOptions['form_params']);
            }
        }
        if ($thisOptions['multipart']) {
            $thisOptions['headers']['content-type'] = 'multipart/form-data; charset=UTF-8';
            if (!$thisOptions['body']) {
                $thisOptions['body'] = $thisOptions['multipart'];
            }
        }
        if (isset($thisOptions['proxy'])) {
            if (is_string($thisOptions['proxy'])) {
                $proxy = parse_url($thisOptions['proxy']);
                if ($proxy === false) {
                    throw new TypeError("cannot parse proxy");
                }
                if (!isset($proxy['host'])) {
                    throw new TypeError("host is not present in proxy url");
                }
                if (!isset($proxy['port'])) {
                    throw new TypeError("port is not present in proxy url");
                }
                $proxyAsArray = [
                    'type' => $proxy['scheme'] ?? "http",
                    'hostname' => $proxy['host'],
                    'port' => $proxy['port'],
                ];
                $thisOptions['proxy'] = $proxyAsArray;
            }
            if (is_array($thisOptions['proxy'])) {
                if (!isset($thisOptions['proxy']['type']) or !is_string($thisOptions['proxy']['type']) or !in_array($thisOptions['proxy']['type'], ['http', 'https', 'socks4', 'socks5'])) {
                    throw new TypeError("proxy type is invalid");
                }
                if (!isset($thisOptions['proxy']['hostname']) or !is_string($thisOptions['proxy']['hostname'])) {
                    throw new TypeError("proxy hostname is invalid");
                }
                if (!isset($thisOptions['proxy']['port']) or !is_numeric($thisOptions['proxy']['port']) or $thisOptions['proxy']['port'] < 0 or $thisOptions['proxy']['port'] > 65535) {
                    throw new TypeError("proxy port is invalid");
                }
            } else {
                throw new TypeError("proxy passed to " . __NAMESPACE__ . "\\" . __CLASS__ . "::" . __METHOD__ . "() must be of the type array");
            }
        }

        if (preg_match("/^[a-z]+\:\/\//i", $URI)) {
            $url = $URI;
        } else {
            $url = $thisOptions['base_uri'] . $URI;
        }
        $url_parse = parse_url($url);
        if (!isset($url_parse['path'])) {
            $url_parse['path'] = '';
        }
        $request = new Request($url_parse['host'], $url_parse['path']);
        if (isset($url_parse['scheme'])) {
            $request->setScheme($url_parse['scheme']);
        }
        if (isset($url_parse['port'])) {
            $request->setPort($url_parse['port']);
        } elseif (isset($url_parse['scheme'])) {
            if ($url_parse['scheme'] == 'https') {
                $request->setPort(443);
            }
        }
        $request->setMethod($method);
        if (isset($url_parse['query']) and $url_parse['query']) {
            parse_str($url_parse['query'], $query);
            if (!is_array($thisOptions['query'])) {
                $thisOptions['query'] = [];
            }
            $thisOptions['query'] = array_replace_recursive($query, $thisOptions['query']);
        }
        if ($thisOptions['query']) {
            $request->setQuery($thisOptions['query']);
        }
        $request->setBody($thisOptions['body']);
        if ($thisOptions['delay'] > 0) {
            usleep($thisOptions['delay']);
        }
        if (is_array($thisOptions['headers'])) {
            $request->setHeaders($thisOptions['headers']);
        }
        if ($thisOptions['proxy']) {
            $request->setProxy($thisOptions['proxy']);
        }
        if (isset($thisOptions['save_as'])) {
            $request->saveAs($thisOptions['save_as']);
        }
        if (isset($thisOptions['outgoing_ip'])) {
            $request->setOutgoingIP($thisOptions['outgoing_ip']);
        }

        file_put_contents("/tmp/whmcs-jeyserver-registrar-client.log", print_r($request, true), FILE_APPEND);
        $response = $this->fire($request, $thisOptions);
        file_put_contents("/tmp/whmcs-jeyserver-registrar-client.log", print_r($response, true) . "\n\n" . str_repeat('-', 50) . "\n\n", FILE_APPEND);



        $status = $response->getStatusCode();
        if ($status >= 400 and $status < 500) {
            throw new Exceptions\ClientException($request, $response);
        } elseif ($status >= 500 and $status < 600) {
            throw new Exceptions\ServerException($request, $response);
        }
        return $response;
    }

    public function get(string $URI, array $options = array()): Response
    {
        return $this->request('GET', $URI, $options);
    }

    public function post(string $URI, array $options = array()): Response
    {
        return $this->request('POST', $URI, $options);
    }

    public function fire(Request $request, array $options): Response
    {
        $handler = new Curl();
        return $handler->fire($request, $options);
    }
}
