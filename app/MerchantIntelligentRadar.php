<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantIntelligentRadar extends Model
{
    protected $table = 'merchant_intelligent_radar';

    protected $fillable = ['id_name', 'id_no', 'trade_no', 'trans_id', 'code', 'desc', 'fee', 'versions', 'apply_score',
        'apply_credibility', 'query_org_count', 'query_finance_count', 'query_cash_count', 'query_sum_count',
        'latest_query_time', 'latest_one_month', 'latest_three_month', 'latest_six_month', 'loans_score',
        'loans_credibility', 'loans_count', 'loans_settle_count', 'loans_overdue_count', 'loans_org_count',
        'consfin_org_count', 'loans_cash_count', 'loans_latest_one_month', 'loans_latest_three_month',
        'loans_latest_six_month', 'history_suc_fee', 'history_fail_fee', 'latest_one_month_suc', 'latest_one_month_fail',
        'loans_long_time', 'loans_latest_time', 'loans_credit_limit', 'curr_loans_credibility', 'curr_loans_org_count',
        'loans_product_count', 'loans_max_limit', 'loans_avg_limit', 'consfin_credit_limit', 'consfin_credibility',
        'curr_consfin_org_count', 'consfin_product_count', 'consfin_max_limit', 'consfin_avg_limit'];
}