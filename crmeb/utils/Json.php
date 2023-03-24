<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\utils;


use think\facade\Config;
use think\facade\Lang;
use think\Log;
use think\Response;

/**
 * Json输出类
 * Class Json
 * @package crmeb\utils
 */
class Json
{
    private $code = 200;

    public function code(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * 压缩数据
     * @param $str
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    protected function compress($str)
    {
        return base64_encode(gzdeflate(json_encode($str, JSON_UNESCAPED_UNICODE), 9));
    }

    /**
     * @param int $status
     * @param string $msg
     * @param array|null $data
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    public function make(int $status, string $msg, ?array $data = null): Response
    {
        $request = app()->request;
        $res = compact('status', 'msg');

        if (!is_null($data)) {

            $jsonData = json_encode($data);
            //在debug关闭的时候返回压缩数据

            $compressData = null;
            if (strstr('/' . app()->request->rule()->getRule(), '/api/') !== false && strlen($jsonData) > 1024 && app()->config->get('cache.is_gzde', false)) {
                $compressData = $this->compress($data);
                $res['gzde'] = 1;
            }

            $res['data'] = $compressData ?: $data;

        }

        if ($res['msg'] && !is_numeric($res['msg'])) {
            if (!$range = $request->get('lang')) {
                $range = $request->cookie(Config::get('lang.cookie_var'));
            }
            $langData = array_values(Config::get('lang.accept_language', []));
            if (!in_array($range, $langData)) {
                $range = 'zh-cn';
            }
            $res['msg'] = Lang::get($res['msg'], [], $range);
        }

        //记录原始数据
        $response = $res;
        $response['data'] = $data;
        response_log_write((array)$res, Log::INFO);

        return Response::create($res, 'json', $this->code);
    }

    public function success($msg = 'ok', ?array $data = null): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg = 'ok';
        }

        return $this->make(200, $msg, $data);
    }

    public function successful(...$args): Response
    {
        return $this->success(...$args);
    }

    public function fail($msg = 'fail', ?array $data = null): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg = 'ok';
        }

        return $this->make(400, $msg, $data);
    }

    public function status($status, $msg = 'ok', $result = [])
    {
        $status = strtoupper($status);
        if (is_array($msg)) {
            $result = $msg;
            $msg = 'ok';
        }
        return $this->success($msg, compact('status', 'result'));
    }
}
