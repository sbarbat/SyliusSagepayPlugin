<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

class SagepayUtil
{
    /**
     * Encrypt a string ready to send to SagePay using encryption key.
     *
     * @param string $key the encryption key
     *
     * @return string the encrypted string
     */
    public static function encrypt(array $data, string $key): string
    {
        $data = self::arrayToQueryString($data);

        return strtoupper('@'.bin2hex(openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $key)));
    }

    /**
     * Decode a returned string from SagePay.
     *
     * @throws SagepayApiException
     *
     * @return string the unecrypted string
     */
    public static function decrypt(string $str, string $key): array
    {
        $str = substr($str, 1);
        $str = pack('H*', $str);

        return self::queryStringToArray(openssl_decrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $key));
    }

    /**
     * Convert a data array to a query string ready to post.
     *
     * @param array $data       the data array
     * @param bool  $urlencoded If true encode the final query string
     * @param mixed $delimiter
     *
     * @return string the array as a string
     */
    public static function arrayToQueryString(array $data, $delimiter = '&', $urlencoded = false)
    {
        $queryString = '';
        $delimiterLength = strlen($delimiter);

        // Parse each value pairs and concate to query string
        foreach ($data as $name => $value) {
            // Apply urlencode if it is required
            if ($urlencoded) {
                $value = urlencode($value);
            }
            $queryString .= $name.'='.$value.$delimiter;
        }

        // remove the last delimiter
        return substr($queryString, 0, -1 * $delimiterLength);
    }

    public static function arrayToQueryStringRemovingSensitiveData(
        array $data,
        array $nonSensitiveDataKey,
        $delimiter = '&',
        $urlencoded = false
    )
    {
        $queryString = '';
        $delimiterLength = strlen($delimiter);

        // Parse each value pairs and concate to query string
        foreach ($data as $name => $value) {
            if (! in_array($name, $nonSensitiveDataKey, true)) {
                $value = MASK_FOR_HIDDEN_FIELDS;
            } elseif ($urlencoded) {
                $value = urlencode($value);
            }
            // Apply urlencode if it is required

            $queryString .= $name.'='.$value.$delimiter;
        }

        // remove the last delimiter
        return substr($queryString, 0, -1 * $delimiterLength);
    }

    /**
     * Convert string to data array.
     *
     * @param string $data      Query string
     * @param string $delimeter Delimiter used in query string
     *
     * @return array
     */
    public static function queryStringToArray($data, $delimeter = '&')
    {
        // Explode query by delimiter
        $pairs = explode($delimeter, $data);
        $queryArray = [];

        // Explode pairs by "="
        foreach ($pairs as $pair) {
            $keyValue = explode('=', $pair);

            // Use first value as key
            $key = array_shift($keyValue);

            // Implode others as value for $key
            $queryArray[$key] = implode('=', $keyValue);
        }

        return $queryArray;
    }

    public static function queryStringToArrayRemovingSensitiveData($data, $delimeter = '&', $nonSensitiveDataKey)
    {
        // Explode query by delimiter
        $pairs = explode($delimeter, $data);
        $queryArray = [];

        // Explode pairs by "="
        foreach ($pairs as $pair) {
            $keyValue = explode('=', $pair);
            // Use first value as key
            $key = array_shift($keyValue);
            if (in_array($key, $nonSensitiveDataKey, true)) {
                $keyValue = explode('=', $pair);
            } else {
                $keyValue = [MASK_FOR_HIDDEN_FIELDS];
            }
            // Implode others as value for $key
            $queryArray[$key] = implode('=', $keyValue);
        }

        return $queryArray;
    }

    /**
     * Extract last 4 digits from card number;.
     *
     * @param string $cardNr
     *
     * @return string
     */
    public static function getLast4Digits($cardNr)
    {
        // Apply RegExp to extract last 4 digits
        $matches = [];
        if (preg_match('/\d{4}$/', $cardNr, $matches)) {
            return $matches[0];
        }

        return '';
    }
}
