<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Wechatmini extends Migrator
{
    public function up()
    {


        $table = $this->table('wechatmini', ['engine' => 'InnoDB']);
        $table->addColumn(Column::integer('saas_account_id')->setNullable()->setComment('saas_account_id')->setDefault(0))
            ->addColumn(Column::string('nickname', 255)->setNullable()->setComment('小程序昵称')->setDefault(null))
            ->addColumn(Column::string('headimg', 255)->setNullable()->setComment('小程序头像')->setDefault(null))
            ->addColumn(Column::integer('fanc_num')->setNullable()->setComment('粉丝数')->setDefault(0))
            ->addColumn(Column::tinyInteger('service_type')->setNullable()->setComment('小程序类型:0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号')->setDefault(0))
            ->addColumn(Column::tinyInteger('verify_type_info')->setNullable()->setComment('授权方认证类型，-1代表未认证，0代表微信认证，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证')->setDefault(-1))
            ->addColumn(Column::string('original_id', 255)->setNullable()->setComment('小程序的原始ID')->setDefault(null))
            ->addColumn(Column::string('principal_name')->setNullable()->setComment('小程序的主体名称')->setDefault(null))
            ->addColumn(Column::string('alias', 255)->setNullable()->setComment('小程序所设置的微信号，可能为空')->setDefault(null))
            ->addColumn(Column::tinyInteger('is_open_store')->setNullable()->setComment('是否开通微信门店功能')->setDefault(0))
            ->addColumn(Column::tinyInteger('is_open_scan')->setNullable()->setComment('是否开通微信扫商品功能')->setDefault(0))
            ->addColumn(Column::tinyInteger('is_open_pay')->setNullable()->setComment('是否开通微信支付功能')->setDefault(0))
            ->addColumn(Column::tinyInteger('is_open_card')->setNullable()->setComment('是否开通微信卡券功能')->setDefault(0))
            ->addColumn(Column::tinyInteger('is_open_shake')->setNullable()->setComment('是否开通微信摇一摇功能')->setDefault(0))
            ->addColumn(Column::string('qrcode_url', 255)->setNullable()->setComment('小程序二维码图片的URL')->setDefault(null))
            ->addColumn(Column::integer('idc')->setNullable()->setComment('')->setDefault(0))
            ->addColumn(Column::string('signature', 255)->setNullable()->setComment('个性签名')->setDefault(null))
            ->addColumn(Column::string('authorizer_appid', 255)->setNullable()->setComment('授权方appid')->setDefault(null))
            ->addColumn(Column::string('authorizer_access_token', 255)->setNullable()->setComment('授权方access_token')->setDefault(null))
            ->addColumn(Column::timestamp('authorizer_expires_time')->setNullable()->setComment('授权方access_token过期时间')->setDefault(null))
            ->addColumn(Column::string('authorizer_refresh_token', 255)->setNullable()->setComment('授权方的刷新令牌')->setDefault(null))
            ->addColumn(Column::text('func_info')->setNullable()->setComment('公众号授权给开发者的权限集列表')->setDefault(null))
            ->addColumn(Column::tinyInteger('location_report')->setNullable()->setComment('地理位置上报选项:-1,未获取;0,无上报;1,进入会话时上报;2,每5s上报;')->setDefault(-1))
            ->addColumn(Column::tinyInteger('voice_recognize')->setNullable()->setComment('语音识别开关选项:-1,未获取;0,关闭语音识别;1,开启语音识别;')->setDefault(-1))
            ->addColumn(Column::tinyInteger('customer_service')->setNullable()->setComment('多客服开关选项:-1,未获取;0,关闭多客服;1,开启多客服;')->setDefault(-1))
            ->addColumn(Column::timestamp('authorized_at')->setNullable()->setComment('授权时间')->setDefault(null))
            ->addColumn(Column::timestamp('update_authorized_at')->setNullable()->setComment('更新授权时间')->setDefault(null))
            ->addColumn(Column::timestamp('unauthorized_at')->setNullable()->setComment('取消授权时间')->setDefault(null))
            ->addColumn(Column::timestamp('create_time')->setNullable()->setComment('添加时间')->setDefault(null))
            ->addColumn(Column::timestamp('update_time')->setNullable()->setComment('更新时间')->setDefault(null))
            ->addSoftDelete()
            ->setComment('商家小程序信息表')
            ->create();

    }
}
