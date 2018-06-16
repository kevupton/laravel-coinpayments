<?php

namespace Kevupton\LaravelCoinpayments\Validators;

use Kevupton\LaravelCoinpayments\Hashers\Sha3;

/**
 * Class EthereumValidator
 *
 * @package Kevupton\LaravelCoinpayments\Validators
 * @source https://stackoverflow.com/questions/44990408/how-to-validate-ethereum-addresses-in-php
 */
class EthereumValidator
{
    /**
     * @param $address
     * @return bool
     * @throws \Exception
     */
    public function isAddress($address)
    {
        // See: https://github.com/ethereum/web3.js/blob/7935e5f/lib/utils/utils.js#L415
        if ($this->matchesPattern($address)) {
            return $this->isAllSameCaps($address) ?: $this->isValidChecksum($address);
        }

        return false;
    }

    protected function matchesPattern($address)
    {
        return preg_match('/^(0x)?[0-9a-f]{40}$/i', $address);
    }

    protected function isAllSameCaps($address)
    {
        return preg_match('/^(0x)?[0-9a-f]{40}$/', $address) || preg_match('/^(0x)?[0-9A-F]{40}$/', $address);
    }

    /**
     * @param $address
     * @return bool
     * @throws \Exception
     */
    protected function isValidChecksum($address)
    {
        $address = str_replace('0x', '', $address);
        // See: https://github.com/ethereum/web3.js/blob/b794007/lib/utils/sha3.js#L35
        $hash = Sha3::hash(strtolower($address), 256);

        // See: https://github.com/web3j/web3j/pull/134/files#diff-db8702981afff54d3de6a913f13b7be4R42
        for ($i = 0; $i < 40; $i++ ) {
            if (ctype_alpha($address{$i})) {
                // Each uppercase letter should correlate with a first bit of 1 in the hash char with the same index,
                // and each lowercase letter with a 0 bit.
                $charInt = intval($hash{$i}, 16);

                if ((ctype_upper($address{$i}) && $charInt <= 7) || (ctype_lower($address{$i}) && $charInt > 7)) {
                    return false;
                }
            }
        }

        return true;
    }
}