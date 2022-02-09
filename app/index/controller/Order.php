<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\service\OrderService;
use app\service\PaymentService;
use app\service\GoodsCommentsService;
use app\service\ConfigService;
use app\service\SeoService;
use app\service\ResourcesService;

/**
 * 订单管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Order extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();

        // 是否登录
        $this->IsLogin();
    }

    /**
     * 订单列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     */
    public function Index()
    {
        // 总数
        $total = OrderService::OrderTotal($this->form_where);

        // 分页
        $page_params = [
            'number'    =>  $this->page_size,
            'total'     =>  $total,
            'where'     =>  $this->data_request,
            'page'      =>  $this->page,
            'url'       =>  MyUrl('index/order/index'),
        ];
        $page = new \base\Page($page_params);

        // 获取列表
        $data_params = [
            'm'                 => $page->GetPageStarNumber(),
            'n'                 => $this->page_size,
            'where'             => $this->form_where,
            'order_by'          => $this->form_order_by['data'],
            'is_orderaftersale' => 1,
            'is_operate'        => 1,
            'user_type'         => 'user',
        ];
        $ret = OrderService::OrderList($data_params);

        // 支付参数
        $pay_params = OrderService::PayParamsHandle($this->data_request);

        // 发起支付 - 支付方式
        MyViewAssign('buy_payment_list', PaymentService::BuyPaymentList(['is_enable'=>1, 'is_open_user'=>1]));

        // 加载百度地图api
        MyViewAssign('is_load_baidu_map_api', 1);

        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('我的订单', 1));

        // 基础参数赋值
        MyViewAssign('page_html', $page->GetPageHtml());
        MyViewAssign('data_list', $ret['data']);
        MyViewAssign('pay_params', $pay_params);
        MyViewAssign('params', $this->data_request);
        return MyView();
    }

    /**
     * 订单详情
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-08
     * @desc    description
     */
    public function Detail()
    {
        // 获取订单信息
        $data = $this->OrderFirst();
        if(empty($data))
        {
            MyViewAssign('msg', '没有相关数据');
            return MyView('public/tips_error');
        }

        // 发起支付 - 支付方式
        MyViewAssign('buy_payment_list', PaymentService::BuyPaymentList(['is_enable'=>1, 'is_open_user'=>1]));

        // 虚拟销售配置
        $site_fictitious = ConfigService::SiteFictitiousConfig();
        MyViewAssign('site_fictitious', $site_fictitious['data']);

        // 支付参数
        $pay_params = OrderService::PayParamsHandle($this->data_request);

        // 加载百度地图api
        MyViewAssign('is_load_baidu_map_api', 1);

        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('订单详情', 1));

        // 数据赋值
        MyViewAssign('data', $data);
        MyViewAssign('pay_params', $pay_params);
        MyViewAssign('params', $this->data_request);
        return MyView();
    }

    /**
     * 评价页面
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-08
     * @desc    description
     */
    public function Comments()
    {
        // 获取订单信息
        $data = $this->OrderFirst();
        if(empty($data))
        {
            MyViewAssign('msg', '没有相关数据');
            return MyView('public/tips_error');
        }

        // 上一个页面 url 地址
        MyViewAssign('referer_url', empty($_SERVER['HTTP_REFERER']) ? MyUrl('index/order/index') : $_SERVER['HTTP_REFERER']);
        MyViewAssign('data', $data);

        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('订单评论', 1));

        // 编辑器文件存放地址
        MyViewAssign('editor_path_type', ResourcesService::EditorPathTypeValue('order_comments-'.$this->user['id'].'-'.$data['id']));
        return MyView();
    }

    /**
     * 获取一条订单信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-25
     * @desc    description
     */
    public function OrderFirst()
    {
        $data = [];
        if(!empty($this->data_request['id']))
        {
            // 条件
            $where = [
                ['is_delete_time', '=', 0],
                ['user_is_delete_time', '=', 0],
                ['id', '=', intval($this->data_request['id'])],
                ['user_id', '=', $this->user['id']],
            ];

            // 获取列表
            $data_params = [
                'm'                 => 0,
                'n'                 => 1,
                'where'             => $where,
                'is_orderaftersale' => 1,
                'is_operate'        => 1,
                'user_type'         => 'user',
            ];
            $ret = OrderService::OrderList($data_params);
            $data = (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
        }
        return $data;
    }

    /**
     * 评价保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-09
     * @desc    description
     */
    public function CommentsSave()
    {
        if($this->data_post)
        {
            $params = $this->data_post;
            $params['user'] = $this->user;
            $params['business_type'] = 'order';
            return GoodsCommentsService::Comments($params);
        } else {
            MyViewAssign('msg', '非法访问');
            return MyView('public/tips_error');
        }
    }

    /**
     * 订单支付
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     */
    public function Pay()
    {
        $params = $this->data_request;
        $params['user'] = $this->user;
        $ret = OrderService::Pay($params);
        if($ret['code'] == 0)
        {
            return MyRedirect($ret['data']['data']);
        } else {
            MyViewAssign('msg', $ret['msg']);
            return MyView('public/tips_error');
        }
    }

    /**
     * 支付同步返回处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     */
    public function Respond()
    {
        // 参数
        $params = $this->data_request;

        // 是否自定义状态
        if(isset($params['appoint_status']))
        {
            $ret = ($params['appoint_status'] == 0) ? DataReturn('支付成功', 0) : DataReturn('支付失败', -100);

            // 获取支付回调数据
        } else {
            $params['user'] = $this->user;
            $ret = OrderService::Respond($params);
        }

        // 自定义链接
        MyViewAssign('to_url', MyUrl('index/order/index'));
        MyViewAssign('to_title', '我的订单');

        // 状态
        MyViewAssign('msg', $ret['msg']);
        if($ret['code'] == 0)
        {
            return MyView('public/tips_success');
        }
        return MyView('public/tips_error');
    }

    /**
     * 订单取消
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-30
     * @desc    description
     */
    public function Cancel()
    {
        if($this->data_post)
        {
            $params = $this->data_post;
            $params['user_id'] = $this->user['id'];
            $params['creator'] = $this->user['id'];
            $params['creator_name'] = $this->user['user_name_view'];
            return OrderService::OrderCancel($params);
        } else {
            MyViewAssign('msg', '非法访问');
            return MyView('public/tips_error');
        }
    }

    /**
     * 订单收货
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-30
     * @desc    description
     */
    public function Collect()
    {
        if($this->data_post)
        {
            $params = $this->data_post;
            $params['user_id'] = $this->user['id'];
            $params['creator'] = $this->user['id'];
            $params['creator_name'] = $this->user['user_name_view'];
            return OrderService::OrderCollect($params);
        } else {
            MyViewAssign('msg', '非法访问');
            return MyView('public/tips_error');
        }
    }

    /**
     * 订单删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-30
     * @desc    description
     */
    public function Delete()
    {
        if($this->data_post)
        {
            $params = $this->data_post;
            $params['user_id'] = $this->user['id'];
            $params['creator'] = $this->user['id'];
            $params['creator_name'] = $this->user['user_name_view'];
            $params['user_type'] = 'user';
            return OrderService::OrderDelete($params);
        } else {
            MyViewAssign('msg', '非法访问');
            return MyView('public/tips_error');
        }
    }

    /**
     * 支付状态校验
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-01-08
     * @desc    description
     */
    public function PayCheck()
    {
        if($this->data_post)
        {
            $params = $this->data_post;
            $params['user'] = $this->user;
            return OrderService::OrderPayCheck($params);
        } else {
            MyViewAssign('msg', '非法访问');
            return MyView('public/tips_error');
        }
    }
}
?>