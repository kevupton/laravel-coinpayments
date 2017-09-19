<?php

use Kevupton\LaravelCoinpayments\Models\Log;

define('COINPAYMENTS_CONFIG', 'coinpayments');
define('CP_TABLE_PREFIX', cp_prefix());
define('CP_LOG_LEVEL', cp_conf('log_level', Log::LEVEL_NONE));