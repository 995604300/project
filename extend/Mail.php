<?php
/**
 * 邮件服务类
 */
class Mail extends \PHPMailer\PHPMailer\PHPMailer
{
    function __construct()
    {
        date_default_timezone_set('PRC');                          // 默认时区设置
        $this->IsSMTP(); // 启用SMTP
        $this->Host = 'smtp.mxhichina.com'; //SMTP服务器 以126邮箱为例子
        $this->Port = 80;  //邮件发送端口（线上443端口，本地测试25端口）
        $this->SMTPAuth = true;  //启用SMTP认证
        $this->SMTPSecure = "tsl";   // 设置安全验证方式为ssl
        $this->CharSet = "UTF-8"; //字符集
        $this->Encoding = "base64"; //编码方式
        $this->Username = 'server@rulaiyun.net';  //你的邮箱
        $this->Password = 'Rulaiyun.net';  //你的密码
        $this->Subject = '如来云系统提示'; //邮件标题
        $this->From = 'server@rulaiyun.net';  //发件人地址（也就是你的邮箱）
        $this->FromName = '如来云';  //发件人姓名
    }

    /**
     * 发送邮件
     * @param  [type] $toMail      收件人地址
     * @param  [type] $toName      收件人名称
     * @param  [type] $subject     邮件主题
     * @param  [type] $content     邮件内容，支持html
     * @param  [type] $attachment  附件列表。文件路径或路径数组
     * @return [type]              成功返回true，失败返回错误消息
     */
    function sendMail($toMail, $toName, $content)
    {
        $this->AddAddress($toMail, $toName); //添加收件人（地址，昵称）
        $this->IsHTML(true); //支持html格式内容
        $this->msgHTML($content);   //邮件主体内容
        //$this->Body = $content;     //邮件主体内容
        //发送成功就删除
        if (!$this->Send()) {
            return $this->ErrorInfo;
        } else {
            return true;
        }
    }
}