<?php

namespace app\api\model;


/**
 *
 * Class Wechatmini
 * @package app\common\model
 * @property int $id                 主键id，自增长
 * @property int $saas_account_id    saas_account_id
 * @property string $nickname           小程序昵称
 * @property string $headimg            小程序头像
 * @property int $fanc_num           粉丝数
 * @property tinyint $service_type      小程序类型:0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号
 * @property tinyint $verify_type_info  授权方认证类型，-1代表未认证，0代表微信认证，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证
 * @property string $original_id        小程序的原始ID
 * @property string $principal_name     小程序的主体名称
 * @property string $alias              小程序所设置的微信号，可能为空
 * @property tinyint $is_open_store     是否开通微信门店功能
 * @property tinyint $is_open_scan      是否开通微信扫商品功能
 * @property tinyint $is_open_pay       是否开通微信支付功能
 * @property tinyint $is_open_card      是否开通微信卡券功能
 * @property tinyint $is_open_shake     是否开通微信摇一摇功能
 * @property string $qrcode_url         小程序二维码图片的URL
 * @property int $idc
 * @property string $signature          个性签名
 * @property string $authorizer_appid   授权方appid
 * @property string $authorizer_access_token    授权方access_token
 * @property string $authorizer_expires_time    授权方access_token过期时间
 * @property string $authorizer_refresh_token   授权方的刷新令牌
 * @property string $func_info          公众号授权给开发者的权限集列表
 * @property tinyint $location_report   地理位置上报选项:-1,未获取;0,无上报;1,进入会话时上报;2,每5s上报;
 * @property tinyint $voice_recognize   语音识别开关选项:-1,未获取;0,关闭语音识别;1,开启语音识别;
 * @property tinyint $customer_service  多客服开关选项:-1,未获取;0,关闭多客服;1,开启多客服;
 * @property string $authorized_at      授权时间
 * @property string $update_authorized_at   更新授权时间
 * @property string $unauthorized_at
 * @property string $create_time 添加时间
 * @property string $update_time 更新时间
 * @property string $delete_time 删除时间
 */
class Wechatmini extends BaseModel
{


}