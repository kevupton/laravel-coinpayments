<?php

use Kevupton\LaravelCoinpayments\Models\Log;

if (!function_exists('cp_prefix')) {
    /**
     * Gets the prefix of the database table.
     *
     * @return string
     */
    function cp_prefix() {
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
    function cp_conf($prop, $default = '') {
        return config(COINPAYMENTS_CONFIG . '.' . $prop, $default);
    }
}


if (!function_exists('cp_log')) {
    /**
     * Logs the request in the database
     *
     * @param string $content the xml data received
     * @param array $sent the data sent
     * @return Log
     */
    function cp_log($content, array $sent = null) {
        return Log::create([
            'sent' => json_encode(array_except($sent, \Kevupton\MerchantWarrior\Models\Log::DO_NOT_LOG)),
            'content' => $content
        ]);
    }
}