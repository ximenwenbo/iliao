<?php

namespace anerg\OAuth2\Gateways;

use anerg\OAuth2\Connector\Gateway;

class Qq extends Gateway
{
    const API_BASE            = 'https://graph.qq.com/';
    protected $AuthorizeURL   = 'https://graph.qq.com/oauth2.0/authorize';
    protected $AccessTokenURL = 'https://graph.qq.com/oauth2.0/token';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $params = [
            'response_type' => $this->config['response_type'],
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'state'         => $this->config['state'],
            'scope'         => $this->config['scope'],
            'display'       => $this->display,
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $this->getToken();

        if (!isset($this->token['openid']) || !$this->token['openid']) {
            $this->token['openid'] = $this->getOpenID();
        }

        return $this->token['openid'];
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userinfo()
    {
        $rsp = $this->userinfoRaw();

        $userinfo = [
            'openid'  => $this->openid(),
            'channel' => 'qq',
            'nick'    => $rsp['nickname'],
            'gender'  => $rsp['gender'] == "男" ? 'm' : 'f',
            'avatar'  => $rsp['figureurl_qq_2'] ? $rsp['figureurl_qq_2'] : $rsp['figureurl_qq_1'],
        ];
        return $userinfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function userinfoRaw()
    {
        return $this->call('user/get_user_info');
    }

    /**
     * 发起请求
     *
     * @param string $api
     * @param array $params
     * @param string $method
     * @return array
     */
    private function call($api, $params = [], $method = 'GET')
    {
        $method = strtoupper($method);

        $params['openid']             = $this->openid();
        $params['oauth_consumer_key'] = $this->config['app_id'];
        $params['access_token']       = $this->token['access_token'];
        $params['format']             = 'json';

        $data = $this->$method(self::API_BASE . $api, $params);

        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $token 获取access_token的方法的返回值
     */
    protected function parseToken($token)
    {
        parse_str($token, $data);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new \Exception("获取腾讯QQ ACCESS_TOKEN 出错：" . $token);
        }
    }

    /**
     * 通过接口获取openid
     *
     * @return string
     */
    private function getOpenID()
    {
        $client   = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::API_BASE . 'oauth2.0/me', ['query' => ['access_token' => $this->token['access_token']]]);
        $data     = $response->getBody()->getContents();
        $data     = json_decode(trim(substr($data, 9), " );\n"), true);
        if (isset($data['openid'])) {
            return $data['openid'];
        } else {
            throw new \Exception("获取用户openid出错：" . $data['error_description']);
        }
    }
}
