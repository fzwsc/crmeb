<?php
/**
 * @author: zhypy<214681832@qq.com>
 * @day: 2020/7/9
 */
declare (strict_types=1);

namespace app\services\zfp;

use app\services\BaseServices;

use app\services\bank\ZhifpServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderSuccessServices;
use app\services\ybmp\YbmpHandleServices;
use think\exception\ValidateException;

use think\facade\Config;

/**
 *
 * Class RoutineServices
 * @package app\services\wechat
 */
class  ZfpServices extends BaseServices
{

    /**
     * RoutineServices constructor.
     * @param WechatUserDao $dao
     */
    public function __construct()
    {

    }
    /**
     * 支付派支付回调
     */
    public function notify($input)
    {
        /** @var ZhifpServices $zhifpServices */
        $zhifpServices = app()->make(ZhifpServices::class);
        if(!$zhifpServices->zfp_check_sign($input)){
            throw new ValidateException('支付单号签名验证失败');
        }

        /** @var YbmpHandleServices $ybmpHandleServices */
        $ybmpHandleServices = app()->make(YbmpHandleServices::class);

        $storeOrderServices = app()->make(StoreOrderServices::class);
        $orderInfo = $storeOrderServices->getOne(['order_id' => $input['orderno']]);
        if (!$orderInfo || !isset($orderInfo['paid'])) {
            throw new ValidateException('支付订单不存在');
        }
        $orderInfo = $orderInfo->toArray();

        $this->transaction(function () use ($ybmpHandleServices, $orderInfo) {

            /** @var StoreOrderSuccessServices $storeOrderSuccessServices */
            $res = $storeOrderSuccessServices->paySuccess($orderInfo,'zfp');//余额支付成功
            if (!$res) {
                throw new ValidateException('支付失败!');
            }
            if($orderInfo['electronic_money']){
                $res1 = $ybmpHandleServices->sendCommission($orderInfo['electronic_code']);
                if (!$res1) {
                    throw new ValidateException('支付失败!');
                }
                throw new ValidateException('支付失败!');
            }
        });
        exit('success');
    }


}
