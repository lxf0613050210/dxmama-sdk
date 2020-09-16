<?php

namespace Tests\Unit;

use Seekx2y\DxmamaSDK\Dxmama;
use PHPUnit\Framework\TestCase;

class GetOrdersTest extends TestCase
{
    public function testCbDelivery()
    {
        $config = [
            'common' => [
                'supplierId' => '3348323072356128508',
            ],
            'token'      => '5f11466cf1e22d1cf99feede6a25f7b8',
            'debug'      => true, // 非必填，是否查看http请求详情
        ];
        $params = '{
"expressNo":"YT4434031440711", "expressCompanyName":"圆通速递", "expressCompanyNo":"yuantong", "orderId":3409025170189847223
}';
        $params = json_decode($params, true);
        $api    = new Dxmama($config);
        $res    = $api->cbDelivery($params['orderId'], $params['expressCompanyName'], $params['expressCompanyNo'], $params['expressNo']);
        var_dump($res);
        $this->assertObjectHasAttribute('success', $res);
        $this->assertEquals(1, $res->success);
    }
}
