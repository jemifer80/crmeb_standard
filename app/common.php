<?php

use crmeb\services\UploadService;
use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\ClickWordCaptchaService;
use Fastknife\Service\Service;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Log;

if (!function_exists('get_tree_value')) {
    /**
     * 获取
     * @param array $data
     * @param int|string $value
     * @return array
     */
    function get_tree_value(array $data, $value, array &$childrenValue = [])
    {
        foreach ($data as &$item) {
            if ($item['value'] == $value) {
                $childrenValue[] = $item['value'];
                if ($item['pid']) {
                    $value = $item['pid'];
                    unset($item);
                    return get_tree_value($data, $value, $childrenValue);
                }
            }
        }
        return $childrenValue;
    }
}

if (!function_exists('is_brokerage_statu')) {

    /**
     * 是否能成为推广人
     * @param float $price
     * @return bool
     */
    function is_brokerage_statu(float $price)
    {
        if (!sys_config('brokerage_func_status')) {
            return false;
        }
        $storeBrokerageStatus = sys_config('store_brokerage_statu', 1);
        if ($storeBrokerageStatus == 1) {
            return false;
        } else if ($storeBrokerageStatus == 2) {
            return false;
        } else {
            $storeBrokeragePrice = sys_config('store_brokerage_price', 0);
            return $price >= $storeBrokeragePrice;
        }
    }
}

if (!function_exists('time_tran')) {
    /**
     * 时间戳人性化转化
     * @param $time
     * @return string
     */
    function time_tran($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c . $v . '前';
            }
        }
    }
}

if (!function_exists('url_to_path')) {
    /**
     * url转换路径
     * @param $url
     * @return string
     */
    function url_to_path($url)
    {
        $path = trim(str_replace('/', DS, $url), DS);
        if (0 !== strripos($path, 'public'))
            $path = 'public' . DS . $path;
        return app()->getRootPath() . $path;
    }
}

if (!function_exists('path_to_url')) {
    /**
     * 路径转url路径
     * @param $path
     * @return string
     */
    function path_to_url($path)
    {
        return trim(str_replace(DS, '/', $path), '.');
    }
}

if (!function_exists('get_image_thumb')) {
    /**
     * 获取缩略图
     * @param $filePath
     * @param string $type all|big|mid|small
     * @param bool $is_remote_down
     * @return mixed|string|string[]
     */
    function get_image_thumb($filePath, string $type = 'all', bool $is_remote_down = false)
    {
        if (!$filePath || !is_string($filePath) || strpos($filePath, '?') !== false) return $filePath;
        try {
            $upload = UploadService::getOssInit($filePath, $is_remote_down);
            $data = $upload->thumb('', $type);
            $image = $type == 'all' ? $data : $data[$type] ?? $filePath;
        } catch (\Throwable $e) {
            $image = $filePath;
            //            throw new ValidateException($e->getMessage());
            \think\facade\Log::error('获取缩略图失败，原因：' . $e->getMessage() . '----' . $e->getFile() . '----' . $e->getLine() . '----' . $filePath);
        }
        $data = parse_url($image);
        if (!isset($data['host']) && (substr($image, 0, 2) == './' || substr($image, 0, 1) == '/')) {//不是完整地址
            $image = sys_config('site_url') . $image;
        }
        //请求是https 图片是http 需要改变图片地址
        if (strpos(request()->domain(), 'https:') !== false && strpos($image, 'https:') === false) {
            $image = str_replace('http:', 'https:', $image);
        }
        return $image;
    }
}

if (!function_exists('get_thumb_water')) {
    /**
     * 处理数组获取缩略图、水印
     * @param $list
     * @param string $type
     * @param array|string[] $field 1、['image','images'] type 取值参数:type 2、['small'=>'image','mid'=>'images'] type 取field数组的key
     * @param bool $is_remote_down
     * @return array|mixed|string|string[]
     */
    function get_thumb_water($list, string $type = 'small', array $field = ['image'], bool $is_remote_down = false)
    {
        if (!$list || !$field) return $list;
        $baseType = $type;
        $data = $list;
        if (is_string($list)) {
            $field = [$type => 'image'];
            $data = ['image' => $list];
        }
        if (is_array($data)) {
            foreach ($field as $type => $key) {
                if (is_integer($type)) {//索引数组，默认type
                    $type = $baseType;
                }
                //一维数组
                if (isset($data[$key])) {
                    if (is_array($data[$key])) {
                        $path_data = [];
                        foreach ($data[$key] as $k => $path) {
                            $path_data[] = get_image_thumb($path, $type, $is_remote_down);
                        }
                        $data[$key] = $path_data;
                    } else {
                        $data[$key] = get_image_thumb($data[$key], $type, $is_remote_down);
                    }
                } else {
                    foreach ($data as &$item) {
                        if (!isset($item[$key]))
                            continue;
                        if (is_array($item[$key])) {
                            $path_data = [];
                            foreach ($item[$key] as $k => $path) {
                                $path_data[] = get_image_thumb($path, $type, $is_remote_down);
                            }
                            $item[$key] = $path_data;
                        } else {
                            $item[$key] = get_image_thumb($item[$key], $type, $is_remote_down);
                        }
                    }
                }
            }
        }
        return is_string($list) ? ($data['image'] ?? '') : $data;
    }
}
if (!function_exists('put_image')) {
    /**
     * 获取图片转为base64
     * @param string $avatar
     * @return bool|string
     */
    function put_image($url, $filename = '')
    {

        if ($url == '') {
            return false;
        }
        try {
            if ($filename == '') {

                $ext = pathinfo($url);
                if ($ext['extension'] != "jpg" && $ext['extension'] != "png" && $ext['extension'] != "jpeg") {
                    return false;
                }
                $filename = time() . "." . $ext['extension'];
            }

            //文件保存路径
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
            $path = 'uploads/qrcode';
            $fp2 = fopen(public_path() . $path . '/' . $filename, 'a');
            fwrite($fp2, $img);
            fclose($fp2);
            return $path . '/' . $filename;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('make_path')) {

    /**
     * 上传路径转化,默认路径
     * @param $path
     * @param int $type
     * @param bool $force
     * @return string
     */
    function make_path($path, int $type = 2, bool $force = false)
    {
        $path = DS . ltrim(rtrim($path));
        switch ($type) {
            case 1:
                $path .= DS . date('Y');
                break;
            case 2:
                $path .= DS . date('Y') . DS . date('m');
                break;
            case 3:
                $path .= DS . date('Y') . DS . date('m') . DS . date('d');
                break;
        }
        try {
            if (is_dir(app()->getRootPath() . 'public' . DS . 'uploads' . $path) == true || mkdir(app()->getRootPath() . 'public' . DS . 'uploads' . $path, 0777, true) == true) {
                return trim(str_replace(DS, '/', $path), '.');
            } else return '';
        } catch (\Exception $e) {
            if ($force)
                throw new \Exception($e->getMessage());
            return '无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS;
        }

    }
}

if (!function_exists('check_phone')) {
    /**
     * 手机号验证
     * @param $phone
     * @return false|int
     */
    function check_phone($phone)
    {
        return preg_match("/^1[3456789]\d{9}$/", $phone);
    }
}

if (!function_exists('check_mail')) {
    /**
     * 邮箱验证
     * @param $mail
     * @return false|int
     */
    function check_mail($mail)
    {
        if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('aj_captcha_check_one')) {
    /**
     * 验证滑块1次验证
     * @param string $token
     * @param string $pointJson
     * @return bool
     */
    function aj_captcha_check_one(string $captchaType, string $token, string $pointJson)
    {
        aj_get_serevice($captchaType)->check($token, $pointJson);
        return true;
    }
}

if (!function_exists('aj_captcha_check_two')) {
    /**
     * 验证滑块2次验证
     * @param string $token
     * @param string $pointJson
     * @return bool
     */
    function aj_captcha_check_two(string $captchaType, string $captchaVerification)
    {
        aj_get_serevice($captchaType)->verificationByEncryptCode($captchaVerification);
        return true;
    }
}


if (!function_exists('aj_captcha_create')) {
    /**
     * 创建验证码
     * @return array
     */
    function aj_captcha_create(string $captchaType)
    {
        return aj_get_serevice($captchaType)->get();
    }
}

if (!function_exists('aj_get_serevice')) {

    /**
     * @param string $captchaType
     * @return ClickWordCaptchaService|BlockPuzzleCaptchaService
     */
    function aj_get_serevice(string $captchaType)
    {
        $config = Config::get('ajcaptcha');
        switch ($captchaType) {
            case "clickWord":
                $service = new ClickWordCaptchaService($config);
                break;
            case "blockPuzzle":
                $service = new BlockPuzzleCaptchaService($config);
                break;
            default:
                throw new ValidateException('captchaType参数不正确！');
        }
        return $service;
    }
}

if (!function_exists('mb_substr_str')) {

    /**
     * 截取制定长度,并使用填充
     * @param string $value
     * @param int $length
     * @param string $str
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/12/1
     */
    function mb_substr_str(string $value, int $length, string $str = '...', int $type = 0)
    {
        if (mb_strlen($value) > $length) {
            $value = mb_substr($value, 0, $length - mb_strlen($str)) . $str;
        }

        //等于1时去掉数组
        if ($type === 1) {
            $value = preg_replace('/[0-9]/', '', $value);
        }

        return $value;
    }
}

if (!function_exists('response_log_write')) {

    /**
     * 日志写入
     * @param array $data
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/12/2
     */
    function response_log_write(array $data, string $type = \think\Log::ERROR)
    {
        try {
            $id = 0;
            foreach (['adminId', 'kefuId', 'uid', 'supplierId'] as $value) {
                if (request()->hasMacro($value)) {
                    $id = request()->{$value}();
                }
            }

            //日志内容
            $log = [
                $id,//管理员ID
                request()->ip(),//客户ip
                ceil(msectime() - (request()->time(true) * 1000)),//耗时（毫秒）
                request()->method(true),//请求类型
                str_replace("/", "", request()->rootUrl()),//应用
                request()->baseUrl(),//路由
                json_encode(request()->param(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),//请求参数
                json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),//报错数据
            ];

            Log::write(implode("|", $log), $type);
        } catch (\Throwable $e) {

        }
    }
}
