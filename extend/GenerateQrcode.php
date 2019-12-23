<?php
/**
 * Created by PhpStorm.
 * User: liwqbj
 * Date: 2019/7/8
 * Time: 18:06
 */

class GenerateQrcode
{
    protected $appid;
    protected $secret;

    function __construct()
    {
        $this->appid = 'wx2c7b9ef875979308';
        $this->secret = 'cd1dd3a420bbbb622b83b801ae689be6';

        //$this->appid = 'wxf4f5feddb6911d1a';
        //$this->secret = 'c7ddd0a6196ec7576bcb14bd316206f7';
    }

    private function send_post($url, $post_data,$method='POST') {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => $method, //or GET
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    private function qrcodeAccessToken()
    {
        $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->secret;
        $getArr = array();
        $tokenArr = json_decode($this->send_post($tokenUrl, $getArr, "GET"));
        return $tokenArr->access_token;
    }

    private function api_notice_increment($url, $data){
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        } else {
            return $tmpInfo;
        }
    }

    public function payQrcode($qrcode_id) {
        $pdata = json_encode([
            'scene'=> $qrcode_id,
            'path'=>"pages/index/index",
            'width'=>430,
            //'auto_color'=>'f',
            'is_hyaline'=>true,
         ]);
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$this->qrcodeAccessToken();
        $result = $this->api_notice_increment($url,$pdata);
        return $result;
    }
}