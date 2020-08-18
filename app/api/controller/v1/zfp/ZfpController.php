<?php


namespace app\api\controller\v1\zfp;


use app\Request;
use app\services\zfp\ZfpServices;

/**
 * 支付派回调
 * Class ZfpController
 * @package app\api\controller\wechat
 */
class ZfpController
{
    protected $services = NUll;

    /**
     * AuthController constructor.
     * @param RoutineServices $services
     */
    public function __construct(ZfpServices $services)
    {
        $this->services = $services;
    }

    /**
     * 支付派支付回调
     */
    public function notify(Request $request)
    {
        $input = input();
        $this->services->notify($input);
    }

}
