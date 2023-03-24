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

namespace app\services;

use crmeb\utils\JwtAuth;
use think\facade\Db;
use think\facade\Config;
use think\facade\Route as Url;

/**
 * Class BaseServices
 * @package app\services
 */
abstract class BaseServices
{
    /**
     * 模型注入
     * @var object
     */
    protected $dao;

    /**
     * 获取分页配置
     * @param bool $isPage
     * @param bool $isRelieve
     * @return int[]
     */
    public function getPageValue(bool $isPage = true, bool $isRelieve = true)
    {
        $page = $limit = 0;
        if ($isPage) {
            $page = app()->request->param(Config::get('database.page.pageKey', 'page') . '/d', 0);
            $limit = app()->request->param(Config::get('database.page.limitKey', 'limit') . '/d', 0);
        }
        $limitMax = Config::get('database.page.limitMax');
        $defaultLimit = Config::get('database.page.defaultLimit', 10);
        if ($limit > $limitMax && $isRelieve) {
            $limit = $limitMax;
        }
        return [(int)$page, (int)$limit, (int)$defaultLimit];
    }

    /**
     * 数据库事务操作
     * @param callable $closure
     * @return mixed
     */
    public function transaction(callable $closure, bool $isTran = true)
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    /**
     * 创建token
     * @param int $id
     * @param $type
     * @return array
     */
    public function createToken(int $id, $type, string $pwd = '', Array $extra = [])
    {
        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        return $jwtAuth->createToken($id, $type, array_replace(['auth' => md5($pwd)], $extra));
    }

    /**
     * 获取路由地址
     * @param string $path
     * @param array $params
     * @param bool $suffix
     * @return \think\route\Url
     */
    public function url(string $path, array $params = [], bool $suffix = false, bool $isDomain = false)
    {
        return Url::buildUrl($path, $params)->suffix($suffix)->domain($isDomain)->build();
    }

    /**
     * 密码hash加密
     * @param string $password
     * @return false|string|null
     */
    public function passwordHash(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * 格式化时间
     * @param $time
     * @param bool $is_time_key
     * @return array
     */
    public function timeHandle($time, bool $is_time_key = false)
    {
        switch ($time) {
            case 'today':
                $start = date('Y-m-d 00:00:00');
                $end = date('Y-m-d 23:59:59');
                break;
            case 'yesterday':
                $start = date('Y-m-d 00:00:00', strtotime("-1 day"));
                $end = date('Y-m-d 23:59:59', strtotime("-1 day"));
                break;
            case 'sevenday':
                $start = date('Y-m-d 00:00:00', strtotime('-6 day'));
                $end = date('Y-m-d 23:59:59');
                break;
            case 'thirtyday':
                $start = date('Y-m-d 00:00:00', strtotime('-29 day'));
                $end = date('Y-m-d 23:59:59');
                break;
            case 'month':
                $start = date('Y-m-01 00:00:00');
                $end = date('Y-m-d 23:59:59', mktime(23, 59, 59, date('m'), date('t'), date('Y')));
                break;
            case 'year':
                $start = date('Y-01-01 00:00:00');
                $end = date('Y-12-31 23:59:59');
                break;
            default:
                $start = date("Y/m/d", strtotime("-30 days", time()));
                $end = date("Y/m/d", time());
                if (strstr($time, '-') !== false) {
                    [$start, $end] = explode('-', $time);
                    if ($start || $end) {
                        $end_time = strtotime($end);
                        //没选具体时分秒 加上86400
                        if ($end_time == strtotime(date('Y/m/d', $end_time))) {
                            $end = date('Y/m/d H:i:s', $end_time + 86399);
                        }
                    }
                }
                break;
        }
        $start = strtotime($start);
        $end = strtotime($end);
        if ($is_time_key) {
            $dayCount = ceil(($end - $start) / 86400);
            $s_start = $start;
            $timeKey = [];
            if ($dayCount == 1) {
                $timeType = 'hour';
                $timeKey = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
            } elseif ($dayCount <= 31) {
                $timeType = 'day';
                for ($i = 0; $i < $dayCount; $i++) {
                    $timeKey[] = date('m-d', $s_start);
                    $s_start = strtotime("+1 day", $s_start);
                }
            } elseif ($dayCount <= 92) {
                $timeType = 'weekly';
                for ($i = 0; $i < $dayCount; $i = $i + 7) {
                    $timeKey[] = '第' . date('W', $s_start) . '周';
                    $s_start = strtotime("+1 week", $s_start);
                }
            } else {
                $timeType = 'year';
                while ($s_start <= $end) {
                    $timeKey[] = date('Y-m', $s_start);
                    $s_start = strtotime("+1 month", $s_start);
                }
            }
            return [$start, $end, $timeType, $timeKey];
        }
        return [$start, $end];
    }

    /**
     * 计算环比增长率
     * @param $nowValue
     * @param $lastValue
     * @return float|int|string
     */
    public function countRate($nowValue, $lastValue)
    {
        if ($lastValue == 0 && $nowValue == 0) return 0;
        if ($lastValue == 0) return round(bcmul(bcdiv($nowValue, 1, 4), 100, 2), 2);
        if ($nowValue == 0) return -round(bcmul(bcdiv($lastValue, 1, 4), 100, 2), 2);
        return bcmul(bcdiv((bcsub($nowValue, $lastValue, 2)), $lastValue, 4), 100, 2);
    }

    /**
     * tree处理 分类、标签数据(这一类数据)
     * @param array $cate
     * @param array $label
     * @return array
     */
    public function get_tree_children(array $cate, array $label)
    {
        if ($cate) {
            foreach ($cate as $key => $value) {
                if ($label) {
                    foreach ($label as $k => $item) {
                        if ($value['id'] == $item['label_cate']) {
                            $cate[$key]['children'][] = $item;
                            unset($label[$k]);
                        }
                    }
                } else {
                    $cate[$key]['children'] = [];
                }
            }
        }
        return $cate;
    }

    /**
     * ip转城市
     * @param $ip
     * @return array|string|string[]|null
     */
    public function convertIp($ip)
    {
        try {
            $ip1num = 0;
            $ip2num = 0;
            $ipAddr1 = "";
            $ipAddr2 = "";
            $dat_path = public_path() . 'statics/ip.dat';
            if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
                return '';
            }
            if (!$fd = @fopen($dat_path, 'rb')) {
                return '';
            }
            $ip = explode('.', $ip);
            $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
            $DataBegin = fread($fd, 4);
            $DataEnd = fread($fd, 4);
            $ipbegin = implode('', unpack('L', $DataBegin));
            if ($ipbegin < 0) $ipbegin += pow(2, 32);
            $ipend = implode('', unpack('L', $DataEnd));
            if ($ipend < 0) $ipend += pow(2, 32);
            $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
            $BeginNum = 0;
            $EndNum = $ipAllNum;
            while ($ip1num > $ipNum || $ip2num < $ipNum) {
                $Middle = intval(($EndNum + $BeginNum) / 2);
                fseek($fd, $ipbegin + 7 * $Middle);
                $ipData1 = fread($fd, 4);
                if (strlen($ipData1) < 4) {
                    fclose($fd);
                    return '';
                }
                $ip1num = implode('', unpack('L', $ipData1));
                if ($ip1num < 0) $ip1num += pow(2, 32);

                if ($ip1num > $ipNum) {
                    $EndNum = $Middle;
                    continue;
                }
                $DataSeek = fread($fd, 3);
                if (strlen($DataSeek) < 3) {
                    fclose($fd);
                    return '';
                }
                $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
                fseek($fd, $DataSeek);
                $ipData2 = fread($fd, 4);
                if (strlen($ipData2) < 4) {
                    fclose($fd);
                    return '';
                }
                $ip2num = implode('', unpack('L', $ipData2));
                if ($ip2num < 0) $ip2num += pow(2, 32);
                if ($ip2num < $ipNum) {
                    if ($Middle == $BeginNum) {
                        fclose($fd);
                        return '';
                    }
                    $BeginNum = $Middle;
                }
            }
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(1)) {
                $ipSeek = fread($fd, 3);
                if (strlen($ipSeek) < 3) {
                    fclose($fd);
                    return '';
                }
                $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
                fseek($fd, $ipSeek);
                $ipFlag = fread($fd, 1);
            }
            if ($ipFlag == chr(2)) {
                $AddrSeek = fread($fd, 3);
                if (strlen($AddrSeek) < 3) {
                    fclose($fd);
                    return '';
                }
                $ipFlag = fread($fd, 1);
                if ($ipFlag == chr(2)) {
                    $AddrSeek2 = fread($fd, 3);
                    if (strlen($AddrSeek2) < 3) {
                        fclose($fd);
                        return '';
                    }
                    $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                    fseek($fd, $AddrSeek2);
                } else {
                    fseek($fd, -1, SEEK_CUR);
                }
                while (($char = fread($fd, 1)) != chr(0))
                    $ipAddr2 .= $char;
                $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
                fseek($fd, $AddrSeek);
                while (($char = fread($fd, 1)) != chr(0))
                    $ipAddr1 .= $char;
            } else {
                fseek($fd, -1, SEEK_CUR);
                while (($char = fread($fd, 1)) != chr(0))
                    $ipAddr1 .= $char;
                $ipFlag = fread($fd, 1);
                if ($ipFlag == chr(2)) {
                    $AddrSeek2 = fread($fd, 3);
                    if (strlen($AddrSeek2) < 3) {
                        fclose($fd);
                        return '';
                    }
                    $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                    fseek($fd, $AddrSeek2);
                } else {
                    fseek($fd, -1, SEEK_CUR);
                }
                while (($char = fread($fd, 1)) != chr(0)) {
                    $ipAddr2 .= $char;
                }
            }
            fclose($fd);
            if (preg_match('/http/i', $ipAddr2)) {
                $ipAddr2 = '';
            }
            $ipaddr = $ipAddr1;
            $ipaddr = preg_replace('/CZ88.NET/is', '', $ipaddr);
            $ipaddr = preg_replace('/^s*/is', '', $ipaddr);
            $ipaddr = preg_replace('/s*$/is', '', $ipaddr);

            if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
                $ipaddr = '';
            }
            return $this->strToUtf8($ipaddr);

        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * 文字格式转utf8
     * @param $str
     * @return array|false|string|string[]|null
     */
    public function strToUtf8($str)
    {
        $encode = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        if ($encode == 'UTF-8') {
            return $str;
        } else {
            return mb_convert_encoding($str, 'UTF-8', $encode);
        }
    }

    /**
     * 处理城市数据
     * @param $address
     * @return array
     */
    public function addressHandle($address)
    {
        if ($address) {
            try {
                preg_match('/(.*?(省|自治区|北京市|天津市|上海市|重庆市|澳门特别行政区|香港特别行政区))/', $address, $matches);
                if (count($matches) > 1) {
                    $province = $matches[count($matches) - 2];
                    $address = preg_replace('/(.*?(省|自治区|北京市|天津市|上海市|重庆市|澳门特别行政区|香港特别行政区))/', '', $address, 1);
                }
                preg_match('/(.*?(市|自治州|地区|区划|县))/', $address, $matches);
                if (count($matches) > 1) {
                    $city = $matches[count($matches) - 2];
                    $address = str_replace($city, '', $address);
                }
                preg_match('/(.*?(区|县|镇|乡|街道))/', $address, $matches);
                if (count($matches) > 1) {
                    $area = $matches[count($matches) - 2];
                    $address = str_replace($area, '', $address);
                }
            } catch (\Throwable $e) {
            }
        }
        return [
            'province' => $province ?? '',
            'city' => $city ?? '',
            'district' => $area ?? '',
            "address" => $address
        ];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }
}
