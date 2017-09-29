<?php
/**
 * Advanced print_r
 *
 * @param $var
 * @param bool $return
 * @return string
 */
function var_debug($var, $return = false)
{
    // get print_r output
    $output = print_r($var, true);

    // if were are not on console, wrap the output in <pre> tags
    if (php_sapi_name() != 'cli') $output = "<pre>$output</pre>";

    // return or print output
    if ($return) return $output;
    print $output;
}

/**
 * Redirect to an URI
 *
 * @param $uri
 * @param int $httpResponseCode
 */
function redirect($uri, $httpResponseCode = 301)
{
    header('Location: ' . $uri, null, $httpResponseCode);
    exit;
}

/**
 * Print cron message
 *
 * @param $message
 * @param bool $highlight
 */
function cronout($message, $highlight = false)
{
    echo date('d.m.Y H:i:s') . " ";
    if ($highlight) echo ' ====== ';
    echo $message;
    if ($highlight) echo ' ======';
    echo "\n";
}

/**
 * Send downnload header
 *
 * @param $filename
 * @param string $mimetype
 * @param bool $noCache
 */
function downloadHeader($filename, $mimetype = 'application/octet-stream', $noCache = true)
{
    header('Content-type: ' . $mimetype);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    if ($noCache)
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    }
}

/**
 * Print a string with following linebreak
 *
 * @param $str
 * @return string
 */
function println($str)
{
    return $str . "\n";
}

/**
 * log an HTTP request to a text file
 */
function logHttpRequest($logGet = true, $logPost = true, $logFiles = true, $logSession = false)
{
    if (($logGet && $_GET) || ($logPost && $_POST) || ($logFiles && $_FILES) || $logSession && $_SESSION)
    {
        $data = array();
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['time'] = date('Y-m-d H:i:s');
        if ($logGet && $_GET) $data['get'] = json_encode($_GET);
        if ($logPost && $_POST) $data['post'] = json_encode($_POST);
        if ($logFiles && $_FILES) $data['files'] = json_encode($_FILES);
        if ($logSession && $_SESSION)
        {
            $data['session_id'] = session_id();
            $data['session'] = json_encode($_SESSION);
        }
        file_put_contents('logs' . DIRECTORY_SEPARATOR . date('Y-m-d-H') . '.txt', json_encode($data) . "\n", FILE_APPEND);
    }
}

/**
 * Detect the current environment by the hostname
 *
 * @example detectEnvironment(array('dev' => 'dev-server', 'live' => 'www1'));
 *
 * @param array $values
 * @return int|string
 */
function detectEnvironment($values = array())
{
    $hostname = strtolower(gethostname());
    foreach ($values as $envId => $envHostname)
    {
        if (strpos($hostname, $envHostname) !== false)
            return $envId;
    }
}

/**
 * Send HTTP auth header with username/password promot.
 *
 * Returns TRUE if user entered the correct credentials, otherwise FALSE.
 *
 * @example if (httpAuth('john.doe', 'god123') == false) echo 'Permission denied';
 *
 * @param $username
 * @param $password
 * @param string $realm
 * @return bool
 */
function httpAuth($username, $password, $realm = 'Restricted Area')
{
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']))
    {
        header('WWW-Authenticate: Basic realm="' . $realm . '"');
        header('HTTP/1.0 401 Unauthorized');
    }
    else
    {
        if ($username == $_SERVER['PHP_AUTH_USER'] && $password == $_SERVER['PHP_AUTH_PW']) return true;
    }
    return false;
}