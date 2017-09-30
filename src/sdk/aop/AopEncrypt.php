<?php

namespace Cncal\Alipay\Sdk\Aop;

class AopEncrypt
{
    /**
     * Encrypt function
     * @param string $str
     * @return string
     */
    public static function encrypt($str, $secret_key)
    {
        $secret_key = base64_decode($secret_key);
        $str = trim($str);
        $str = self::addPKCS7Padding($str);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secret_key, $str, MCRYPT_MODE_CBC);

        return base64_encode($encrypt_str);
    }

    /**
     * Decrypt function
     * @param string $str
     * @return string
     */
    public static function decrypt($str, $secret_key)
    {
        $str = base64_decode($str);
        $secret_key = base64_decode($secret_key);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secret_key, $str, MCRYPT_MODE_CBC);
        $encrypt_str = trim($encrypt_str);
        $encrypt_str = self::stripPKSC7Padding($encrypt_str);

        return $encrypt_str;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    private static function addPKCS7Padding($source)
    {
        $source = trim($source);

        /**
         * mcrypt_get_block_size (string $cipher, string $mode)
         * 针对 libmcrypt 2.4.x 或 2.5.x
         */
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }

        return $source;
    }

    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    private static function stripPKSC7Padding($source)
    {
        $source = trim($source);
        $char = substr($source, -1);
        $num = ord($char);
        if ($num == 62) {
            return $source;
        }
        $source = substr($source, 0, -$num);

        return $source;
    }
}