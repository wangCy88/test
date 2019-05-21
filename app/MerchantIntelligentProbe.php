<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantIntelligentProbe extends Model
{
    protected $table = 'merchant_intelligent_probe';

    protected $fillable = ['id_name', 'id_no', 'trade_no', 'trans_id', 'code', 'desc', 'fee', 'versions', 'result_code',
        'max_overdue_amt', 'max_overdue_days', 'latest_overdue_time', 'currently_overdue', 'currently_performance',
        'acc_exc', 'acc_sleep'];
}
