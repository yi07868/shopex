<qs src="../../utils/tools.qs" module="tools" />
<!-- 导航 -->
<view class="nav">
  <block qq:for="{{nav_status_list}}" qq:key="key">
    <view qq:if="{{nav_status_index == index}}" class="item fl tc cr-main" data-index="{{index}}" bindtap="nav_event">{{item.name}}</view>
    <view qq:else class="item fl tc" data-index="{{index}}" bindtap="nav_event">{{item.name}}</view>
  </block>
</view>

<!-- 订单列表 -->
<scroll-view scroll-y="{{true}}" class="scroll-box" bindscrolltolower="scroll_lower" lower-threshold="30">
  <view class="list-content">
    <view class="list-item bg-white spacing-mb" qq:if="{{data_list.length > 0}}" qq:for="{{data_list}}" qq:key="key">
      <view class="item-base oh br-b">
        <!-- 选择 -->
        <view qq:if="{{nav_status_index == 1 && home_is_enable_order_bulk_pay == 1}}" bindtap="selected_event" data-oid="{{item.id}}" class="fl selected">
          <image class="icon" src="/images/default-select{{tools.indexOf(order_select_ids, item.id) ? '-active' : ''}}-icon.png" mode="widthFix" />
        </view>
        <!-- 基础信息 -->
        <view class="fl" bindtap="warehouse_group_event" data-value="{{item.warehouse_url || ''}}">
          <image qq:if="{{(item.warehouse_icon || null) != null}}" class="warehouse-group-icon va-m margin-right-xs" src="{{item.warehouse_icon}}" mode="aspectFit" />
          <text class="cr-666 va-m">{{item.warehouse_name}}</text>
        </view>
        <text class="fr cr-main">{{item.status_name}}<text qq:if="{{(item.is_under_line_text || null) != null}}">（{{item.is_under_line_text}}）</text></text>
      </view>
      <view qq:for="{{item.items}}" qq:key="key" qq:for-item="detail" class="goods-item br-b-dashed oh">
        <navigator url="/pages/user-order-detail/user-order-detail?id={{item.id}}" hover-class="none">
          <image class="goods-image fl" src="{{detail.images}}" mode="aspectFill" />
          <view class="goods-base">
            <view class="goods-title multi-text" >{{detail.title}}</view>
            <block qq:if="{{detail.spec != null}}">
              <view class="goods-spec cr-888" qq:for="{{detail.spec}}" qq:key="key" qq:for-item="spec">
                {{spec.type}}:{{spec.value}}
              </view>
            </block>
            <view qq:if="{{(item.is_can_launch_aftersale == 1 || (detail.orderaftersale || null) != null) && (detail.orderaftersale_btn_text || null) != null}}" class="orderaftersale-btn-text" catchtap="orderaftersale_event" data-oid="{{item.id}}" data-did="{{detail.id}}">{{detail.orderaftersale_btn_text}}</view>
          </view>
          <view class="oh goods-price">
            <text class="sales-price">{{item.currency_data.currency_symbol}}{{detail.price}}</text>
            <text qq:if="{{detail.original_price > 0}}" class="original-price">{{item.currency_data.currency_symbol}}{{detail.original_price}}</text>
            <text class="buy-number">x{{detail.buy_number}}</text>
          </view>
        </navigator>
      </view>
      <view class="item-describe tr cr-666">{{item.describe}}</view>
      <view qq:if="{{item.operate_data.is_cancel + item.operate_data.is_pay + item.operate_data.is_collect + item.operate_data.is_comments + item.operate_data.is_delete > 0 || (item.status == 2 && item.order_model != 2)}}" class="item-operation tr br-t">
        <button qq:if="{{item.operate_data.is_cancel == 1}}" class="submit-cancel" type="default" size="mini" bindtap="cancel_event" data-value="{{item.id}}" data-index="{{index}}" hover-class="none">取消</button>
        <button qq:if="{{item.operate_data.is_pay == 1}}" class="submit-pay cr-666 br" type="default" size="mini" bindtap="pay_event" data-value="{{item.id}}" data-index="{{index}}" hover-class="none">支付</button>
        <button qq:if="{{item.operate_data.is_collect == 1}}" class="submit-success cr-666 br" type="default" size="mini" bindtap="collect_event" data-value="{{item.id}}" data-index="{{index}}" hover-class="none">收货</button>
        <button qq:if="{{item.operate_data.is_comments == 1}}" class="submit-success cr-666 br" type="default" size="mini" bindtap="comments_event" data-value="{{item.id}}" data-index="{{index}}" hover-class="none">评论</button>
        <button qq:if="{{item.status == 2 && item.order_model != 2}}" class="submit-rush cr-666 br" type="default" size="mini" bindtap="rush_event" data-value="{{item.id}}" data-index="{{index}}" hover-class="none">催催</button>
      </view>
    </view>

    <view qq:if="{{data_list.length == 0}}">
      <import src="/pages/common/nodata.qml" />
      <template is="nodata" data="{{status: data_list_loding_status}}">
      </template>
    </view>

    <import src="/pages/common/bottom_line.qml" />
    <template is="bottom_line" data="{{status: data_bottom_line_status}}"></template>
  </view>
</scroll-view>

<!-- 合并支付 -->
<view qq:if="{{nav_status_index == 1 && order_select_ids.length > 0 && home_is_enable_order_bulk_pay == 1}}">
  <button class="submit-fixed pay-merge-submit" type="default" size="mini" hover-class="none" bindtap="pay_merge_event">合并支付</button>
</view>

<!-- 支付方式 popup -->
<component-popup prop-show="{{is_show_payment_popup}}" prop-position="bottom" bindonclose="payment_popup_event_close">
  <view qq:if="{{payment_list.length > 0}}" class="payment-list oh bg-white">
    <view class="item tc fl" qq:for="{{payment_list}}" qq:key="key">
      <view class="item-content br" data-value="{{item.id}}" bindtap="popup_payment_event">
        <image qq:if="{{(item.logo || null) != null}}" class="icon" src="{{item.logo}}" mode="widthFix" />
        <text>{{item.name}}
        </text>
      </view>
    </view>
  </view>
  <view qq:else class="payment-list oh bg-white tc cr-888">没有支付方式</view>
</component-popup>