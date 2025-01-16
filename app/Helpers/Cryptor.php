<?php

namespace App\Helpers;

class Cryptor
{
    static function encrypt($string)
    {
        $key = hash('sha256', env('ID_SECRET_KEY'));
        $iv = substr(hash('sha256', env('ID_SECRET_IV')), 0, 16);

        $output = openssl_encrypt($string, env('ID_ENCRYPT_METHOD'), $key, 0, $iv);

        $output = base64_encode($output);

        $output = str_replace('=','', $output);

        return $output;
    }

    static function decrypt($string)
    {
        $key = hash('sha256', env('ID_SECRET_KEY'));
        $iv = substr(hash('sha256', env('ID_SECRET_IV')), 0, 16);

        return openssl_decrypt(base64_decode($string), env('ID_ENCRYPT_METHOD'), $key, 0, $iv);
    }

    static function walletEncrypt($id, $key)
    {
        if (is_null($id) || is_null($key)) return false;

        return self::encrypt($id.'-'.$key);
    }

    static function walletDecrypt($string)
    {
        try {
            $split = explode('-', $string);
            if (count($split) != 2) return false;

            $prefix = $split[0];

            $decrypted = self::decrypt($split[1]);

            $split = explode('-', $decrypted);
            if (count($split) != 2) return false;

            return [
                'prefix' => $prefix,
                'id' => $split[0],
                'key' => $split[1],
            ];
        } catch (\Throwable $th) {
            return false;
        }
    }

    static function encryptArray($array)
    {
        $encryptedArray = [];
        foreach($array as $item) {
            array_push($encryptedArray, self::encrypt($item));
        }

        return $encryptedArray;
    }

    static function decryptArray($array)
    {
        $decryptedArray = [];
        foreach($array as $item) {
            array_push($decryptedArray, self::decrypt($item));
        }

        return $decryptedArray;
    }

    static function encryptObject($object)
    {
        $encryptedObject = [];
        foreach($object as $key => $value) {
            $encryptedObject[$key] = self::encrypt($value);
        }

        return $encryptedObject;
    }

    static function decryptObject($object)
    {
        $decryptedObject = [];
        foreach($object as $key => $value) {
            $decryptedObject[$key] = self::decrypt($value);
        }

        return $decryptedObject;
    }

    static function encryptObjectArray($objectArray)
    {
        $encryptedObjectArray = [];
        foreach($objectArray as $object) {
            array_push($encryptedObjectArray, self::encryptObject($object));
        }

        return $encryptedObjectArray;
    }

    static function decryptObjectArray($objectArray)
    {
        $decryptedObjectArray = [];
        foreach($objectArray as $object) {
            array_push($decryptedObjectArray, self::decryptObject($object));
        }

        return $decryptedObjectArray;
    }
}
