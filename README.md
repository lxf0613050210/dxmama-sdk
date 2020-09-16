# 丁香妈妈跨境 SDK

Based on [foundation-sdk](https://github.com/HanSon/foundation-sdk)


## Requirement
- PHP >= 7.1
- **[composer](https://getcomposer.org/)**

## Installation
```
composer require seek-x2y/dxmama-sdk -vvv
```
## Usage
本扩展包以不同参数区分是跨境还是一般贸易
```php
// 提供supplierId这种是跨境
/*
$config = [
    'supplierId' => '',
    'token' => '',
    'debug' => true, // 是否查看http请求详情
];*/
// 这种是一般贸易
$config = [
    'appkey' => '',
    'appsecret' => '',
    'token' => '',
    'debug' => true, // 是否查看http请求详情
];

$api = new Dxmama($this->config);
$res = $api['orders']->getOrderList();
var_dump($res);
```

## License

MIT
