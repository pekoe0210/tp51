<?php
namespace app\api\logic;

use think\facade\Log;
use think\Db;
use think\facade\Cache;
use app\api\model\Wechatmini;
use app\api\model\WechatminiPublishInfo;
use app\api\model\SaasAccount;
use think\Queue;

class WechatMiniLogic
{
    /**
     * 更新第三方平台授权方信息
     * @param $auth_code
     * @param $user
     * @throws ApiException
     */
    public static function updateComponentAuthorizerInfo($auth_code, $saas_account)
    {
        $saas_account_id = $saas_account['id'];

        $wechat = new \Pekoe\Wechat\Wechat('component');
        $authorization_info = $wechat->queryComponentApiAuth($auth_code);
        if (!$authorization_info) {
            Log::error('第三方授权获取公众号或小程序信息错误:' . $wechat->getErrorCode() . ' ' . $wechat->getErrorMsg());
            throw new \Exception('遇到错误了,请重新操作!');
        }

        $authorizer_appid = $authorization_info['authorization_info']['authorizer_appid'];

        //step 1:获取授权信息
        $authorizer_info = $wechat->getComponentAuthorizerInfo($authorizer_appid);
        if (!$authorizer_info) {
            Log::error('第三方授权获取授权信息错误:' . $wechat->getErrorCode() . ' ' . $wechat->getErrorMsg());
            throw new \Exception('遇到错误了,请重新操作!');
        }

        //判断是公众号还是小程序
        if (isset($authorizer_info['authorizer_info']['MiniProgramInfo'])) {
            //小程序
            self::updateMiniAppComponentAuthorizerInfo($authorizer_appid, $saas_account_id, $saas_account, $authorization_info, $authorizer_info);
        } else {
            //公众号
//            self::updateMpComponentAuthorizerInfo($authorizer_appid, $saas_account_id, $saas_account, $authorization_info, $authorizer_info);
            self::updateMiniAppComponentAuthorizerInfo($authorizer_appid, $saas_account_id, $saas_account, $authorization_info, $authorizer_info);
        }

        return true;
    }


    /**
     * 更新公众号第三方平台授权方信息
     * @param $auth_code
     * @param $user
     * @throws ApiException
     */
    public static function updateMpComponentAuthorizerInfo($authorizer_appid, $saas_account_id, $saas_account, $authorization_info, $authorizer_info)
    {
        //判断是否已有授权记录
        $wechatmini = \App\api\Model\Wechatmini::where('authorizer_appid', $authorizer_appid)
            ->select('id', 'saas_account_id')
            ->first();
        if ($wechatmini && $wechatmini->saas_account_id != $saas_account_id) {
            //刷新公众号的authorizer_access_token和authorizer_refresh_token
            $save_data = array(
                'authorizer_appid' => $authorizer_info['authorization_info']['authorizer_appid'],
                'authorizer_access_token' => $authorization_info['authorization_info']['authorizer_access_token'],
                'authorizer_expires_time' => $authorization_info['authorization_info']['expires_in'] + time() - 100,
                'authorizer_refresh_token' => $authorizer_info['authorization_info']['authorizer_refresh_token'],
                'func_info' => json_encode($authorization_info['authorization_info']['func_info'], JSON_UNESCAPED_UNICODE)
            );

            set_save_data($wechatmini, $save_data);
            $update = $wechatmini->save();

            if (!$update) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }

            throw new \Exception('该公众平台已绑定其他账号!');
        }

        //此处要判断这个授权操作要干啥
        //授权状态: new,新增授权; updateForOld,原有的公众号更新授权;updateForNew,替换为新的公众号;
        //这个授权是更新旧的公众号授权
        if ($wechatmini && $wechatmini->saas_account_id == $saas_account_id) {
            $auth_status = 'updateForOld';
        } elseif (empty($wechatmini->wechat_id)) {
            $auth_status = 'new';
        } else {
            $auth_status = 'updateForNew';
        }

        //保存到数据库
        $save_data = array(
            'saas_account_id' => $saas_account_id,
            'nickname' => $authorizer_info['authorizer_info']['nick_name'],
            'headimg' => $authorizer_info['authorizer_info']['head_img'],
            'service_type' => $authorizer_info['authorizer_info']['service_type_info']['id'],
            'verify_type_info' => $authorizer_info['authorizer_info']['verify_type_info']['id'],
            'original_id' => $authorizer_info['authorizer_info']['user_name'],
            'principal_name' => $authorizer_info['authorizer_info']['principal_name'],
            'alias' => $authorizer_info['authorizer_info']['alias'],
            'is_open_store' => $authorizer_info['authorizer_info']['business_info']['open_store'],
            'is_open_scan' => $authorizer_info['authorizer_info']['business_info']['open_scan'],
            'is_open_pay' => $authorizer_info['authorizer_info']['business_info']['open_pay'],
            'is_open_card' => $authorizer_info['authorizer_info']['business_info']['open_card'],
            'is_open_shake' => $authorizer_info['authorizer_info']['business_info']['open_shake'],
            'qrcode_url' => $authorizer_info['authorizer_info']['qrcode_url'],
            'idc' => $authorizer_info['authorizer_info']['idc'],
            'signature' => $authorizer_info['authorizer_info']['signature'],
            'authorizer_appid' => $authorizer_info['authorization_info']['authorizer_appid'],
            'authorizer_access_token' => $authorization_info['authorization_info']['authorizer_access_token'],
            'authorizer_expires_time' => date('Y-m-d H:i:s',$authorization_info['authorization_info']['expires_in'] + time() - 100),
            'authorizer_refresh_token' => $authorizer_info['authorization_info']['authorizer_refresh_token'],
            'func_info' => json_encode($authorization_info['authorization_info']['func_info'], JSON_UNESCAPED_UNICODE)
        );

        DB::beginTransaction();
        $user_mp_id = 0;

        if ($auth_status == 'updateForOld') {
            $save_data['update_authorized_at'] = date('Y-m-d H:i:s',time());
            $res = \App\api\Model\Wechatmini::where('id', $wechatmini->wechat_id)
                ->update($save_data);
            if (!$res) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }
            $wechat_id = $wechatmini->wechat_id;

            if ($wechatmini->is_guide == 1) {
                //更新user表
                $user_data = [
                    'guide_step' => 2
                ];
                $update = \App\api\Model\SaasAccount::where('id', $saas_account_id)
                    ->update($user_data);
                if (!$update) {
                    DB::rollBack();
                    throw new \Exception('遇到错误了,请重新操作!');
                }
            }

        } elseif ($auth_status == 'updateForNew') {
            //删除原来的公众号
            $res = \App\api\Model\Wechatmini::where('id', $wechatmini->wechat_id)
                ->update(['is_on' => 0]);
            if (!$res) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }
        }

        if ($auth_status == 'new' || $auth_status == 'updateForNew') {  // 新的授权

            $save_data['authorized_at'] = date('Y-m-d H:i:s',time());
            $wechat_model = new \App\api\Model\Wechatmini();
            set_save_data($wechat_model, $save_data);
            $res = $wechat_model->save();

            if (!$res) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }

            //更新user表
            $user_data = [
                'wechat_id' => $wechat_model->id
            ];

            if ($user->is_guide == 1) {
                $user_data['guide_step'] = 2;
            }
            $update = \App\api\Model\SaasAccount::where('id', $saas_account_id)
                ->update($user_data);
            if (!$update) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }
            $user_mp_id = $wechat_model->id;
        }

        DB::commit();

        //队列异步处理下载头像
//        dispatch((new \App\Jobs\DownloadMpHeadImg([
//            'url' => $authorizer_info['authorizer_info']['head_img'],
//            'user_mp_id' => $user_mp_id
//        ]))->onQueue('downImage'));

        //公众号二维码
//        dispatch((new \App\Jobs\DownloadMpQrcode([
//            'url' => $authorizer_info['authorizer_info']['qrcode_url'],
//            'user_mp_id' => $user_mp_id
//        ]))->onQueue('downImage'));
//
//        //公众号粉丝列表(新的公众号才触发)
//        dispatch((new \App\Jobs\GetMpFans($user_mp_id, 'total')));
        return true;
    }


    /**
     * 更新小程序第三方平台授权方信息
     * @param $auth_code
     * @param $user
     * @throws ApiException
     */
    public static function updateMiniAppComponentAuthorizerInfo($authorizer_appid, $saas_account_id, $saas_account, $authorization_info, $authorizer_info)
    {
        //判断小程序是否已授权
        $wechatmini_model = new Wechatmini();
        $wechatmini = $wechatmini_model->where('authorizer_appid', $authorizer_appid)
            ->find();

        DB::startTrans();

        if ($wechatmini && $wechatmini->saas_account_id != $saas_account_id) {
            //刷新小程序的authorizer_access_token和authorizer_refresh_token
            $save_data = array(
                'authorizer_access_token' => $authorization_info['authorization_info']['authorizer_access_token'],
                'authorizer_expires_time' => date('Y-m-d H:i:s',$authorization_info['authorization_info']['expires_in'] + time() - 100),
                'authorizer_refresh_token' => $authorizer_info['authorization_info']['authorizer_refresh_token'],
                'func_info' => json_encode($authorization_info['authorization_info']['func_info'], JSON_UNESCAPED_UNICODE)
            );
            $update = $wechatmini->update($save_data);
            if (!$update) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }
            throw new \Exception('该小程序已授权其他账号!');
        }

        //此处要判断这个授权操作要干啥
        //授权状态: new,新增授权; updateForOld,原有的小程序更新授权;updateForNew,替换为新的小程序;
        //这个授权是更新旧的小程序授权
        if ($wechatmini && $wechatmini->saas_account_id == $saas_account_id) {
            $auth_status = 'updateForOld';
        } elseif (empty($saas_account['wechatmini_id'])) {
            $auth_status = 'new';
        } else {
            $auth_status = 'updateForNew';
        }

        //保存到数据库wechatmini
        $save_data = array(
            'saas_account_id' => $saas_account_id,
            'nickname' => $authorizer_info['authorizer_info']['nick_name'],
            'headimg' => $authorizer_info['authorizer_info']['head_img'],
            'service_type' => $authorizer_info['authorizer_info']['service_type_info']['id'],
            'verify_type_info' => $authorizer_info['authorizer_info']['verify_type_info']['id'],
            'original_id' => $authorizer_info['authorizer_info']['user_name'],
            'principal_name' => $authorizer_info['authorizer_info']['principal_name'],
            'alias' => $authorizer_info['authorizer_info']['alias'],
            'is_open_store' => $authorizer_info['authorizer_info']['business_info']['open_store'],
            'is_open_scan' => $authorizer_info['authorizer_info']['business_info']['open_scan'],
            'is_open_pay' => $authorizer_info['authorizer_info']['business_info']['open_pay'],
            'is_open_card' => $authorizer_info['authorizer_info']['business_info']['open_card'],
            'is_open_shake' => $authorizer_info['authorizer_info']['business_info']['open_shake'],
            'qrcode_url' => $authorizer_info['authorizer_info']['qrcode_url'],
            'idc' => $authorizer_info['authorizer_info']['idc'],
            'signature' => $authorizer_info['authorizer_info']['signature'],
            'authorizer_appid' => $authorizer_info['authorization_info']['authorizer_appid'],
            'authorizer_access_token' => $authorization_info['authorization_info']['authorizer_access_token'],
            'authorizer_expires_time' => date('Y-m-d H:i:s',$authorization_info['authorization_info']['expires_in'] + time() - 100),
            'authorizer_refresh_token' => $authorizer_info['authorization_info']['authorizer_refresh_token'],
            'func_info' => json_encode($authorization_info['authorization_info']['func_info'], JSON_UNESCAPED_UNICODE)
        );

        //保存到数据库wechatmini_publish_info表
        $save_publish_info = array(
            'saas_account_id' => $saas_account_id,
            'authorizer_appid' => $authorizer_info['authorization_info']['authorizer_appid'],
            'original_id' => $authorizer_info['authorizer_info']['user_name'],
            'user_name' => $authorizer_info['authorizer_info']['user_name'],
        );


        $wechatmini_id = 0;

        if ($auth_status == 'updateForOld') {
            $save_data['update_authorized_at'] = date('Y-m-d H:i:s',time());
            $res = $wechatmini_model->where('id', $saas_account['wechatmini_id'])
                ->update($save_data);
            if (!$res) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }
            $wechatmini_id = $saas_account['wechatmini_id'];

        } elseif ($auth_status == 'updateForNew') {
            //删除原来的小程序
            $res = $wechatmini_model->where('id', $saas_account['wechatmini_id'])
                ->delete();
            if (!$res) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }

            //删除原来小程序的wechatmini_publish_info表的配置及发布信息
            $wechatmini_publish_info_model = new  WechatminiPublishInfo();
            $wechatmini_publish_info = $wechatmini_publish_info_model->where('saas_account_id', $saas_account_id)
                ->find();
            if ($wechatmini_publish_info) {
                $update = $wechatmini_publish_info->delete();
                if (!$update) {
                    \DB::rollBack();
                    throw new \Exception('遇到错误了,请重新操作!');
                }
            }
        }

        if ($auth_status == 'new' || $auth_status == 'updateForNew') {  // 新的授权

            //保存到数据库wechatmini表
            $save_data['authorized_at'] = date('Y-m-d H:i:s',time());
            $res = $wechatmini_model->create($save_data);

            if (!$res) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }

            //保存到数据库wechatmini_publish_info表
            $wechatmini_publish_info_model = new WechatminiPublishInfo();
            $update_publish_info = $wechatmini_publish_info_model->create($save_publish_info);

            if (!$update_publish_info) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }

            //更新saas_account表
            $save_saas_account_data = [
                'wechatmini_id' => $res->id,
                'wechatmini_name' => $res->nickname
            ];

            $saas_account_model = new \App\api\Model\SaasAccount();
            $update = $saas_account_model->where('id', $saas_account_id)
                ->update($save_saas_account_data);
            if (!$update) {
                DB::rollBack();
                throw new \Exception('遇到错误了,请重新操作!');
            }
            $wechatmini_id = $res->id;
        }

        DB::commit();

        //队列异步处理下载头像
        $job = 'app\api\jobs\DownloadWechatminiHeadImg';
        $jobData = [
            'url' => $authorizer_info['authorizer_info']['head_img'],
            'wechatmini_id' => $wechatmini_id
        ];
        Queue::push($job , $jobData);


        //队列异步处理小程序二维码
        $job = 'app\api\jobs\DownloadWechatminiQrcode';
        $jobData = [
            'url' => $authorizer_info['authorizer_info']['qrcode_url'],
            'wechatmini_id' => $wechatmini_id
        ];
        Queue::push($job , $jobData);

        return true;
    }

    /**
     *  第三方平台授权方取消授权
     */
    public static function componentUnauthorized($data)
    {
        //查询对应的授权信息
        $wechatmini_model = new Wechatmini();
        $auth_info = $wechatmini_model->where('authorizer_appid', $data['AuthorizerAppid'])
            ->find();
        if (!$auth_info) {
            throw new \Exception('没有相应的信息!');
        }

        DB::startTrans();
        //更新操作
        $save_data = [
            'unauthorized_at' => date('Y-m-d H:i:s',time()),
            'delete_time' => date('Y-m-d H:i:s',time()),
        ];
        $update = $wechatmini_model->where('id', $auth_info->id)
            ->update($save_data);
        if (!$update) {
            DB::rollBack();
            throw new \Exception('数据库错误!');
        }

        $saas_account_model = new SaasAccount();
        $update = $saas_account_model->where('id', $auth_info->saas_account_id)
            ->update(['wechatmini_id' => 0, 'wechatmini_name' => '']);
        if (!$update) {
            DB::rollBack();
            throw new \Exception('数据库错误!');
        }

        $wechatmini_publish_info_model = new WechatminiPublishInfo();
        $update = $wechatmini_publish_info_model->where('saas_account_id', $auth_info->saas_account_id)
            ->delete();
        if (!$update) {
            DB::rollBack();
            throw new \Exception('数据库错误!');
        }
        DB::commit();

        return true;
    }

    /**
     *  第三方平台授权方更新授权
     */
    public static function componentUpdateAuthorized($data)
    {
        //查询对应的授权信息
        $wechatmini_model = new Wechatmini();
        $auth_info = $wechatmini_model->where('authorizer_appid', $data['AuthorizerAppid'])
            ->find();
        if (!$auth_info) {
            throw new \Exception('没有相应的信息!');
        }

        //获取用户信息
        $saas_account_model = new SaasAccount();
        $saas_account = $saas_account_model->findOrFail($auth_info->saas_account_id)->toArray();
        if (!$saas_account) {
            throw new \Exception('遇到错误了,请重新操作!');
        }

        self::updateComponentAuthorizerInfo($data['AuthorizationCode'], $saas_account);
    }


}