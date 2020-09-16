<?php

namespace Seekx2y\DxmamaSDK;

use Carbon\Carbon;
use Hanson\Foundation\Foundation;
use Seekx2y\DxmamaSDK\Exceptions\ParamValidException;
use Seekx2y\DxmamaSDK\Exceptions\TimeException;

class Dxmama extends Foundation
{
    protected $providers = [
        ServiceProvider::class
    ];

    public function __construct(array $config)
    {
        $config['debug'] = $config['debug'] ?? false;
        parent::__construct($config);
    }

    /**
     * 获取一般贸易订单
     * @param Carbon|null $startTime 开始时间(格 式:yyyy-MM-dd HH:mm:ss)
     * @param Carbon|null $endTime 截止时间(格 式:yyyy-MM-dd HH:mm:ss)
     * @param null $orderNo 平台订单号，若不为空，则 代表查询单个订单的数据， 查询单个订单时，可不传时 间、状态等
     * @param string $orderStatus 订单交易状态 (JH_01:等待买家付款, JH_02:等待卖家发货, JH_03:等待买家确认收货, JH_04:交易成功, JH_05:交易关闭, JH_99:所有订单)
     * @param string $timeType 订单时间类别(订单修改时 间=JH_01，订单创建时间 =JH_02)
     * @param int $pageIndex
     * @param int $pageSize
     * @return mixed
     * @throws ParamValidException
     * @throws TimeException
     */
    public function generalOrders(Carbon $startTime = null, Carbon $endTime = null, $orderNo = null, $orderStatus = 'JH_02', $timeType = 'JH_02', $pageIndex = 1, $pageSize = 30)
    {
        if (empty($orderNo)) {
            if (empty($startTime) && empty($endTime)) {
                throw new ParamValidException('参数中开始时间、结束时间或者订单编号不能同时为空');
            } elseif (!empty($startTime) && empty($endTime)) {
                $endTime = $startTime->addMinutes(119);
            } elseif (empty($startTime) && !empty($endTime)) {
                $startTime = $endTime->subMinutes(119);
            } elseif ($startTime->diffInHours($endTime) > 2) {
                throw new TimeException('拉取发货单的结束时间与开始时间不能相差两个小时以上');
            }
            $params = [
                'StartTime'   => $startTime->format('Y-m-d H:i:s'),
                'EndTime'     => $endTime->format('Y-m-d H:i:s'),
                'OrderStatus' => $orderStatus,
                'TimeType'    => $timeType,
                'PageIndex'   => $pageIndex,
                'PageSize'    => $pageSize,
            ];
        } else {
            $params = ['PlatOrderNo' => $orderNo];
        }

        return $this->api->request('Differ.JH.Business.GetOrder', $params);
    }

    /**
     * 一般贸易订单物流同步
     * @param string $orderNo 平台订单号
     * @param string $logisticName
     * @param string $logisticType 快递类别，详见物流公司代码对照表
     * @param string $logisticNo 快递运单号
     * @param string $subPlatOrderNo 平台子订单交易单号，支持订单拆分为 不同商品不同数量发货，多个商品用“|” 隔开，为空则视为整单发货，包含子订 单 编 号 和 商 品 发 货 数 量 ， 格 式 suborderno1:count1|suborderno:count 2
     * @return mixed
     */
    public function generalDelivery(string $orderNo, string $logisticName, string $logisticType, string $logisticNo, string $subPlatOrderNo)
    {
        $params = [
            'PlatOrderNo'    => $orderNo,
            'LogisticName'   => $logisticName,
            'LogisticType'   => $logisticType,
            'LogisticNo'     => $logisticNo,
            'SubPlatOrderNo' => $subPlatOrderNo
        ];

        return $this->api->request('Differ.JH.Business.Send', $params);
    }

    /**
     * 发货前检测订单退款状态
     * @param string $orderNo
     * @return mixed
     */
    public function refundStatus(string $orderNo)
    {
        return $this->api->request('Differ.JH.Business.CheckRefundStatus', $orderNo);
    }

    /*****************************************************************************************************/

    public function crossBorderOrders(Carbon $startTime, Carbon $endTime, $pageNo = 1, $pageSize = 20)
    {
//        if ($startTime->diffInHours($endTime) > 1) {
//            throw new TimeException('拉取发货单的结束时间与开始时间不能相差两个小时以上');
//        }
        $params = [
            'startTime' => $startTime->format('Y-m-d H:i:s'),
            'endTime'   => $endTime->format('Y-m-d H:i:s'),
            'pageNo'    => $pageNo,
            'pageSize'  => $pageSize,
        ];

        return $this->api->request('/japi/platform/201029007', $params);
    }

    /**
     * 跨境物流信息同步
     * @param string $orderNo
     * @param string $logisticName 物流公司名称
     * @param string $logisticType 物流公司编码
     * @param string $logisticNo  物流单号
     * @return mixed
     */
    public function cbDelivery(string $orderNo, string $logisticName, string $logisticType, string $logisticNo)
    {
        $params = [
            'orderId'            => $orderNo,
            'expressCompanyName' => $logisticName,
            'expressCompanyNo'   => $logisticType,
            'expressNo'          => $logisticNo,
        ];

        return $this->api->request('/japi/platform/201029009', $params);
    }

    /**
     * 清关状态同步
     * @param string $orderNo
     * @param int $status
     * @param string $cancelInfo
     * @return mixed
     */
    public function customsStatus(string $orderNo, int $status, string $cancelInfo)
    {
        $params = [
            'orderId'    => $orderNo,
            'status'     => $status,
            'cancelInfo' => $cancelInfo
        ];

        return $this->api->request('/japi/platform/201029008', $params);
    }

}