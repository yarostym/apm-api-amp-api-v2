<?php
namespace Apm;

use \Psr\Http\Message\ResponseInterface;
use \GuzzleHttp\Exception\RequestException;


class Api {
    private $client = null;
    private static $httpAuthUser = null;
    private static $httpAuthPass = null;
    public static $baseUrl = 'https://api.zerass.com';

    function __construct($httpAuthUser = null, $httpAuthPass = null) {
        $this->client = $this->init($httpAuthUser, $httpAuthPass);
    }

    static public function init($httpAuthUser = null, $httpAuthPass = null) {
        if (empty($httpAuthUser)) {
            if (function_exists('config') && !empty(config('apm.api.http_auth.user'))) {
                $httpAuthUser = config('apm.api.http_auth.user');
            } elseif(!function_exists('config')) {
               die(' config not function_exists');
            } elseif(empty(config('apm.api.http_auth.user'))) {
               die(' config(\'apm.api.http_auth.user\') is empty');
            } else {
                die('UNKNOWN ERROR config(\'apm.api.http_auth.user\')');
            }
        }
        if (empty($httpAuthPass)) {
            if (function_exists('config') && !empty(config('apm.api.http_auth.password'))) {
                $httpAuthPass = config('apm.api.http_auth.password');
            } elseif(!function_exists('config')) {
                die(' config not function_exists');
            } elseif(empty(config('apm.api.http_auth.password'))) {
                die(' config(\'apm.api.http_auth.password\') is empty');
            } else {
                die('UNKNOWN ERROR config(\'apm.api.http_auth.password\')');
            }
        }
        if (function_exists('config') && !empty(config('apm.api.base_url'))) {
            static::$baseUrl = config('apm.api.base_url');
        }



        return new \GuzzleHttp\Client([
            'base_uri' => static::$baseUrl,
            'auth' => [$httpAuthUser, $httpAuthPass],
            'timeout'  => 0,
        ]);
    }

    public function api($postData, $debug = false) {

        $postData = ['form_params' => ['requests' => $postData]];
        $userAccessToken = null;
        if (ISSET($_SESSION['user_access_token'])) {
            $userAccessToken = $_SESSION['user_access_token'];
        } elseif (function_exists('session')) {
            $userAccessToken = session('user_access_token');
        }
        if (!empty($userAccessToken)) {
            $postData['form_params']['user_access_token'] = $userAccessToken;
        }
        $response = $this->client->post('/', $postData);
        return self::returnResult($postData, $response, $debug);
    }

    static public function __apiStatic($httpAuthUser, $httpAuthPass, &$postData, $debug = false) {
        $client = self::init($httpAuthUser, $httpAuthPass);
        $postData = ['form_params' => ['requests' => [0 => $postData]]];
        $userAccessToken = null;
        if (ISSET($_SESSION['user_access_token'])) {
            $userAccessToken = $_SESSION['user_access_token'];
        } elseif (function_exists('session')) {
            $userAccessToken = session('user_access_token');
        }
        if (!empty($userAccessToken)) {
            $postData['form_params']['user_access_token'] = $userAccessToken;
        }

        $response = $client->post('/', $postData);
        return self::returnResult($postData, $response, $debug);
    }
    public static function apiStatic(&$postData, $debug = false) {
        return self::__apiStatic(self::$httpAuthUser, self::$httpAuthPass, $postData, $debug);
    }

    static public function __apiOrder($httpAuthUser, $httpAuthPass, &$postData, $debug = false) {
        $client = self::init($httpAuthUser, $httpAuthPass);
        $postData = ['form_params' => ['requests' => $postData]];
        $userAccessToken = null;
        if (ISSET($_SESSION['user_access_token'])) {
            $userAccessToken = $_SESSION['user_access_token'];
        } elseif (function_exists('session')) {
            $userAccessToken = session('user_access_token');
        }
        if (!empty($userAccessToken)) {
            $postData['form_params']['user_access_token'] = $userAccessToken;
        }

        $response = $client->post('/', $postData);
        return self::returnResult($postData, $response, $debug);
    }
    public static function apiOrder(&$postData, $debug = false) {
        return self::__apiOrder(self::$httpAuthUser, self::$httpAuthPass, $postData, $debug);
    }

    public static function returnResult(&$postData, $response, $debug = false) {
        $decodeResult = json_decode($response->getBody()->getContents(), true);
        if ($debug !== -1) {
            if ($debug || (  (ISSET($decodeResult['error'])) && $decodeResult['error'] == 1) || $decodeResult == false) {
                ?>
                <hr>
                Posted data: <?=print_r($postData, true);?>
                <h5>ANSWER:</h5>
                <?php if ($decodeResult) {
                        if (is_array($decodeResult) && (function_exists('dd'))) {
                            if ($debug == 2) {
                                dd($decodeResult);
                            } else {
                                echo '<xmp style="background-color:orange;">';
                                print_r($decodeResult);
                                echo '</xmp>';
                            }
                        } else {
                            echo '<xmp style="background-color:orange;">';
                                print_r($decodeResult);
                            echo '</xmp>';
                        }
                    } else {
                        echo 'BaseUrl' . static::$baseUrl . "\r\n";
                        echo 'HTTP CODE:' . $response->getStatusCode() . "\r\n";
                        echo 'Headers::' . print_r($response->getHeaders(),true);
                        echo '<xmp style="background-color:orange;">response:' . $response->getBody()->getContents() . '</xmp>';

                    }?>
                    <hr>
                <?php
            }
        }
        $postData = [];
        return $decodeResult;
    }
}
/*
 * //ENV
 * APM_API_HTTP_AUTH_USER = ""
 * APM_API_HTTP_AUTH_PASSWORD = ""
 *
$debug = false;
$multiApiCallAr = [
        'var1' => ['domain', 'object', 'method', []]
    ];
$resultMultiApiCall = empty($multiApiCallAr)?[]:\Apm\Api::apiStatic($multiApiCallAr, $debug);
print_r($result['var1']);
 *
 *  OR
 *
 *
 * $multiApiCallAr = [
        'var1' => ['domain', 'object', 'method', []]
    ];
 * $resultMultiApiCall = empty($multiApiCallAr)?[]:\Apm\Api::apiStatic(config('apm.api.http_auth.user'), config('apm.api.http_auth.password'), $multiApiCallAr);
print_r($resultMultiApiCall['var1']);
 *
 *  OR
 *
 *
    $apmApi =  new \Apm\Api(config('apm.api.http_auth.user'), config('apm.api.http_auth.password'));
    $multiApiCallAr = [
        'var1' => ['domain', 'object', 'method', []]
    ];
    $resultMultiApiCall = $apmApi->api($multiApiCallAr, 0);
print_r($result['var1']);

 */