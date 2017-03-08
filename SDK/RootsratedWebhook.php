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
	            $key = $sdk->getKey();
                if (!empty($key)) {
                    $hookName = $jsonHook['hook'];
                    $this->parseHook($jsonHook, $hookName, $posts, $sdk);
                    if (version_compare(phpversion(), '5.4.0', '>=')) {
                        http_response_code(200);
                    } else {
                        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                        header($protocol . ' 200 OK');
                    }
                    echo('{"message":"ok"}');
                    flush();
                    return true;
                } else {
	  	    echo 'FALSE';
                    return false;
                }
            }else {
	        echo 'FALSE';
                return false;
            }
        } else { //GET! WE don't care about it
            echo 'FALSE';
            return false;
        }
    }

    public function parseHook($jsonHook, $hookName, $posts, $sdk)
    {
        //$postId = $this->getPostIdFromHook($jsonHook['distribution']['id']);

        switch ($hookName) {
	        case "distribution_schedule" : $this->postScheduling($jsonHook, $posts,$sdk); break;
            case "distribution_go_live" : $this->postGoLive($jsonHook, $posts, $sdk); break;
            case "content_update" :  $this->postRevision($jsonHook, $posts, $sdk); break; /* changed from distribution_update to content_update */
            case "distribution_update" :  $this->postUpdate($jsonHook, $posts, $sdk); break; /* changed from distribution_update to content_update */
            case "distribution_revoke" : $this->postRevoke($jsonHook, $posts);break;
            case "service_cancel" : $posts->deactivationPlugin(); break;
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
        return $posts->postScheduling($distribution, $rrId);
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

        return $posts->postGoLive($distribution, $launchAt, $rrId);
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

    public function postRevoke($jsonHook, $posts)
    {
        $rrId = trim($jsonHook['distribution']['id']);

        return $posts->postRevoke($rrId);
    }

}