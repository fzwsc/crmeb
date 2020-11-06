<?php


namespace app\services\bank;

use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use app\dao\system\SystemBankDao;

class ZhifpServices extends BaseServices
{
    protected $appid  = '';
    protected $appsecret = '';
    protected $host = '';
    public function __construct(SystemBankDao $dao)
    {
//        $this->appid = 'e4da3b7fbbce2345d7772b0674a318d5';
//        $this->appsecret = '8c81f14df60460609c596450babcb0b997c9907b';
        $this->appid = 'c81e728d9d4c2f636f067f89cc14862c';
        $this->appsecret = '7c56232549ee2750791a1d1f802f3652fd3b1338';
        $this->host = 'http://pay.fzwsc.com';
        $this->dao = $dao;
    }

    /**
     * 商户开户
     * @param $name
     * @param $mobile
     * @param $type
     * @return mixed
     */
    public function merchantOpen($name,$mobile,$type)
    {
        $url = $this->host.'/merchant/open';
        $r = random(20);
        $data = [
            'appid'=>$this->appid,
            'name'=>$name,
            'mobile'=>$mobile,
            'type'=>$type,
            'r'=>$r
        ];
        $sign = md5($this->appsecret.$this->appid.$mobile.$name.$r.$type.$this->appsecret);
        $data['sign'] = $sign;
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 商户信息
     * @param $uid
     * @return mixed
     */
    public function merchantInfo($uid,$token)
    {
        $url = $this->host.'/merchant/info';
        $r = random(20);
        $data = [
            'appid'=>$this->appid,
            'uid'=>$uid,
            'r'=>$r,
        ];

        $sign = md5($this->appsecret.$this->appid.$r.$token.$uid.$this->appsecret);
        $data['sign'] = $sign;
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 发起支付
     * @param $data
     * @return mixed
     */
    public function zfpai($data)
    {
        $url = $this->host.'/zfpai';
        $sign = make_sign($data);
        $data['sign'] = $sign;
        unset($data['token']);
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 用户提现信息
     * @param $data
     * @return mixed
     */
    public function cashinfo($data)
    {
        $url = $this->host.'/merchant/cashinfo';
        $r = random(20);
        $data['r'] = $r;
        $sign = make_sign($data);
        $data['sign'] = $sign;
        unset($data['token']);
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 商户确认提现
     * @param $data
     * @return mixed
     */
    public function cashwithdraw($data)
    {
        $url = $this->host.'/merchant/cashwithdraw';
        $r = random(20);
        $data['r'] = $r;
        $sign = make_sign($data);
        $data['sign'] = $sign;
        unset($data['token']);
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 商户提现查询
     * @param $data 里保存 uid / token
     * @return mixed
     */
    public function cashquery($data)
    {
        $url = $this->host.'/merchant/cashquery';
        $r = random(20);
        $data['r'] = $r;
        $sign = make_sign($data);
        $data['sign'] = $sign;
        unset($data['token']);
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }
    /**
     * 订单退款
     * @param $data 里保存 uid/orderno/desc
     * @return mixed
     */
    public function payrefund($data)
    {
        $url = $this->host.'/zfpai/payrefund';
        $r = random(20);
        $data['r'] = $r;
        $sign = make_sign($data);
        $data['sign'] = $sign;
        unset($data['token']);
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 订单退款查询
     * @param $data 里保存 uid/orderno
     * @return mixed
     */
    public function payrefundquery($data)
    {
        $url = $this->host.'/zfpai/payrefundquery';
        $r = random(20);
        $data['r'] = $r;
        $sign = make_sign($data);
        $data['sign'] = $sign;
        unset($data['token']);
        $result = curl_request($url,$data);
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 获取账号信息
     * @return int
     */
    public function getBankInfo()
    {
        return $this->dao->getBank();
    }


    public function zfp_check_sign($input)
    {
        $bankInfo = $this->dao->getBank();
        $data_zfp = [
            'trade_no'=>$input['trade_no'],
            'transaction_no'=>$input['transaction_no'],
            'price'=>$input['price'] ?? '',
            'realprice'=>$input['realprice'] ?? '',
            'orderno'=>$input['orderno'] ?? '',
            'orderuid'=>$input['orderuid'] ?? '',
            'attach'=>$input['attach'] ?? '',
            'token'=>$bankInfo['token']
        ];
        return $this->check_sign($data_zfp,$input['sign']);

    }

    public function check_sign($data, $sign)
    {
        $tmpStr = $this->sign_string($data);
        if (md5($tmpStr) == $sign) {
            return true;
        } else {
            return false;
        }
    }

    public function sign_string($data)
    {
        $tmpArr = [];
        foreach ($data as $k => $v) {
            if ($k == 'sign') {
                continue;
            }
            array_push($tmpArr, $k);
        }
        sort($tmpArr, SORT_STRING);
        $tmpStr = '';
        foreach ($tmpArr as $k => $v) {
            $tmpStr .= $data[$v];
        }
        return $tmpStr;
    }

}
