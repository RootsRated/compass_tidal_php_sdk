<?php
class RootsRatedSDK {

    // protected fields
    protected $token;
    protected $apiURL = 'https://compass.rootsrated.com/tidal/v1_0/';
    protected $imageUploadPath;
    protected $key;
    protected $secret;
    protected $pluginActivatedFlag = false;

    public function setConfig($configJson){
        $rootsrated = json_decode($configJson, true);
        $this->setImageUploadPath($rootsrated['rootsrated']['image_upload_path']);
        $this->setKeyAndSecret($rootsrated['rootsrated']['rootsrated_key'], $rootsrated['rootsrated']['rootsrated_secret']);
        $this->setToken($rootsrated['rootsrated']['rootsrated_token']);

    }

    // Getters and Setters
    public function getToken(){
        return $this->token;
    }

    public function setToken($token) {
        if(!empty($token)) {
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
        return $this->key;
    }

    public function setKeyAndSecret($newKey, $newSecret){
        if(!empty($newKey) && !empty($newSecret)) {
            $this->key = $newKey;
            $this->secret = $newSecret;
        }
    }

    public function getSecret(){
        return $this->secret;
    }

    public function getActivatedFlag(){
        if (!empty($this->key) && !empty($this->secret) && !empty($this->token)) {
            return true;
        }

        return false;
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
            'Authorization: Basic '. base64_encode($this->getKey() . ':' . $this->getSecret()),
            $this->getApiURL() . $this->getToken() . '/' . $command
        ));
        
        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data, true);

        if (!is_array($data) || !array_key_exists('response', $data)) {
            return false;
        }

		
        return $data;

    }

    public function getAllContent() {
        return $this->getData('content');
    }

    // Error messages
    public function errorActivationGlobal()
    {
        $message = "Sorry, something was wrong. Please, try reactivating plugin";
        return $message;
    }

    public function errorActivation()
    {
        $message = "Credentials invalid.";
        return $message;
    }

    public function messageActivated()
    {
        $message = "Your plugin has been activated.";
        return $message;
    }

    public function getHookCallbackJS()
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
