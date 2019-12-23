<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: ShouKun Liu  |  Email:24147287@qq.com  | Time:2017/3/11 10:56
// +----------------------------------------------------------------------
// | TITLE: 发送响应
// +----------------------------------------------------------------------
namespace DawnApi\facade;
use think\Response;
use think\response\Redirect;

trait Send
{
    protected $restDefaultType = 'json';
    /**
     * 设置响应类型
     * @param null $type
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = (string)(!empty($type)) ? $type : $this->restDefaultType;
        return $this;
    }

    /**
     * 失败响应
     * @param int $error
     * @param string $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     */
    public function sendErrors($error = 400, $message = '失败', $code = 400, $data = [], $headers = [], $options = [])
    {
        $responseData['error'] = (string)$error;
        $responseData['message'] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers,$options);
    }

    /**
     * 失败响应
     * @param int $error
     * @param string $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     */
    public function sendError($time = 0, $error = 1, $message = '失败', $code = 200, $data = [], $headers = [], $options = [])
    {
        $responseData['error'] = (string)$error;
        $responseData['message'] = (string)$message;
        $responseData['time'] = (string)$time;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers, $options);
    }

    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @param int $code
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\Xml
     */
    public function sendSuccess($time = 0, $data = [], $message = '成功', $code = 200, $headers = [], $options = [])
    {
        $responseData['error'] = 0;
        $responseData['message'] = (string)$message;
        $responseData['time'] = $time.' ms';
        $responseData['data'] = !empty($data) ? $data : [];
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers,$options);
    }

    public function sendSuccesss($data = [], $message = '成功', $code = 200, $headers = [], $options = [], $time = 0)
    {
        $responseData['error'] = 0;
        $responseData['message'] = (string)$message;
        $responseData['time'] = (string)$time;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers, $options);
    }

    /**
     * 重定向
     * @param $url
     * @param array $params
     * @param int $code
     * @param array $with
     * @return Redirect
     */
    public function sendRedirect($url, $params = [], $code = 302, $with = [])
    {
        $response = new Redirect($url);
        if (is_integer($params)) {
            $code = $params;
            $params = [];
        }
        $response->code($code)->params($params)->with($with);
        return $response;
    }

    /**
     * 响应
     * @param $responseData
     * @param $code
     * @param $headers
     * @param $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\View|\think\response\Xml
     */
    public function response($responseData, $code, $headers,$options)
    {
        if (!isset($this->type) || empty($this->type)) $this->setType();
        return Response::create($responseData,$this->type, $code, $headers,$options);
    }
}