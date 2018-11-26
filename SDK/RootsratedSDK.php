<?php

class RootsRatedSDK {

    // protected fields
    protected $token;
    protected $apiURL = 'https://app.getmatcha.com/tidal/v1_0/';
    protected $imageUploadPath;
    protected $key;
    protected $secret;
    protected $phoneHomeUrl;
    protected $categoryName = 'RootsRated';
    protected $postType = 'Post';
    protected $applicationPath;
    private $headerSnippetAdded = false;

    public function __construct()
    {
        $configJson = file_get_contents(__DIR__ .'/config.json');
        $this->setConfig($configJson);
    }

    public function setConfig($configJson)
    {

        $rootsrated = $configJson;
        if (is_string($configJson))
        {
            $rootsrated = json_decode($configJson, true);
        }

        if(array_key_exists('image_upload_path',$rootsrated['rootsrated']))
        {
            $this->setImageUploadPath($rootsrated['rootsrated']['image_upload_path']);
        }

        if(array_key_exists('rootsrated_key',$rootsrated['rootsrated']) &&
            array_key_exists('rootsrated_secret',$rootsrated['rootsrated']))
        {
            $this->setKeyAndSecret($rootsrated['rootsrated']['rootsrated_key'], $rootsrated['rootsrated']['rootsrated_secret']);
        }

        if(array_key_exists('rootsrated_token',$rootsrated['rootsrated']))
        {
            $this->setToken($rootsrated['rootsrated']['rootsrated_token']);
        }

        if(array_key_exists('phone_home_url',$rootsrated['rootsrated']))
        {
            $this->setPhoneHomeUrl($rootsrated['rootsrated']['phone_home_url']);
        }

        if(array_key_exists('category',$rootsrated['rootsrated']))
        {
            $this->setCategoryName($rootsrated['rootsrated']['category']);
        }

        if(array_key_exists('posttype',$rootsrated['rootsrated']))
        {
            $this->setPostType($rootsrated['rootsrated']['posttype']);
        }

        if(array_key_exists('application_path',$rootsrated['rootsrated']))
        {
            $this->setApplicationPath($rootsrated['rootsrated']['application_path']);
        }
    }

    // Getters and Setters
    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        if($this->hasField($token))
        {
            $this->token = $token;
        }
    }

    public function getApiURL()
    {
        return $this->apiURL;
    }

    public function getImageUploadPath()
    {
        return $this->imageUploadPath;
    }

    public function setImageUploadPath($imagePath)
    {
        $this->imageUploadPath = $imagePath;
    }

    public function getKey()
    {
        return base64_encode($this->key);
    }

    public function checkConfig() {
        return (bool)($this->key && $this->secret && $this->token);
    }

    public function setKeyAndSecret($newKey, $newSecret)
    {
        if($this->hasField($newKey) && $this->hasField($newSecret))
        {
            $this->key = $newKey;
            $this->secret = $newSecret;
        }
    }

    public function getBasicAuth()
    {
        return base64_encode($this->key . ':' . $this->secret);
    }


    public function validateHookSignature($hookPayload, $requestSignature)
    {
        $generatedSignature = hash_hmac('sha256', $hookPayload, $this->secret);
        return $generatedSignature == $requestSignature;
    }

    public function isAuthenticated(){
        return ($this->hasField($this->key) && $this->hasField($this->secret) && $this->hasField($this->token));

    }

    public function getCategoryName()
    {
        return $this->categoryName;
    }

    public function setCategoryName($categoryName)
    {
        if($this->hasField($categoryName))
        {
            $this->categoryName = $categoryName;
        }
    }

    public function getPostType()
    {
        return $this->postType;
    }

    public function setPostType($postType)
    {
        if($this->hasField($postType))
        {
            $this->postType = $postType;
        }
    }

    public function getApplicationPath()
    {
        return $this->applicationPath;
    }

    public function setApplicationPath($appPath)
    {
        if($this->hasField($appPath))
        {
            $this->applicationPath = $appPath;
        }
    }

    public function getPhoneHomeUrl()
    {
        return $this->phoneHomeUrl;
    }

    public function setPhoneHomeUrl($phoneHomeUrl)
    {
        if($this->hasField($phoneHomeUrl))
        {
            $this->phoneHomeUrl = $phoneHomeUrl;
        }
    }

    private function setHeaderSnippetAdded($hasBeenSet)
    {
        if($this->hasField($hasBeenSet))
        {
            $this->headerSnippetAdded = $hasBeenSet;
        }
    }

    // Get Data
    public function getData($command) {
        $url = $this->getApiURL() . $this->getToken() . '/' . $command;
        $auth = $this->getBasicAuth();
        $options = array(
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic '. $auth
            )
        );

        $http = curl_init();
        if (!$http) {
            error_log("Matcha: curl_init failed\n");
            return false;
        }
        curl_setopt_array($http, $options);
        $response = curl_exec($http);
        $status_code = curl_getinfo($http, CURLINFO_HTTP_CODE);
        $error_detail = curl_error($http);
        curl_close($http);

        $data = json_decode($response, true);
        if (!$this->isValidArray($data)) {
            error_log("Matcha: getData failed for URL " . $url . "; response=\"" . $response . "\"; response code=" . $status_code . "; curl_error=" . $error_detail . "\n");
            return false;
        }
        return $data;
    }

    public function siteJavascript()
    {
        if($this->token == '')
        {
            return '';
        }

        if($this->headerSnippetAdded == true)
        {
            return '';
        }

        $hook = <<<HOOKFUNCTION
         <script>
            (function(r,oo,t,s,ra,te,d){if(!r[ra]){(r.GlobalRootsRatedNamespace=r.GlobalRootsRatedNamespace||[]).push(ra);
            r[ra]=function(){(r[ra].q=r[ra].q||[]).push(arguments)};r[ra].q=r[ra].q||[];te=oo.createElement(t);
            d=oo.getElementsByTagName(t)[0];te.async=1;te.src=s;d.parentNode.insertBefore(te,d)
            }}(window,document,"script","https://static.getmatcha.com/rootsrated.min.js","rr"));
            rr('config', 'channelToken', '$this->token');
         </script>
HOOKFUNCTION;

        $this->setHeaderSnippetAdded(true);
        return $hook;
    }


    public function hasField($field)
    {
        return !empty($field);
    }

    public function isValidArray($data)
    {
        if (is_array($data) && array_key_exists('response', $data))
        {
            return true;
        }
        return false;
    }

}
