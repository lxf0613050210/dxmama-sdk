<?php


namespace Seekx2y\DxmamaSDK;

use Carbon\Carbon;
use Hanson\Foundation\AbstractAPI;
use Seekx2y\DxmamaSDK\Exceptions\ParamValidException;

class Api extends AbstractAPI
{
    const DEV_URL = 'https://mama.dxy.net';
    const PRODUCTION_URL = 'https://mama.dxy.com';

    private $app;
    private $apiType; // 1 跨境 2 一般贸易
    private $signStr; // 生成sign时字符串首尾应该加入的参数 token  或 appsecret

    /**
     * Api constructor.
     * @param Dxmama $dxmama
     * @throws ParamValidException
     */
    public function __construct(Dxmama $dxmama)
    {
        $this->app = $dxmama;
        $config    = $this->app->getConfig();
        if (isset($config['common']['supplierId'])) {
            $this->apiType = 1;
            $this->signStr = $this->app->getConfig('token');
        } elseif (isset($config['common']['appkey'])) {
            $this->apiType = 2;
            $this->signStr = $this->app->getConfig('appsecret');
        } else {
            throw new ParamValidException('请检查配置参数结构，跨境和一般贸易API公共参数不同');
        }
    }

    private function makeSign(array &$params)
    {
        ksort($params);
        $str = $this->signStr;
        foreach ($params as $k => $v) {
            $str .= $k . $v;
        }
        $str .= $this->signStr;
        if ($this->apiType == 2) {
            $str = strtolower($str);
        }

        return md5($str);
    }

    /**
     * @param string $method 对于跨境，后跟接口地址， 对于一般贸易，method参数放入请求参数
     * @param array $params
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $method, array &$params)
    {
        $isDebug = $this->app->getConfig('debug');
        $url     = $isDebug ? static::DEV_URL : static::PRODUCTION_URL;
        switch ($this->apiType) {
            case 1:
                $data = array_merge($this->app->getConfig('common'), [
                    'timestamp' => intval(Carbon::now()->timestamp . '000'),
                    'data'      => json_encode($params),
                ]);
                $url  .= $method;
                break;
            case 2:
                $data = array_merge($this->app->getConfig('common'), [
                    'method'     => $method,
                    'bizcontent' => json_encode($params),
                ]);
                $url  .= '/japi/platform/201029020';
                break;
        }
        $data['sign'] = $this->makeSign($data);
        if ($isDebug) {
            $data['debug'] = $isDebug;
        }
        $response = $this->getHttp()->post($url, $data);

        return json_decode(strval($response->getBody()));
    }

    public function middlewares()
    {
        $this->http->addMiddleware($this->headerMiddleware([
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ]));
    }
}
