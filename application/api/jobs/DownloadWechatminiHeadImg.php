<?php

namespace app\api\jobs;

use think\queue\Job;
use app\api\model\Wechatmini;

class DownloadWechatminiHeadImg
{

    /**
     * fire方法是消息队列默认调用的方法
     * @param Job $job 当前的任务对象
     * @param array|mixed $data 发布任务时自定义的数据
     */
    public function fire(Job $job, $data)
    {

        $isJobDone = $this->doJob($data);
        if ($isJobDone) {
            //如果任务执行成功， 记得删除任务
            $job->delete();
        } else {
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                $job->delete();
                // 也可以重新发布这个任务
                //$job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
            }
        }

    }

    /**
     * 根据消息中的数据进行实际的业务处理
     * @param array|mixed $data 发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function doJob($data)
    {
        // 根据消息中的数据进行实际的业务处理...
        $contents = $this->http_get($data['url']);

        if ($contents) {
            $file_name = md5(time() . rand(10000, 99999)) . '.png';
            $headimg = 'uploads/wechatmini/headimg/' . $file_name;
            if(!file_exists(dirname($headimg))){
                mkdir(dirname($headimg), 0777, true);
            }
            file_put_contents($headimg, $contents);

            $wechatmini_model = new Wechatmini();
            $update = $wechatmini_model->where('id', '=', $data['wechatmini_id'])
                ->update(['headimg' => $headimg]);

            if (!$update) {
                throw new \Exception('修改数据库错误!');
            }

        } else {
            throw new \Exception('头像下载失败!');
        }
        return true;
    }

    /**
     * GET 请求
     * @param string $url
     */
    public function http_get($url, $timeout = 0, $headers = array())
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

        $user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        curl_setopt($oCurl, CURLOPT_USERAGENT, $user_agent);

        if (!empty($timeout)) {
            curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeout);   //秒
        }

        if (!empty($headers)) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        }

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

}