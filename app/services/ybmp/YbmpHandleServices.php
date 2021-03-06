<?php


namespace app\services\ybmp;

use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use app\dao\system\SystemBankDao;
use think\facade\Db;
use think\facade\Env;

class  YbmpHandleServices extends BaseServices
{
    protected $connect  = [];
    protected $db = null;
    public function __construct()
    {
        $this->db = Db::connect('ybmp');
    }

    /**
     * 获取电子券信息
     * @param $name
     * @param $mobile
     * @param $type
     * @return mixed
     */
    public function getElectronicVoucher($code)
    {
        $rs = ['code'=>0,'msg'=>'','info'=>[]];
        $evu = Db::connect('ybmp')->name('ybmp_electronic_voucher_use')->where('code',$code)->find();
        if(!$evu){
            $rs['code'] = 1;
            $rs['msg'] = '电子券不存在';
            return $rs;
        }
        if($evu['status']==1){
            $rs['code'] = 1;
            $rs['msg'] = '该电子券已使用';
            return $rs;
        }
        if(!$evu['user_id']){
            $rs['code'] = 1;
            $rs['msg'] = '该券尚不可用';
            return $rs;
        }
        if($evu['begin_time']>time() || $evu['end_time']<time()){
            $rs['code'] = 1;
            $rs['msg'] = '该电子券不在使用期或已过期';
            return $rs;
        }
        $yev = Db::connect('ybmp')->name('ybmp_electronic_voucher')->where('id',$evu['e_id'])->find();
        if(!$yev){
            $rs['code'] = 1;
            $rs['msg'] = '电子券不存在';
            return $rs;
        }
        $rs['info'] = [
            'price'=>$yev['price'],
            'sub_price'=>$yev['sub_price'],
            'id'=>$yev['id']
        ];
        return $rs;
    }

    /**
     * 更新电子券状态
     * @param $name
     * @param $mobile
     * @param $type
     * @return mixed
     */
    public function updateElectronicVoucher($code)
    {
        $update = [
            'status'=>1,
            'use_time'=>time()
        ];
        return Db::connect('ybmp')->name('ybmp_electronic_voucher_use')->where('code',$code)->update($update);
    }

    /**
     * 电子券相应的用户更新状态
     * @param $name
     * @param $mobile
     * @param $type
     * @return mixed
     */
    public function sendCommission($code)
    {
        $evu = Db::connect('ybmp')->name('ybmp_electronic_voucher_use')->where('code',$code)->find();
        if(!$evu){
            return false;
        }
        $ev = Db::connect('ybmp')->name('ybmp_electronic_voucher')->where('id',$evu['e_id'])->find();
        if(!$ev){
            return false;
        }
        if(!$ev['profit']){
            return true;
        }
        if($evu['type']==2){
            return true;
        }
        $this->db->startTrans();
        $user_share_money = [
            'mch_id'  =>  $evu['mch_id'],
            'order_id'=>  $evu['id'],
            'user_id' => $evu['send_id'],
            'money'=>$ev['profit'],
            'create_time'=>time(),
            'type'=>3
        ];

        $res1 = Db::connect('ybmp')->name('ybmp_user_share_money')->insert($user_share_money);
        $res2 = Db::connect('ybmp')->name('ybmp_user')->where('uid',$evu['send_id'])
                        ->inc('total_price',$ev['profit'])
                        ->inc('price',$ev['profit'])
                        ->update();
        if($res1 && $res2){
            $this->db->commit();
            return true;
        }else{
            $this->db->rollback();
            return false;
        }
    }

    /**
     * 判断是否是指定商品使用该电子券
     * @param $name
     * @param $mobile
     * @param $type
     * @return mixed
     */
    public function isProductUse($code,$product_id)
    {
        $is_use=1000;
        $evu = Db::connect('ybmp')->name('ybmp_electronic_voucher_use')->where('code',$code)->find();
        if(!$evu){
            //'电子券不存在';
            $is_use=1001;
        }
        if($evu['status']==1){
            //'该电子券已使用';
            $is_use=1002;
        }
        if(!$evu['user_id']){
            //'该券尚不可用';
            $is_use=1003;
        }
        if($evu['begin_time']>time() || $evu['end_time']<time()){
            //'该电子券不在使用期或已过期';
            $is_use=1004;
        }
        if($evu['product_ids'] && !in_array($product_id,explode(',',$evu['product_ids']))){
            //'该券指定了商品使用，购买的商品不满足条件';
            $is_use=1005;
        }
        $yev = Db::connect('ybmp')->name('ybmp_electronic_voucher')->where('id',$evu['e_id'])->find();
        if(!$yev){
            //'电子券不存在';
            $is_use=1006;
        }

        return $is_use;
    }
}
