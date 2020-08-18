<?php


namespace app\services\ybmp;

use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use app\dao\system\SystemBankDao;
use think\Db;
use think\facade\Env;

class YbmpHandleServices extends BaseServices
{
    protected $connect  = [];
    protected $db = null;
    public function __construct()
    {
        $this->connect = [
            // 数据库类型
            'type'            => Env::get('database_ybmp.type', 'mysql'),
            // 服务器地址
            'hostname'        => Env::get('database_ybmp.hostname', '172.18.1.159'),
            // 数据库名
            'database'        => Env::get('database_ybmp.database', 'zxsc_shop_dev'),
            // 用户名
            'username'        => Env::get('database_ybmp.username', 'root'),
            // 密码
            'password'        => Env::get('database_ybmp.password', ''),
            // 端口
            'hostport'        => Env::get('database_ybmp.hostport', '3306'),
            // 连接dsn
            'dsn'             => '',
            // 数据库连接参数
            'params'          => [],
            // 数据库编码默认采用utf8
            'charset'         => Env::get('database_ybmp.charset', 'utf8'),
            // 数据库表前缀
            'prefix'          => Env::get('database_ybmp.prefix', 'ims_'),
            // 数据库调试模式
            'debug'           => Env::get('database_ybmp.debug', true),
        ];
        $this->db = Db::connect($this->connect);
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
        $evu = $this->db->name('ybmp_electronic_voucher_use')->where('code',$code)->find();
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
        if($evu['begin_time']>time() || $evu['end_time']<time()){
            $rs['code'] = 1;
            $rs['msg'] = '该电子券不在使用期或已过期';
            return $rs;
        }
        $yev = $this->db->name('ybmp_electronic_voucher')->where('id',$evu['e_id'])->find();
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
        return $this->db->name('ybmp_electronic_voucher_use')->where('code',$code)->update($update);
    }
}
