<?php
class RootsRatedError {
    const globalError = "Sorry, something was wrong. Please, try reactivating plugin";
    const activationError = "Credentials invalid.";
    const activatedMessage = "Your plugin has been activated.";

    public function hasField($field)
    {
        if (!empty($field)) {
            return true;
        }
        return false;
    }

    public function isValidArray($data)
    {
        if (!is_array($data) || !array_key_exists('response', $data)) {
            return false;
        } else {
            return true;
        }
    }
}