<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantMnoReport extends Model
{
    protected $table = 'merchant_mno_report';

    protected $fillable = ['name', 'certNo', 'mobile', 'gender', 'age', 'birthAddress', 'belongTo', 'highRiskLists', 'numberUsedLong',
        'emergencyContacts', 'partnerCount', 'idcCount', 'phoneCount', 'starnetCount', 'contactsSize',
        'exchangeCallMobileCount', 'exchangeCallMobileCountEvidence', 'contactsActiveDegree', 'notCallAndSmsDayCount',
        'notCallAndSmsDayCountEvidence', 'nightCallCount', 'nightCallCountEvidence', 'callSizeOver200Month',
        'callSizeOver500Month', 'callDurationLess1minSize', 'callDuration1to5minSize', 'callDuration5to10minSize',
        'singleCallingDurationMax', 'singleCalledDurationMax', 'mnoMonthCostInfos', 'mnoMonthUsedInfos',
        'mnoOneMonthCommonlyConnectMobiles', 'mnoCommonlyConnectMobiles', 'openTime', 'mnoType', 'allCallCountFrequencyEvidence',
        'passRealName', 'equalToPetitioner'];
}