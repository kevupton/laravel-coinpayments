<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 19/09/2017
 * Time: 12:31 PM
 */

namespace Oasin\LaravelCoinpayments\Models;

/**
 * Class Ipn
 *
 * @package Oasin\LaravelCoinpayments\Models
 * @property mixed          id
 * @property mixed          ipn_version
 * @property mixed          ipn_id
 * @property mixed          ipn_mode
 * @property mixed          merchant
 * @property mixed          address
 * @property mixed          amount
 * @property mixed          amounti
 * @property mixed          currency
 * @property mixed          feei
 * @property mixed          dest_tag
 * @property mixed          confirms
 * @property mixed          ipn_type
 * @property mixed          txn_id
 * @property mixed          status
 * @property mixed          status_text
 * @property mixed          currency1
 * @property mixed          currency2
 * @property mixed          amount1
 * @property mixed          amount2
 * @property mixed          ref_id
 * @property mixed          fee
 * @property mixed          buyer_name
 * @property mixed          item_name
 * @property mixed          item_number
 * @property mixed          invoice
 * @property mixed          custom
 * @property mixed          send_tx
 * @property mixed          received_amount
 * @property mixed          received_confirms
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Ipn extends Model
{
    public $fillable = [
        'ipn_version', 'ipn_id', 'ipn_mode', 'merchant', 'ipn_type', 'txn_id', 'status', 'ref_id',
        'status_text', 'currency1', 'currency2', 'amount1', 'amount2', 'fee', 'buyer_name',
        'item_name', 'item_number', 'invoice', 'custom', 'send_tx', 'received_amount', 'received_confirms',
        'address', 'amount', 'amounti', 'currency', 'feei', 'dest_tag', 'confirms',
    ];

    public function isComplete ()
    {
        // If $order_status is >100 or is 2, return true
        return $this->status >= 100 || $this->status == 2;
    }

    public function isWithdrawal ()
    {
        return $this->ipn_type === 'withdrawal';
    }

    public function isApi ()
    {
        return $this->ipn_type === 'api';
    }

    public function isSimpleButton ()
    {
        return $this->ipn_type === 'simple';
    }

    public function isAdvancedButton ()
    {
        return $this->ipn_type === 'button';
    }
}