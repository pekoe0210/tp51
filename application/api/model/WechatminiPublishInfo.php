<?php

namespace app\api\model;


/**
 *
 * Class WechatminiPublishInfo
 * @package app\common\model
 * @property int $id                    主键id，自增长
 * @property int $saas_account_id       saas_account_id
 * @property string $authorizer_appid   小程序appid
 * @property string $original_id        小程序原始ID
 * @property int $template_id           代码库中的代码模版ID
 * @property mediumtext $ext_json       第三方自定义的配置
 * @property string $user_version       代码版本号
 * @property string $user_desc          代码描述
 * @property tinyint $is_upload         小程序自定义设置是否上传,0为未上传，1为上传
 * @property string $test_qrcode        体验二维码
 * @property text    $page_list         小程序的第三方提交代码的页面配置
 * @property string $auditid            审核编码
 * @property string $goto_check_time    提审时间
 * @property string $check_version      审核版本号
 * @property tinyint $check_status      审核状态，其中0为审核成功，1为审核失败，2为审核中，3为未审核，4为取消审核
 * @property text $reason               审核失败原因
 * @property string $wxacode            小程序码
 * @property tinyint $is_gotorelease    是否可以发布，0为不可以，1为可以
 * @property text $release_page         发布小程序的页面配置
 * @property string $release_version    发布版本
 * @property string $release_time       发布时间
 * @property tinyint $is_release        是否发布，0为未发布，1为发布
 * @property string $create_time 添加时间
 * @property string $update_time 更新时间
 * @property string $delete_time 删除时间
 */
class WechatminiPublishInfo extends BaseModel
{


}