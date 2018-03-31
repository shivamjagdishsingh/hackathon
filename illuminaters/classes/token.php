<?php

class token{

    public static function generate()
    {
       return session::put(config::get('session/token_name'), md5(uniqid()));
    }

    public static function check($token)
    {
        $tokenName = config::get('session/token_name');
        // return $tokenName;
        if(session::exist($tokenName) && $token === session::get($tokenName))
        {
            session::delete($tokenName);
            return true;
        }
        return false;
    }

}