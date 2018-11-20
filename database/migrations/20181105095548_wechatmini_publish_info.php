<?php

use think\migration\Migrator;
use think\migration\db\Column;

class WechatminiPublishInfo extends Migrator
{
    public function up()
    {


        $table = $this->table('wechatmini_publish_info', ['engine' => 'InnoDB']);
        $table->addColumn(Column::integer('saas_account_id')->setNullable()->setComment('saas_account_id')->setDefault(0))
            ->addColumn(Column::string('authorizer_appid', 255)->setNullable()->setComment('小程序appid')->setDefault(null))
            ->addColumn(Column::string('original_id', 50)->setNullable()->setComment('小程序原始ID')->setDefault(null))
            ->addColumn(Column::integer('template_id')->setNullable()->setComment('代码库中的代码模版ID')->setDefault(1))
            ->addColumn(Column::mediumtext('ext_json')->setNullable()->setComment('第三方自定义的配置')->setDefault(null))
            ->addColumn(Column::string('user_version', 50)->setNullable()->setComment('代码版本号')->setDefault(null))
            ->addColumn(Column::string('user_desc', 255)->setNullable()->setComment('代码描述')->setDefault(null))
            ->addColumn(Column::tinyInteger('is_upload')->setNullable()->setComment('小程序自定义设置是否上传,0为未上传，1为上传')->setDefault(0))
            ->addColumn(Column::string('test_qrcode', 255)->setNullable()->setComment('体验二维码')->setDefault(null))
            ->addColumn(Column::text('page_list')->setNullable()->setComment('小程序的第三方提交代码的页面配置')->setDefault(null))
            ->addColumn(Column::string('auditid', 255)->setNullable()->setComment('审核编码')->setDefault(null))
            ->addColumn(Column::timestamp('goto_check_time')->setNullable()->setComment('提审时间')->setDefault(null))
            ->addColumn(Column::string('check_version')->setNullable()->setComment('审核版本号')->setDefault(null))
            ->addColumn(Column::tinyInteger('check_status')->setNullable()->setComment('审核状态，其中0为审核成功，1为审核失败，2为审核中，3为未审核，4为取消审核')->setDefault(3))
            ->addColumn(Column::text('reason', 255)->setNullable()->setComment('审核失败原因')->setDefault(null))
            ->addColumn(Column::timestamp('check_time')->setNullable()->setComment('审核时间')->setDefault(null))
            ->addColumn(Column::string('wxacode', 255)->setNullable()->setComment('小程序码')->setDefault(null))
            ->addColumn(Column::tinyInteger('is_gotorelease')->setNullable()->setComment('是否可以发布，0为不可以，1为可以')->setDefault(0))
            ->addColumn(Column::text('release_page', 255)->setNullable()->setComment('发布小程序的页面配置')->setDefault(null))
            ->addColumn(Column::string('release_version', 50)->setNullable()->setComment('发布版本')->setDefault(null))
            ->addColumn(Column::timestamp('release_time')->setNullable()->setComment('发布时间')->setDefault(null))
            ->addColumn(Column::tinyInteger('is_release')->setNullable()->setComment('是否发布，0为未发布，1为发布')->setDefault(0))
            ->addColumn(Column::timestamp('create_time')->setNullable()->setComment('添加时间')->setDefault(null))
            ->addColumn(Column::timestamp('update_time')->setNullable()->setComment('更新时间')->setDefault(null))
            ->addSoftDelete()
            ->setComment('商家小程序发布信息表')
            ->create();

    }
}
