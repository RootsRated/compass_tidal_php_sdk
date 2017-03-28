<?php

require_once __DIR__ . '/RootsratedError.php';


class RootsRatedSDK {

    // protected fields
    protected $token;
    protected $apiURL = 'https://compass.rootsrated.com/tidal/v1_0/';
    protected $imageUploadPath;
    protected $key;
    protected $secret;
    protected $error;
    protected $phoneHomeUrl;
    protected $categoryName = 'RootsRated';
    protected $postType = 'Post';


    public function __construct(){
        $this->error = new  RootsRatedError();
    }

    public function setConfig($configJson){
        $rootsrated = json_decode($configJson, true);
        $this->setImageUploadPath($rootsrated['rootsrated']['image_upload_path']);
        $this->setKeyAndSecret($rootsrated['rootsrated']['rootsrated_key'], $rootsrated['rootsrated']['rootsrated_secret']);
        $this->setToken($rootsrated['rootsrated']['rootsrated_token']);
        $this->setPhoneHomeUrl($rootsrated['rootsrated']['phone_home_url']);
        $this->setCategoryName($rootsrated['rootsrated']['category']);


    }

    // Getters and Setters
    public function getToken(){
        return $this->token;
    }

    public function setToken($token) {
        if($this->error->hasField($token)){
            $this->token = $token;
        }
    }

    public function getApiURL(){
        return $this->apiURL;
    }

    public function getImageUploadPath(){
        return $this->imageUploadPath;
    }

    public function setImageUploadPath($imagePath){
        $this->imageUploadPath = $imagePath;
    }

    public function getKey(){
        return base64_encode($this->key);
    }

    public function setKeyAndSecret($newKey, $newSecret){
    if($this->error->hasField($newKey) && $this->error->hasField($newSecret)){
            $this->key = $newKey;
            $this->secret = $newSecret;
        }
    }

    public function isAuthenticated(){
        if($this->error->hasField($this->key) && $this->error->hasField($this->secret) && $this->error->hasField($this->token)){
            return true;
        }

        return false;
    }

    public function getCategoryName(){
        return $this->categoryName;
    }

    public function setCategoryName($categoryName) {
        if($this->error->hasField($categoryName)){
            $this->categoryName = $categoryName;
        }
    }

    public function getPostType(){
        return $this->postType;
    }

    public function setPostType($postType) {
        if($this->error->hasField($postType)){
            $this->postType = $postType;
        }
    }

    public function getPhoneHomeUrl(){
        return $this->phoneHomeUrl;
    }

    public function setPhoneHomeUrl($phoneHomeUrl) {
        if($this->error->hasField($phoneHomeUrl)){
            $this->phoneHomeUrl = $phoneHomeUrl;
        }
    }

    // Get Data
    public function getData($command)
    {

        if (!($ch = curl_init())) {
            return false;
        }

        curl_setopt($ch, CURLOPT_URL, $this->getApiURL() . $this->getToken() . '/' . $command);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Basic '. base64_encode($this->key . ':' . $this->secret),
            $this->getApiURL() . $this->getToken() . '/' . $command
        ));
        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data, true);

        if(!$this->error->isValidArray($data))
	{
      	    $data = null;
        }
	
        return $data;
    }

    public function siteJavascript()
    {
        $hook = <<<HOOKFUNCTION
            (function(r,oo,t,s,ra,te,d){if(!r[ra]){(r.GlobalRootsRatedNamespace=r.GlobalRootsRatedNamespace||[]).push(ra);
            r[ra]=function(){(r[ra].q=r[ra].q||[]).push(arguments)};r[ra].q=r[ra].q||[];te=oo.createElement(t);
            d=oo.getElementsByTagName(t)[0];te.async=1;te.src=s;d.parentNode.insertBefore(te,d)
            }}(window,document,"script","https://static.rootsrated.com/rootsrated.min.js","rr"));
            rr(\'config\', \'channelToken\',' . $this->token . ')' ;
HOOKFUNCTION;

        return $hook;
    }

}
