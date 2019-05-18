<?php
    function authenticateUser($tainted_username, $tainted_password)
    {
        $username = NULL;
        $password = NULL;

        if(validateUsernamePassword($tainted_username, $tainted_password)) {
            $username = $tainted_username;
            $password = $tainted_password;
        } else {
            return false;
        }

        return login($username, $password);
    }

    function validateUsernamePassword($tainted_username, $tainted_password)
    {
        if( (strlen($tainted_username) > 256 || strlen($tainted_password) > 256)
                && strlen($tainted_password) < 8) {
            return false;
        }

        $username = NULL;
        $password = NULL;

        if(preg_match("/^[A-Za-z0-9]*$/", $tainted_username)) {
            $username = $tainted_username;
            if(preg_match("/^[A-Za-z0-9@*#_]{8,}$/")) {
                $password = $tainted_password;
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    function login($username, $password)
    {

    }
?>