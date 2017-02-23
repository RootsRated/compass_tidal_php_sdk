<?php
class RootsRatedSDK {

    // protected fields
    protected $auth_key;
    protected $token;
    protected $apiURL = 'https://compass.rootsrated.com/tidal/v1_0/';
    protected $imageUploadPath;
    protected $key;
    protected $secret;
    protected $pluginActivatedFlag = false;

    // Getters and Setters
    public function getToken(){
        return $this->token;
    }

    public function setAuthKeyAndToken($authKey, $token) {
        $this->auth_key = $authKey;
        $this->token = $token;
    }

    public function getAuthKey(){
        return $this->auth_key;
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
        $this->key = $newKey;
        $this->secret = $newSecret;
    }

    public function getSecret(){
        return $this->secret;
    }

    public function getPluginActivatedFlag(){
        return $this->pluginActivatedFlag;
    }

    public function setPluginActivatedFlag(){
        if (!empty($this->key) && !empty($this->secret) && !empty($this->token)) {
            $this->pluginActivatedFlag = true;
        } else {
            $this->pluginActivatedFlag = false;
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
    public function errorPluginActivationGlobal()
    {
        $message = "Sorry, something was wrong. Please, try reactivating plugin";
        return $message;
    }

    public function errorPluginActivation()
    {
        $message = "Credentials invalid.";
        return $message;
    }

    public function messagePluginActivated()
    {
        $message = "Your plugin has been activated.";
        return $message;
    }

    // Set Configuration
    // TODO figure out variables needed, pass them in, and call setters in order
    public function setConfig(){

    }

    // Plugin
    // TODO once setConfig is created
    public function activationPlugin()
    {

    }

    public function deactivationPlugin()
    {

    }

    public  function uninstallPlugin()
    {
        $this->deactivationPlugin();
    }

    // Refactored Admin
    public function getContent($newKey, $newSecret, $authKey, $newToken){
        $this->setKeyAndSecret($newKey, $newSecret);
        $this->setAuthKeyAndToken($authKey, $newToken);

        $response = $this->getData('content');
        return $response;
    }

}
