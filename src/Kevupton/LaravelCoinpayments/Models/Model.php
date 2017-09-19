<?php namespace Kevupton\LaravelCoinpayments\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    /**
     * Defines the prefix for the table.
     * @param array $attr
     */
    public function __construct($attr = array()) {
        parent::__construct($attr);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return cp_table_prefix() . parent::getTable();
    }
}
