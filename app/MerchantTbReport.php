<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantTbReport extends Model
{
    protected $table = 'merchant_tb_report';

    protected $fillable = ['name', 'certNo', 'mobile', 'gender', 'age', 'birthAddress', 'passRealName', 'equalToPetitioner',
        'taoScore', 'vipLevel', 'huabeiTotal', 'overPartnerPercent', 'huabeiTotalAmount', 'alipayAmount', 'alipayYeb',
        'petitionerAddrChangeTimes', 'petitionerCityChangeTimes', 'friendsCircle', 'oneYearConsumptionTimes',
        'oneYearConsumptionCost', 'avgConsumptionCost', 'oneYearPetitionerConsumptionTimes', 'oneYearPetitionerConsumptionCost',
        'avgPetitionerConsumptionCost', 'oneYearHuabeiConsumptionTimes', 'oneYearHuabeiConsumptionCost', 'avgHuabeiConsumptionCost',
        'lastMonthUseHuabeiInfo', 'lastTimeUseHuabei', 'lastConsumptionTime', 'rechargeTimes', 'rechargeCost', 'mostCommonlyRechargeMobile',
        'shoppingCartQuantity', 'shoppingCartTotalAmount', 'shoppingCartLastJoinedTime', 'lastMonthFootmarkSize', 'lastFootmarkTime',
        'longestDaysNotUseTaobao', 'commonlyUsedAddresss'];
}