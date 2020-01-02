<?php
/**

 *      FILE_NAME: Auth.php
 *      AUTHOR: Wang YX()
 *      CREATE_TIME: 2019-09-16
 */
namespace app\api\auth;
use app\api\model\RolePermission;
use app\api\model\User;
use DawnApi\auth\OAuth;
use DawnApi\exception\UnauthorizedException;
use RandomLib\Factory;
use think\Cache;
use think\Request;

class OauthAuth extends OAuth
{

    /**
     * 客户端获取access_token
     * @param Request $request
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\Xml
     */
    public function accessToken(Request $request)
    {
        $start = getMicrotime();
        //获客户端信息
        try {
            $this->getClient($request);
        } catch (UnauthorizedException $e) {
            //错误则返回给客户端
            $end = getMicrotime();
            return $this->sendError(($end - $start), 401, $e->getMessage());
        } catch (Exception $e) {
            $end = getMicrotime();
            return $this->sendError(($end - $start), 500, $e->getMessage());
        }

        //校验信息
        if ($this->getClientInfo($this->client_id)->checkSecret()) {
            //通过下放令牌
            $access_token = $this->setAccessToken($this->clientInfo);
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start), 401,'token到期或身份验证失败！');
        }
        $permission = RolePermission::where('roleId',$this->clientInfo['RoleId'])->with('permission')->select();
        if ($permission) {
            $permission = collection($permission)->toArray();
        }
        $end = getMicrotime();
        $data = [
            'access_token' => $access_token['access_token'], //访问令牌
            'expires' => self::$expires,      //过期时间秒数
            'permission' => $permission,      //左侧菜单栏
        ];
        return $this->sendSuccess(($end - $start), $data);
    }

    /**
     * 校验密码
     * @return bool
     */
    public function checkSecret()
    {

        if ($this->secret == $this->clientInfo['Password']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户信息后 验证权限
     * @return mixed
     */
    public function certification()
    {
        if ($this->getAccessTokenInfo($this->access_token) == false) {
            return false;
        } else {
            return true;
        }
    }

    protected function getAccessTokenInfo($accessToken)
    {
        $keys = self::$accessTokenPrefix . $accessToken;
        $info = Cache::get($keys);
        if ($info == false || $info['expires_time'] < time()) return false;
            //验证索引是否正确
            $client_id = $info['client']['client_id'];
            if ($this->getAccessTokenAndClient($client_id) != $accessToken) return false;
            $this->clientInfo = $info['client'];
            return $info;
    }

    protected function getAccessTokenAndClient($client_id)
    {
        return Cache::get(self::$accessTokenAndClientPrefix . $client_id);
    }

    /**
     * 返回用户信息
     * @param $client_id
     * @return array
     */
    public static function getUserInfo($client_id)
    {
        $userInfo = User::get(['UserName'=>$client_id]);
        $data['RealName'] = $userInfo['RealName'];
        $data['Password'] = $userInfo['Password'];
        $data['UserName'] = $userInfo['UserName'];
        $data['IDCard'] = $userInfo['IDCard'];
        $data['RoleId'] = $userInfo['RoleId'];
        $data['ClassId'] = $userInfo['ClassId'];
        $data['client_id'] = $userInfo['UserName'];
        //  key $client_id
        return $data;
    }

    /**
     * 获取客户端所有信息
     * @param $client_id
     * @return mixed
     */
    public function getClientInfo($client_id)
    {
        // todo 通过客户端$client_id 获取所有信息
        $this->clientInfo = self::getUserInfo($client_id);
        return $this;
    }

    /**
     *  清除AccessToken
     * @param $clientInfo
     * @return int
     */
    public function clearAccessToken()
    {
        $clientInfo = $this->getAccessTokenInfo($this->access_token);
        self::destroyAccessToken($this->access_token,$clientInfo);
        return TRUE;
    }


    /**
     * 设置AccessToken
     * @param $clientInfo
     * @return int
     */
    protected function setAccessToken($clientInfo)
    {

        //生成令牌
        $accessToken = self::buildAccessToken();
        $accessTokenInfo = [
            'access_token' => $accessToken,//访问令牌
            'expires_time' => time() + self::$expires,      //过期时间时间戳
            'client' => $clientInfo,//用户信息
        ];
        self::saveAccessToken($accessToken, $accessTokenInfo);
        return $accessTokenInfo;
    }



    /**
     * 生成AccessToken
     * @return string
     */
    protected static function buildAccessToken()
    {
        //生成AccessToken
        $factory = new Factory();
        $generator = $factory->getMediumStrengthGenerator();
        return $generator->generateString(32, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    /**
     * 存储
     * @param $accessToken
     * @param $accessTokenInfo
     */
    protected static function saveAccessToken($accessToken, $accessTokenInfo)
    {
        //存储accessToken
        Cache::set(self::$accessTokenPrefix . $accessToken, $accessTokenInfo, self::$expires);
        //存储用户与信息索引 用于比较
        Cache::set(self::$accessTokenAndClientPrefix . $accessTokenInfo['client']['client_id'], $accessToken, self::$expires);
    }

    /**
     *  销毁
     * @param $accessToken
     * @param $accessTokenInfo
     */
    protected static function destroyAccessToken($accessToken, $accessTokenInfo)
    {
        //存储accessToken
        Cache::rm(self::$accessTokenPrefix . $accessToken);
        //存储用户与信息索引 用于比较
        Cache::rm(self::$accessTokenAndClientPrefix . $accessTokenInfo['client']['client_id']);
    }

    /**
     * 获取用户信息
     * @return bool
     */
    public function getUser()
    {
        $info = $this->getAccessTokenInfo($this->access_token);
        if ($info) {
            $this->client_id = $info['client']['client_id'];
            $this->user = $info['client'];
            return $this->user;
        } else {
            return false;
        }

    }

}