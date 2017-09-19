<?php
class RootsRatedWebhook
{

    function getAllHeaders()
    {
        $headers = array();
        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );
        foreach ($_SERVER as $key => $value) 
        {
            if (substr($key, 0, 5) === 'HTTP_') 
            {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) 
                {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } 
            elseif (isset($copy_server[$key])) 
            {
                $headers[$copy_server[$key]] = $value;
            }
        }
        if (!isset($headers['Authorization'])) 
        {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) 
            {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } 
            elseif (isset($_SERVER['PHP_AUTH_USER'])) 
            {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } 
            elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) 
            {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }
        return $headers;
    }

    function executeHook($headers, $reqBody, $posts, $sdk)
    {
        $hookSignature = array_key_exists("X-Tidal-Signature", $headers) ?$headers["X-Tidal-Signature"] : false;
        $hookName = array_key_exists("X-Tidal-Event", $headers) ? $headers["X-Tidal-Event"] : false;

        if ( $sdk->validateHookSignature($reqBody, $hookSignature) && strlen($hookName) > 0)
        {
            $jsonHook = $reqBody ? json_decode($reqBody, true) : '';
            if (is_array($jsonHook)) 
            {
	   
                if ($sdk->isAuthenticated() ) 
                {
                    $hookName = $jsonHook['hook'];
                    $result = $this->parseHook($jsonHook, $hookName, $posts, $sdk);
                    if($result === true) 
                    {
                         $this->HTTPStatus(200, ' 200 OK');
                        echo('{"message":"ok"}');
                        flush();
                        return true;
                    }
                    else
                    {

                        if(gettype($result) === 'string')
                        {
                            echo($result);
                            $this->HTTPStatus(200, '200 OK');
                            return $result;
                        }
                        $this->HTTPStatus(401, '401 Invalid Hook Name');
                        return false;
                    }
                } 
                else 
                {
                    echo 'FALSE';
                    $this->HTTPStatus(401, '401 No Key and/or Secret');
                    return false;
                }
            }
            else 
            {
	         echo 'FALSE';
                $this->HTTPStatus(500, '500 Failed');
                return false;
            }
        } 
        else 
        { 
            echo 'FALSE';
            $this->HTTPStatus(401, '401 Invalid Hook Signature');
            return false;
        }
    }

    public function parseHook($jsonHook, $hookName, $posts, $sdk)
    {
        switch ($hookName) 
        {
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

    private function postScheduling($jsonHook, $posts, $sdk)
    {
        if(!array_key_exists('distribution', $jsonHook)) 
        {
            return false;
        }
        $rrId = trim($jsonHook['distribution']['id']);

        $data = $sdk->getData('content/' . $rrId);
        if (!$data) 
        {
            return false;
        }

        $distribution = $data['response']['distribution'];
        $catName = $sdk->getCategoryName();
        $postType = $sdk->getPostType();

        return $posts->postScheduling($distribution, $rrId, $catName, $postType);
    }

    private function postGoLive($jsonHook, $posts, $sdk)
    {
        if(!array_key_exists('distribution', $jsonHook)) 
        {
            return false;
        }

        $rrId = trim($jsonHook['distribution']['id']);

        if (empty($postId)) 
        {
    
            $data = $sdk->getData('content/' . $rrId);
            if (!$data) 
            {
                return false;
            }
        }

        $distribution = $data['response']['distribution'];
        $launchAt = $jsonHook['distribution']['launch_at'];
        $catName = $sdk->getCategoryName();
        $postType = $sdk->getPostType();

        return $posts->postGoLive($distribution, $launchAt, $rrId, $catName, $postType);
        }

    public function postRevision($jsonHook, $posts, $sdk)
    {
        $data = $sdk;
        $rrId = trim($jsonHook['distribution']['id']);
        $tempPost = $data->getData('content/' . $rrId);
        if (!$tempPost) 
        {
            return false;
        }

        $distribution = $tempPost['response']['distribution'];
        $scheduledAt = $distribution['distribution']['scheduled_at'];
        $postType = $sdk->getPostType();

        return $posts->postRevision($distribution, $rrId, $postType, $scheduledAt);
    }

    public function postUpdate($jsonHook, $posts, $sdk)
    {
        $data = $sdk;
        $rrId = trim($jsonHook['distribution']['id']);

        $tempPost = $data->getData('content/' . $rrId);
        if (!$tempPost) 
        {
            return false;
        }

        $distribution = $tempPost['response']['distribution'];
        $scheduledAt = $distribution['distribution']['scheduled_at'];
        $postType = $sdk->getPostType();

        return $posts->postUpdate($distribution, $rrId, $postType, $scheduledAt);
    }

    public function postRevoke($jsonHook, $posts, $sdk)
    {
        $rrId = trim($jsonHook['distribution']['id']);
        $postType = $sdk->getPostType();
        
        return $posts->postRevoke($rrId, $postType);
    }

    public function servicePhoneHome($options, $sdk)
    {

        if(!$sdk->isAuthenticated())
        { 
            return false;
        }

        $request = $this->phoneHome($options,$sdk);

        $url = $sdk->getPhoneHomeUrl() . $sdk->getToken() . '/phone_home';

        $ch = curl_init();

        $options = array(CURLOPT_POSTFIELDS => $request,
                         CURLOPT_URL => $url,
                         CURLOPT_RETURNTRANSFER => 1,
                         CURLOPT_HTTPHEADER => array(
                             'Content-Type: application/json',
                             'Authorization: Basic '. $sdk->getBasicAuth()
                    ));

        curl_setopt_array($ch, $options);

        $results = curl_exec($ch);
        $results = json_decode($results, true);
        $success = $results["success"];
        curl_close($ch);
        
        if ($success){
            return $request;
        }
        else{
            return $results;
        }
    }


    function sendRequest($methodName, $parameters, $sdk)
    {
        $url = $sdk->getPhoneHomeUrl() . $sdk->getToken() . '/phone_home';

        $request = xmlrpc_encode_request($methodName, $parameters);
        $ch = curl_init();

        $options = array(CURLOPT_POSTFIELDS => $request,
                         CURLOPT_URL => $url,
                         CURLOPT_RETURNTRANSFER => 1,
                         CURLOPT_HTTPHEADER => array(
                             'Content-Type: application/json',
                             'Authorization: Basic '. $sdk->getBasicAuth()
                    ));

        curl_setopt_array($ch, $options);

        $results = curl_exec($ch);
        $results = xmlrpc_decode($results);
        curl_close($ch);
        return $results;
    }

    public function phoneHome($posts, $sdk)
    {

        $options = $posts->getInfo();

        $platformVersion = $options['db_version']; 
        $PHPVersion = phpversion();
        $URI = $options['siteurl'];
        $rootURL = $options['home'];
        $canCreate = $options['publish_posts']; 
        $canRevoke = $options['delete_published_posts'];
        $machineUser = $options['username_exists'];

        $parent = 0;
        $categoryPresent = $options['category_exists'];
        $plugins = $options['plugins']; 
        $token = $sdk->getToken();

        $pluginsJSON = array();

        foreach ($plugins as $plugin)
        {
            $item = array();
            $item['name'] = $plugin['Name'];
            $item['version'] = $plugin['Version'];
            $pluginsJSON[] = $item;
        }


        $system_info = array();
        $system_info['platform_version'] = $platformVersion;
        $system_info['php_version'] = $PHPVersion;
        $system_info['rootsrated_plugin_uri'] = $URI;
        $system_info['root_url'] = $rootURL;
        $system_info['installed_plugins'] = $pluginsJSON;

        $channel = array();
        $channel['token'] = $token;
        $channel['can_create_article'] = $canCreate;
        $channel['can_revoke_article'] = $canRevoke;


        $checks = array();
        $checks['machine_user_present'] = $machineUser;
        $checks['default_category_present'] = $categoryPresent;


        $payload = array();
        $payload['system_info'] = $system_info;
        $payload['channel'] = $channel;
        $payload['checks'] = $checks;

        return json_encode($payload);

    }

    public function HTTPStatus($code, $message)
    {
        if (version_compare(phpversion(), '5.4.0', '>=')) 
        {
            http_response_code($code);
        } 
        else 
        {
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $message);
        }
    }

}