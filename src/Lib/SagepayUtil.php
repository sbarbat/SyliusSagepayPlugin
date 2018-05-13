<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

use Sbarbat\SyliusSagepayPlugin\Lib\SagepayException;

class SagepayUtil
{
    /**
     * Encrypt a string ready to send to SagePay using encryption key.
     *
     * @param  string  $string  The unencrypyted string.
     * @param  string  $key     The encryption key.
     *
     * @return string The encrypted string.
     */
    static public function encrypt(array $data, string $key): string
    {
        $data = SagepayUtil::arrayToQueryString($data);
        
        return strtoupper("@" . bin2hex(openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $key)));
    }

    /**
     * Decode a returned string from SagePay.
     *
     * @param string $strIn         The encrypted String.
     * @param string $password      The encyption password used to encrypt the string.
     *
     * @return string The unecrypted string.
     * @throws SagepayApiException
     */
    static public function decrypt(string $str, string $key): array
    {
        $str = substr($str, 1);
        $str = pack('H*', $str);
        return self::queryStringToArray(openssl_decrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $key));
    }

    /**
     * Convert a data array to a query string ready to post.
     *
     * @param  array   $data        The data array.
     * @param  string  $delimeter   Delimiter used in query string
     * @param  boolean $urlencoded  If true encode the final query string
     *
     * @return string The array as a string.
     */
    static public function arrayToQueryString(array $data, $delimiter = '&', $urlencoded = false)
    {
        $queryString = '';
        $delimiterLength = strlen($delimiter);

        // Parse each value pairs and concate to query string
        foreach ($data as $name => $value)
        {   
            // Apply urlencode if it is required
            if ($urlencoded)
            {
                $value = urlencode($value);
            }
            $queryString .= $name . '=' . $value . $delimiter;
        }

        // remove the last delimiter
        return substr($queryString, 0, -1 * $delimiterLength);
    }

    static public function arrayToQueryStringRemovingSensitiveData(array $data,array $nonSensitiveDataKey, $delimiter = '&', $urlencoded = false)
    {
        $queryString = '';
        $delimiterLength = strlen($delimiter);

        // Parse each value pairs and concate to query string
        foreach ($data as $name => $value)
        {
           if (!in_array($name, $nonSensitiveDataKey)){
				$value=MASK_FOR_HIDDEN_FIELDS;
		   }
		   else if ($urlencoded){
				$value = urlencode($value);
		   }
           	// Apply urlencode if it is required
            	
           $queryString .= $name . '=' . $value . $delimiter;
        }

        // remove the last delimiter
        return substr($queryString, 0, -1 * $delimiterLength);
    }
    /**
     * Convert string to data array.
     *
     * @param string  $data       Query string
     * @param string  $delimeter  Delimiter used in query string
     *
     * @return array
     */
    static public function queryStringToArray($data, $delimeter = "&")
    {
        // Explode query by delimiter
        $pairs = explode($delimeter, $data);
        $queryArray = array();

        // Explode pairs by "="
        foreach ($pairs as $pair)
        {
            $keyValue = explode('=', $pair);

            // Use first value as key
            $key = array_shift($keyValue);

            // Implode others as value for $key
            $queryArray[$key] = implode('=', $keyValue);
        }
        return $queryArray;
    }

   static public function queryStringToArrayRemovingSensitiveData($data, $delimeter = "&", $nonSensitiveDataKey)
    {  
        // Explode query by delimiter
        $pairs = explode($delimeter, $data);
        $queryArray = array();

        // Explode pairs by "="
        foreach ($pairs as $pair)
        {
            $keyValue = explode('=', $pair);
            // Use first value as key
            $key = array_shift($keyValue);
            if (in_array($key, $nonSensitiveDataKey)){
			  $keyValue = explode('=', $pair);
			}
			else{
			  $keyValue = array(MASK_FOR_HIDDEN_FIELDS);
			}
		    // Implode others as value for $key
			$queryArray[$key] = implode('=', $keyValue);
    		
        }
        return $queryArray;
    }

    /**
     * Extract last 4 digits from card number;
     *
     * @param string $cardNr
     *
     * @return string
     */
    static public function getLast4Digits($cardNr)
    {
        // Apply RegExp to extract last 4 digits
        $matches = array();
        if (preg_match('/\d{4}$/', $cardNr, $matches))
        {
            return $matches[0];
        }
        return '';
    }

}
