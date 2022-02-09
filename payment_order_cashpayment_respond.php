<?php

/**
 * 订单支付同步入口
 */
namespace think;

// 默认绑定模块
$_GET['s'] = 'order/respond';

// 支付模块标记
define('PAYMENT_TYPE', 'CashPayment');

// 根目录入口
define('IS_ROOT_ACCESS', true);

// 引入公共入口文件
require __DIR__.'/public/core.php';

// 加载基础文件
require __DIR__ . '/vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->name('index')->run();
$response->send();
$http->end($response);
?>