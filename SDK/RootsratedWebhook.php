<?php
class RootsRatedWebhook{

    function getAllHeaders()
    {
        $headers = array();
        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }
        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }
        return $headers;
    }

    function executeHook($headers, $reqBody, $posts, $sdk)
    {
        $hookSignature = array_key_exists("X-Tidal-Signature",$headers)?$headers["X-Tidal-Signature"]:false;
        $hookName = array_key_exists("X-Tidal-Event",$headers)?$headers["X-Tidal-Event"]:false;

        if (strlen($hookSignature) > 0 && strlen($hookName) > 0){
            $jsonHook = $reqBody ? json_decode($reqBody, true) : '';
            if (is_array($jsonHook)) {
	   
                if ($sdk->isAuthenticated() ) {
                    $hookName = $jsonHook['hook'];
                    $result = $this->parseHook($jsonHook, $hookName, $posts, $sdk);
                    if($result === true) {
                         $this->HTTPStatus(200, ' 200 OK');
                        echo('{"message":"ok"}');
                        flush();
                        return true;
                    }else{

                        if(gettype($result) === 'string'){
                            echo('{"message":"ok"}');
                            $this->HTTPStatus(200, ' 200 OK');
                            return $result;
                        }
                        $this->HTTPStatus(401, ' 401 Invalid Hook Name');
                        return false;
                    }
                } else {
                    echo 'FALSE';
                    $this->HTTPStatus(401, ' 401 No Key and/or Secret');
                    return false;
                }
            }else {
	            echo 'FALSE';
                $this->HTTPStatus(500, ' 500 Failed');
                return false;
            }
        } else { //GET! WE don't care about it
            echo 'FALSE';
            $this->HTTPStatus(401, ' 401 Invalid Hook Signature');
            return false;
        }
    }

    public function parseHook($jsonHook, $hookName, $posts, $sdk)
    {

        switch ($hookName) {
	     case "distribution_schedule" : $this->postScheduling($jsonHook, $posts,$sdk); break;
            case "distribution_go_live" : $this->postGoLive($jsonHook, $posts, $sdk); break;
            case "content_update" :  $this->postRevision($jsonHook, $posts, $sdk); break;                   
            case "distribution_update" :  $this->postUpdate($jsonHook, $posts, $sdk); break; 
            case "distribution_revoke" : $this->postRevoke($jsonHook, $posts, $sdk);break;
            case "service_cancel" : $posts->deactivationPlugin(); break;
            case "service_phone_home" : $result = $this->servicePhoneHome($posts, $sdk); return $result; break;
            default : return false;
        }

        return true;
    }

    private function postScheduling($jsonHook, $posts,$sdk)
    {
        if(!array_key_exists('distribution', $jsonHook)) {
            return false;
        }
        $rrId = trim($jsonHook['distribution']['id']);

        $data = $sdk->getData('content/' . $rrId);
        if (!$data) {
            return 0;
        }

        $distribution = $data['response']['distribution'];
        $catName = $sdk->getCategoryName();
        $posttype = $sdk->getPostType();

        return $posts->postScheduling($distribution, $rrId, $catName, $posttype);
    }

    private function postGoLive($jsonHook, $posts, $sdk)
    {
        if(!array_key_exists('distribution', $jsonHook)) {
            return false;
        }

        $rrId = trim($jsonHook['distribution']['id']);

        if (empty($postId)) {
            $gd = $sdk;
            $data = $gd->getData('content/' . $rrId);
            if (!$data) {
                return 0;
            }
        }

        $distribution = $data['response']['distribution'];
        $launchAt = $jsonHook['distribution']['launch_at'];
        $catName = $sdk->getCategoryName();
        $posttype = $sdk->getPostType();

        return $posts->postGoLive($distribution, $launchAt, $rrId, $catName, $posttype);
        }

    public function postRevision($jsonHook, $posts, $sdk)
    {
        $data = $sdk;
        $tempPost = $data->getData('content/' . $jsonHook['distribution']['id']);
        if (!$tempPost) {
            return false;
        }

        $distribution = $tempPost['response']['distribution'];
        $scheduledAt = $distribution['distribution']['scheduled_at'];

        return $posts->postRevision($distribution, $scheduledAt);
    }

    public function postUpdate($jsonHook, $posts, $sdk)
    {
        $data = $sdk;
        $tempPost = $data->getData('content/' . $jsonHook['distribution']['id']);
        if (!$tempPost) {
            return false;
        }

        $distribution = $tempPost['response']['distribution'];
        $scheduledAt = $distribution['distribution']['scheduled_at'];

        return $posts->postUpdate($scheduledAt);
    }

    public function postRevoke($jsonHook, $posts, $sdk)
    {
        $rrId = trim($jsonHook['distribution']['id']);
        $posttype = $sdk->getPostType();
        
        return $posts->postRevoke($rrId, $posttype);
    }

    public function servicePhoneHome($options, $sdk){


        if(!$sdk->isAuthenticated()){ 
            return false;
        }

        $result = $this->phoneHome($options,$sdk);

        $method = '$this->phoneHome()';
        $this->sendRequest($method, $result , $sdk);

        return $result;
    }


    function sendRequest($methodName, $parameters, $sdk)
    {
        $url = $sdk->getPhoneHomeUrl() .$sdk->getToken().'/phone_home';


        $request = xmlrpc_encode_request($methodName, $parameters);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $results = curl_exec($ch);
        $results = xmlrpc_decode($results);
        curl_close($ch);
        return $results;
    }

    public function phoneHome($posts, $sdk)
    {

        $options = $posts->getInfo();

        $WPVersion = $options['db_version']; 
        $PHPVersion = phpversion();
        $URI = $options['siteurl'];
        $rootURL = $options['home'];
        $canCreate = $options['publish_posts']; 
        $canRevoke = $options['delete_published_posts'];
        $machineUser = $options['username_exists'];

        $parent = 0;
        $categoryPresent = category_exists($cat_name, $parent);
        $plugins = $options['plugins']; 
        $token = $sdk->getToken();

        $pluginsJSON = "";
        foreach ($plugins as $plugin){
            $version = $plugin['Version'];
            $name = $plugin['Name'];
            $pluginsJSON .= <<<PLUGIN
{
"name": ".$name.",
"version": ".$version."
}
PLUGIN;

        }


        $payload = <<<PAYLOAD
{
    "system_info":{
        "wp_version": "$WPVersion",
        "php_version": "$PHPVersion",
        "rootsrated_plugin_uri": "$URI",
        "wordpress_root_url": "$rootURL",
        "installed_plugins": [
        $pluginsJSON
        ]
    },
    "channel":{
    "token": "$token",
    "can_create_article": $canCreate,
    "can_revoke_article": $canRevoke,
    },
    "checks":{
    "machine_user_present": $machineUser,
     "default_category_present": $categoryPresent
    }
}
PAYLOAD;
        return $payload;
    }

    public function HTTPStatus($code, $message){
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            http_response_code($code);
        } else {
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $message);
        }
    }

}