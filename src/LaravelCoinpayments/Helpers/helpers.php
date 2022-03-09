<?php

use Oasin\LaravelCoinpayments\Models\Log;

if (!function_exists('cp_table_prefix')) {
    /**
     * Gets the prefix of the database table.
     *
     * @return string
     */
    function cp_table_prefix ()
    {
        return cp_conf('database_prefix');
    }
}


if (!function_exists('cp_conf')) {
    /**
     * Gets a config value from the config file.
     *
     * @param string $prop the key property
     * @param string $default the default response
     *
     * @return mixed
     */
    function cp_conf ($prop, $default = '')
    {
        return config(COINPAYMENTS_CONFIG . '.' . $prop, $default);
    }
}

if (!function_exists('cp_log_level')) {
    /**
     * Gets the specified log level for the config.
     *
     * @return number
     */
    function cp_log_level ()
    {
        return cp_conf('log_level', Log::LEVEL_NONE);
    }
}

if (!function_exists('cp_log')) {
    /**
     * Logs the request in the database
     *
     * @param mixed $content the xml data received
     * @param null|string $type
     * @param int $level
     * @return Log|null
     * @internal param array $sent the data sent
     */
    function cp_log ($content, $type = null, $level = Log::LEVEL_ALL)
    {
        if (cp_log_level() < $level) return null;

        return Log::create([
            'log' => json_encode($content),
            'type' => $type
        ]);
    }
}


if (!function_exists('validate_eth_address')) {
    /**
     * @param $address
     * @return bool
     * @throws \Oasin\LaravelCoinpayments\Exceptions\InvalidHashException
     */
    function validate_eth_address ($address)
    {
        try {
            if (is_eth_address($address)) {
                return true;
            }
        } catch (Exception $e) {
        }

        throw new \Oasin\LaravelCoinpayments\Exceptions\InvalidHashException('Invalid ETH address');
    }
}

if (!function_exists('is_eth_address')) {
    /**
     * Checks if the given string is an address
     *
     * @method is_eth_address
     * @param $address string the given HEX adress
     * @return bool
     * @throws Exception
     */
    function is_eth_address ($address)
    {
        $validator = new \Oasin\LaravelCoinpayments\Validators\EthereumValidator();
        return $validator->isAddress($address);
    }
}


if (!function_exists('is_checksum_address')) {
    /**
     * Checks if the given string is a checksummed address
     *
     * @method isChecksumAddress
     * @param string $address the given HEX adress
     * @return bool
     */
    function is_checksum_address ($address)
    {
        // Check each case
        $address = str_replace('0x', '', $address);
        $addressHash = hash('sha384', strtolower($address), false);
        for ($i = 0; $i < 40; $i++) {
            // the nth letter should be uppercase if the nth digit of casemap is 1
            if ((intval($addressHash[$i], 16) > 7 && strtoupper($address[$i]) !== $address[$i]) || (intval($addressHash[$i], 16) <= 7 && strtolower($address[$i]) !== $address[$i])) {
                return false;
            }
        }
        return true;
    }
}