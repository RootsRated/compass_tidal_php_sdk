<?php

define('API_URL', 'https://compass.rootsrated.com/tidal/v1_0/');

#define('COMPASS_TOKEN', 'R2qQBRAQAGeTc7s7SfoKUS2U');
#define('COMPASS_KEY', 'LpzB0VMjC7ByXQd0z6v7qA');
#define('COMPASS_SECRET', 'w9YmFXOFqmp83hzA3GYR7w');

define('COMPASS_TOKEN', 'AvpKVo9DHpfFpzSH2mnHA8pB');
define('COMPASS_KEY', 'c9v0purpIGzaZODSc2WMCw');
define('COMPASS_SECRET', 'kXvP7ElIGxVY9osFPqyHRw');

define('TIDAL_COMMAND', 'content');


function doRequest() {
    $http = curl_init();

    $url = API_URL . COMPASS_TOKEN . "/" . TIDAL_COMMAND;
    $auth = base64_encode(COMPASS_KEY . ':' . COMPASS_SECRET);
    echo "curl -i -H 'Content-Type: application/json' -H 'Authorization: Basic " . $auth . "' " . $url . "\n";

    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic '. $auth
        )
    );
    echo "RootsRated Compass: options = " . preg_replace('/\s+/', ' ', print_r($options, 1)) . "\n";
    curl_setopt_array($http, $options);
    $data = curl_exec($http);
    $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
    echo "HTTP status: " . $http_status;
    if ($http_status >= 300) {
        error_log("RootsRated Compass: received status code " . $http_status . " for URL " . $url . " with authorization " . $auth . "\n");
    }
    curl_close($http);

    $data = json_decode($data, true);
    if (!(is_array($data) && array_key_exists('response', $data))) {
        error_log("RootsRated Compass: response is invalid (" . $data . ") for URL " . $url . " with authorization " . $auth . "\n");
        $data = null;
    }

    return $data;
}

echo json_encode(doRequest());

?>
