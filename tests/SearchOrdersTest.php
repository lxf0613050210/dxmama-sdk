<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Seekx2y\DxmamaSDK\Dxmama;
use PHPUnit\Framework\TestCase;

class SearchOrdersTest extends TestCase
{
    public function testCbOrder()
    {
        $config = [
            'common' => [
                'supplierId' => '3348323072356128508',
            ],
            'token'  => '5f11466cf1e22d1cf99feede6a25f7b8',
            'debug'  => true, // 非必填，是否查看http请求详情
        ];
        $api    = new Dxmama($config);
        $res    = $api->crossBorderOrders(Carbon::now()->subDay(), Carbon::now());
        var_dump($res);
        $this->assertObjectHasAttribute('success', $res);
        $this->assertEquals(1, $res->success);
    }
}
