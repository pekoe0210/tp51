<?php

namespace app\api\controller;

use app\api\logic\Auth;
use think\Request;
use think\Cache;
use app\api\logic\WechatMiniLogic;
use think\Log;
use think\Controller;
use think\Queue;

class WechatMiniController extends Controller
{
    /**
     *  第三方平台回调地址
     */
    public function componentNotify()
    {
        $wechat = new \Pekoe\Wechat\Wechat('component');

        $wechat->valid();

        $type = $wechat->getRev()->getRevType(); // 获取数据类型
        $data = $wechat->getRev()->getRevData(); // 获取微信服务器发来的信息

        switch ($data['InfoType']) {
            case 'component_verify_ticket' :
                $wechat->saveComponentVerifyTicket();
                break;
            case 'authorized':        //授权成功通知

                break;
            case 'updateauthorized':        //授权更新通知
                WechatMiniLogic::componentUpdateAuthorized($data);
                break;
            case 'unauthorized':        //取消授权通知
                WechatMiniLogic::componentUnauthorized($data);
                break;
            default:
                break;
        }
    }

    /**
     *  第三方平台授权小程序
     */
    public function componentAuth(Request $request)
    {
        //1.接收callback, auth_type（1,公众号;2,小程序）
//        $data = $request->param();
//        $validate = new \app\Common\validate\Wechatmini();
//        if (!$validate->check($data)) {
//            throw new \Exception($validate->getError());
//        }
        $data['callback'] = request()->domain() . '/console/index/index';
        $data['auth_type'] = 3;

        //2.获取appId,appSecret
        $wechat = new \Pekoe\Wechat\Wechat('component');
        $saasAccountId = Auth::id();

        //3.生成token,存入缓存
        $token = md5(uniqid(md5(microtime(true)), true));
        Cache::set('wechatMini:component:auth:' . $token, ['saas_account_id' => $saasAccountId], 100);

        //4.回调uri,可自定义
        $redirect_uri = request()->domain() . '/wechatmini/component/auth/notifies?state=' . urlencode(base64_encode($token)) . '&backurl=' . urlencode(base64_encode($data['callback']));

        $uri = $wechat->getComponentOAuthUri($redirect_uri, $data['auth_type']);
        if (!$uri) {
            throw new \Exception('遇到错误了!');
        }

        header('Refresh:0.5,Url=' . $uri);
        echo '正在跳转微信授权页...';
        exit;
    }

    /**
     *  第三方平台授权回调
     */
    public function componentAuthNotify(Request $request)
    {
        //1.接收state、backurl、auth_code
        $data = $request->param();

        //2.先获取身份信息
        $token = $data['state'];
        $token = base64_decode(urldecode($token));

        //3.获取缓存
        $user_info = Cache::get('wechatMini:component:auth:' . $token);

        if (!$user_info) {
            throw new \Exception('授权已过期,请重新操作!');
        }

        //4.获取用户信息
        $saasAccountId = Auth::id();
        $saas_account = \App\common\Model\SaasAccount::findOrFail($saasAccountId)->toArray();
        if (!$saas_account) {
            throw new \Exception('遇到错误了,请重新操作!');
        }

        //5.获取到授权码
        $auth_code = $data['auth_code'];
        WechatMiniLogic::updateComponentAuthorizerInfo($auth_code, $saas_account);

        Cache::rm('wechatMini:component:auth:' . $token);
        return redirect(base64_decode($data['backurl']));
    }


    public function queue(){
        $test = new DownloadWechatminiHeadImg();
        $data = [
            'url' => 'http://wx.qlogo.cn/mmopen/5RuQ7k73DpynBRibib3fNsZgUlltXHwSd69up5pQm4dHAR7kT7JX6tMm4RYwH3L9KhNYK5kUQRrvHZRR4vt8PibQw1nFUfYeDc5/0',
            'wechatmini_id' => 8,
        ];
        return $test->fire($data);
    }

    public function test(){
        $authorizer_appid = 'wxc023189819a5b6cc';
        $saasAccountId = Auth::id();
        $saas_account = \App\common\Model\SaasAccount::findOrFail($saasAccountId)->toArray();
        if (!$saas_account) {
            throw new \Exception('遇到错误了,请重新操作!');
        }
        $authorization_info = [
            'authorization_info' => [
                'authorizer_appid' => 'wxc023189819a5b6cc',
                'authorizer_access_token' => '15_bw8D7rEna6gWt3MuKKleJUT4MtP1xepULK95IriK5lX5hKnRSub4IykqTrO2KUf-Jf4zCRMcnTFaWJa3X2PGR3oa5RKBZR7o2aQkl_oN123RZGoSwSNHv8t3h3vj7ljg9lpp1HXdiupeuljgRJKcADDLDE',
                'expires_in' => 7200,
                'authorizer_refresh_token' => 'refreshtoken@@@tHyLC9zV3VwGDPDqV7rDSCyfaAiHae73zcfEadFxXGA',
                'func_info' => '',
            ]

        ];
        $authorizer_info = [
            'authorizer_info' => [
                'nick_name' => '远义测试',
                'head_img' => 'http://wx.qlogo.cn/mmopen/5RuQ7k73DpynBRibib3fNsZgUlltXHwSd69up5pQm4dHAR7kT7JX6tMm4RYwH3L9KhNYK5kUQRrvHZRR4vt8PibQw1nFUfYeDc5/0',
                'service_type_info' => [
                    'id' => 0,
                ],
                'verify_type_info' => [
                    'id' => -1,
                ],
                'user_name' => 'gh_90c1c2a06ee9',
                'alias' => '',
                'qrcode_url' => 'http://mmbiz.qpic.cn/mmbiz_jpg/8bHnf6ZjJAZNia1pEMvWsrrM1kKiaTJOJBrvpF7BddFHnuXqIqvB9ib4DtN461yHiaNLdMCcvs6cNWrU9IE0PZibAcw/0',
                'business_info' => [
                    'open_pay' => 0,
                    'open_shake' => 0,
                    'open_scan' => 0,
                    'open_card' => 0,
                    'open_store' => 0,
                ],
                'idc' => 1,
                'principal_name' => '个人',
                'signature' => '远义测试',
                'MiniProgramInfo' => [
                    'network' => [
                        'RequestDomain' => [],
                        'WsRequestDomain' => [],
                        'UploadDomain' => [],
                        'DownloadDomain' => [],
                        'BizDomain' => [],
                    ],
                    'categories' => [],
                    'visit_status' => 0,
                ],
            ],
            'authorization_info' => [
                'authorizer_appid' => 'wxc023189819a5b6cc',
                'authorizer_refresh_token' => 'refreshtoken@@@tHyLC9zV3VwGDPDqV7rDSCyfaAiHae73zcfEadFxXGA',
                'func_info' => '',
            ]
        ];
        WechatMiniLogic::updateMiniAppComponentAuthorizerInfo($authorizer_appid, $saasAccountId, $saas_account, $authorization_info, $authorizer_info);
    }

}
