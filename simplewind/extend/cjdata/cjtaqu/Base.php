<?php
namespace cjdata\cjtaqu;

use think\Db;
use think\Log;
use think\Exception;

/**
 * 采集他趣数据基础类
 */
class Base
{
    public $distinctRequestId = '';
    public $headerParam = [];

    public function __construct()
    {

    }

    /**
     * 刷新请求头
     */
    public function freshHeaderParams()
    {
        $id='';
        for ($i = 1; $i <= 32; $i++) {
            $id .= chr(rand(97, 122));
        }

        $time1=time();
        $time=$this->getMillisecond();


        $tkey="KFDM";//这里取固定值即可

        $xkey="MJKL";
        $hkey="NGHI";
        $ikey="ODEF";
        $okey="PABC";
        $vaild=md5("c5905376760d7a2cca10d5f684348f5c".$time.$xkey.$hkey).md5("c5905376760d7a2cca10d5f684348f5c".$id.$xkey.$hkey);

        $header = array(
            "xkey:$xkey",
            "Host:ubd-cn.jiaoliuqu.com",
            "AV:6439",
            "TO:$id",
            //"DU:4.4.4_SAMSUNG-SM-N900A_2018040517",
            "DU:6.0.1_LEX720_2017051711",
            "appid:1001",
            "hkey:$hkey",
            "TP:$time1",
            "AC:wifi",
            "PL:android",
            "vaild:$vaild",
            "ikey:$ikey",
            "time:$time",
            "CH:xingjiabi",
            "SX:2",
            "tkey:$tkey",
            "AP:1",
            "okey:$okey",
            "X-Tingyun-Processed:true",
            "Connection:Keep-Alive",
            "User-Agent:Taqu/6439/android/$id"
        );

        $this->distinctRequestId = $id;
        $this->headerParam = $header;
    }

    /**
     * get请求
     * @param $url
     * @param $header
     * @return mixed
     */
    public function getdata($url,$header)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        $data = curl_exec($curl);
        curl_close($curl);
        //$data=str_replace(array('他趣',''),array('这',''),$data);
        return $data;
    }

    /**
     * post请求
     * @param $url
     * @param $data
     * @param $header
     * @return mixed
     */
    public function postdata($url,$data,$header)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
        curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
        //  curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($login));//要提交的信息
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//要提交的信息
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * 生成13位时间戳
     * @return string
     */
    public function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return $t2  .  ceil( ($t1 * 1000) );
    }

    /**
     * 连接采集他趣数据库
     * @return \think\db\Connection
     * @throws Exception
     */
    public function connectCjtaqu()
    {
        try {
            $cjtaquDbConfig = \think\Config::get('option.cjtaqu_db');

            $taquConn = Db::connect($cjtaquDbConfig);
        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据库连接失败：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('采集他趣数据库连接失败：' . $e->getMessage());
        }

        return $taquConn;
    }
}