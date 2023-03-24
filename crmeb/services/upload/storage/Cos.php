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
namespace crmeb\services\upload\storage;

use crmeb\basic\BaseUpload;
use crmeb\exceptions\UploadException;
use GuzzleHttp\Psr7\Utils;
use think\exception\ValidateException;
use QCloud\COSSTS\Sts;
use crmeb\services\upload\extend\cos\Client as CrmebClient;

/**
 * 腾讯云COS文件上传
 * Class COS
 * @package crmeb\services\upload\storage
 */
class Cos extends BaseUpload
{

    /**
     * 应用id
     * @var string
     */
    protected $appid;

    /**
     * accessKey
     * @var mixed
     */
    protected $accessKey;

    /**
     * secretKey
     * @var mixed
     */
    protected $secretKey;

    /**
     * 句柄
     * @var CrmebClient
     */
    protected $handle;

    /**
     * 空间域名 Domain
     * @var mixed
     */
    protected $uploadUrl;

    /**
     * 存储空间名称  公开空间
     * @var mixed
     */
    protected $storageName;

    /**
     * COS使用  所属地域
     * @var mixed|null
     */
    protected $storageRegion;

    /**
     * 水印位置
     * @var string[]
     */
    protected $position = [
        '1' => 'northwest',//：左上
        '2' => 'north',//：中上
        '3' => 'northeast',//：右上
        '4' => 'west',//：左中
        '5' => 'center',//：中部
        '6' => 'east',//：右中
        '7' => 'southwest',//：左下
        '8' => 'south',//：中下
        '9' => 'southeast',//：右下
    ];

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->accessKey = $config['accessKey'] ?? null;
        $this->secretKey = $config['secretKey'] ?? null;
        $this->appid = $config['appid'] ?? null;
        $this->uploadUrl = $this->checkUploadUrl($config['uploadUrl'] ?? '');
        $this->storageName = $config['storageName'] ?? null;
        $this->storageRegion = $config['storageRegion'] ?? null;
        $this->waterConfig['watermark_text_font'] = 'simfang仿宋.ttf';
    }

    /**
     *
     * @return CrmebClient
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/9/29
     */
    protected function app()
    {
        $this->handle = new CrmebClient([
            'accessKey' => $this->accessKey,
            'secretKey' => $this->secretKey,
            'region' => $this->storageRegion ?: 'ap-chengdu',
            'bucket' => $this->storageName,
            'appid' => $this->appid,
            'uploadUrl' => $this->uploadUrl
        ]);
        return $this->handle;
    }

    /**
     * 上传文件
     * @param string|null $file
     * @param bool $isStream 是否为流上传
     * @param string|null $fileContent 流内容
     * @return array|bool|\StdClass
     */
    protected function upload(string $file = null, bool $isStream = false, string $fileContent = null)
    {
        if (!$isStream) {
            $fileHandle = app()->request->file($file);
            if (!$fileHandle) {
                return $this->setError('Upload file does not exist');
            }
            if ($this->validate) {
                try {
                    $error = [
                        $file . '.filesize' => 'Upload filesize error',
                        $file . '.fileExt' => 'Upload fileExt error',
                        $file . '.fileMime' => 'Upload fileMine error'
                    ];
                    validate([$file => $this->validate], $error)->check([$file => $fileHandle]);
                } catch (ValidateException $e) {
                    return $this->setError($e->getMessage());
                }
            }
            $key = $this->saveFileName($fileHandle->getRealPath(), $fileHandle->getOriginalExtension());
            $body = fopen($fileHandle->getRealPath(), 'rb');
            $body = (string)Utils::streamFor($body);
        } else {
            $key = $file;
            $body = $fileContent;
        }
        try {
            $key = $this->getUploadPath($key);
            $this->fileInfo->uploadInfo = $this->app()->putObject($key, $body);
            $this->fileInfo->filePath = $this->uploadUrl . '/' . $key;
            $this->fileInfo->realName = isset($fileHandle) ? $fileHandle->getOriginalName() : $key;
            $this->fileInfo->fileName = $key;
            $this->fileInfo->filePathWater = $this->water($this->fileInfo->filePath);
            $this->authThumb && $this->thumb($this->fileInfo->filePath);
            return $this->fileInfo;
        } catch (UploadException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 文件流上传
     * @param string $fileContent
     * @param string|null $key
     * @return array|bool|mixed|\StdClass
     */
    public function stream(string $fileContent, string $key = null)
    {
        if (!$key) {
            $key = $this->saveFileName();
        }
        return $this->upload($key, true, $fileContent);
    }

    /**
     * 文件上传
     * @param string $file
     * @return array|bool|mixed|\StdClass
     */
    public function move(string $file = 'file')
    {
        return $this->upload($file);
    }

    /**
     * 缩略图
     * @param string $filePath
     * @param string $type
     * @return mixed|string[]
     */
    public function thumb(string $filePath = '', string $type = 'all')
    {
        $filePath = $this->getFilePath($filePath);
        $data = ['big' => $filePath, 'mid' => $filePath, 'small' => $filePath];
        $this->fileInfo->filePathBig = $this->fileInfo->filePathMid = $this->fileInfo->filePathSmall = $this->fileInfo->filePathWater = $filePath;
        if ($filePath) {
            $config = $this->thumbConfig;
            foreach ($this->thumb as $v) {
                if ($type == 'all' || $type == $v) {
                    $height = 'thumb_' . $v . '_height';
                    $width = 'thumb_' . $v . '_width';
                    if (isset($config[$height]) && isset($config[$width]) && $config[$height] && $config[$width]) {
                        $key = 'filePath' . ucfirst($v);
                        $this->fileInfo->$key = $filePath . '?imageMogr2/thumbnail/' . $config[$width] . 'x' . $config[$height];
                        $this->fileInfo->$key = $this->water($this->fileInfo->$key);
                        $data[$v] = $this->fileInfo->$key;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 水印
     * @param string $filePath
     * @return mixed|string
     */
    public function water(string $filePath = '')
    {
        $filePath = $this->getFilePath($filePath);
        $waterConfig = $this->waterConfig;
        $waterPath = $filePath;
        if ($waterConfig['image_watermark_status'] && $filePath) {
            if (strpos($filePath, '?') === false) {
                $filePath .= '?watermark';
            } else {
                $filePath .= '&watermark';
            }
            switch ($waterConfig['watermark_type']) {
                case 1://图片
                    if (!$waterConfig['watermark_image']) {
                        throw new ValidateException('请先配置水印图片');
                    }
                    $waterPath = $filePath .= '/1/image/' . base64_encode($waterConfig['watermark_image']) . '/gravity/' . ($this->position[$waterConfig['watermark_position']] ?? 'northwest') . '/blogo/1/dx/' . $waterConfig['watermark_x'] . '/dy/' . $waterConfig['watermark_y'];
                    break;
                case 2://文字
                    if (!$waterConfig['watermark_text']) {
                        throw new ValidateException('请先配置水印文字');
                    }
                    $waterPath = $filePath .= '/2/text/' . base64_encode($waterConfig['watermark_text']) . '/font/' . base64_encode($waterConfig['watermark_text_font']) . '/fill/' . base64_encode($waterConfig['watermark_text_color']) . '/fontsize/' . $waterConfig['watermark_text_size'] . '/gravity/' . ($this->position[$waterConfig['watermark_position']] ?? 'northwest') . '/dx/' . $waterConfig['watermark_x'] . '/dy/' . $waterConfig['watermark_y'];
                    break;
            }
        }
        return $waterPath;
    }

    /**
     * 获取视频封面图
     * @param string $filePath
     * @param string $type
     * @param int $time
     * @return array
     */
    public function videoCoverImage(string $filePath = '', string $type = 'all', int $time = 1)
    {
        $data = ['big' => $filePath, 'mid' => $filePath, 'small' => $filePath];
        $this->fileInfo->filePathBig = $this->fileInfo->filePathMid = $this->fileInfo->filePathSmall = $this->fileInfo->filePathWater = $filePath;
        if ($filePath) {
            //?ci-process=snapshot
            foreach ($this->thumb as $v) {
                if ($type == 'all' || $type == $v) {
                    $height = 600;
                    $width = 400;
                    $key = 'filePath' . ucfirst($v);
                    $this->fileInfo->$key = $filePath . 'ci-process=snapshot，t_' . $time . ',f_jpg,w_' . $width . ',h_' . $height . ',m_fast';
                }
            }
        }
        return $data;
    }

    /**
     *  删除资源
     * @param $key
     * @return mixed
     */
    public function delete(string $filePath)
    {
        try {
            return $this->app()->deleteObject($this->storageName, $filePath);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 生成签名
     * @return array|mixed
     * @throws \Exception
     */
    public function getTempKeys()
    {
        $sts = new Sts();
        $config = [
            'url' => 'https://sts.tencentcloudapi.com/',
            'domain' => 'sts.tencentcloudapi.com',
            'proxy' => '',
            'secretId' => $this->accessKey, // 固定密钥
            'secretKey' => $this->secretKey, // 固定密钥
            'bucket' => $this->storageName, // 换成你的 bucket
            'region' => $this->storageRegion, // 换成 bucket 所在园区
            'durationSeconds' => 1800, // 密钥有效期
            'allowPrefix' => '*', // 这里改成允许的路径前缀，可以根据自己网站的用户登录态判断允许上传的具体路径，例子： a.jpg 或者 a/* 或者 * (使用通配符*存在重大安全风险, 请谨慎评估使用)
            // 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
            'allowActions' => [
                // 简单上传
                'name/cos:PutObject',
                'name/cos:PostObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload'
            ]
        ];
        // 获取临时密钥，计算签名
        $result = $sts->getTempKeys($config);
        $result['url'] = $this->uploadUrl . '/';
        $result['type'] = 'COS';
        $result['bucket'] = $this->storageName;
        $result['region'] = $this->storageRegion;
        return $result;
    }

    /**
     * 计算临时密钥用的签名
     * @param $opt
     * @param $key
     * @param $method
     * @param $config
     * @return string
     */
    public function getSignature($opt, $key, $method, $config)
    {
        $formatString = $method . $config['domain'] . '/?' . $this->json2str($opt, 1);
        $sign = hash_hmac('sha1', $formatString, $key);
        $sign = base64_encode($this->_hex2bin($sign));
        return $sign;
    }

    public function _hex2bin($data)
    {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }

    // obj 转 query string
    public function json2str($obj, $notEncode = false)
    {
        ksort($obj);
        $arr = array();
        if (!is_array($obj)) {
            return $this->setError($obj . " must be a array");
        }
        foreach ($obj as $key => $val) {
            array_push($arr, $key . '=' . ($notEncode ? $val : rawurlencode($val)));
        }
        return join('&', $arr);
    }

    // v2接口的key首字母小写，v3改成大写，此处做了向下兼容
    public function backwardCompat($result)
    {
        if (!is_array($result)) {
            return $this->setError($result . " must be a array");
        }
        $compat = array();
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $compat[lcfirst($key)] = $this->backwardCompat($value);
            } elseif ($key == 'Token') {
                $compat['sessionToken'] = $value;
            } else {
                $compat[lcfirst($key)] = $value;
            }
        }
        return $compat;
    }

    /**
     * 桶列表
     * @param string|null $region
     * @param bool $line
     * @param bool $shared
     * @return array|mixed
     *  "Name" => "record-1254950941"
     * "Location" => "ap-chengdu"
     * "CreationDate" => "2019-05-16T08:33:29Z"
     * "BucketType" => "cos"
     */
    public function listbuckets(string $region = null, bool $line = false, bool $shared = false)
    {
        try {
            $res = $this->app()->listBuckets();
            $bucket = [];
            if (isset($res['Buckets'][0]['Bucket'])) {
                if (isset($res['Buckets'][0]['Bucket']['Name'])) {
                    $bucket[] = $res['Buckets'][0]['Bucket'];
                } else {
                    $bucket = $res['Buckets'][0]['Bucket'];
                }
            } else if (isset($res['Buckets']['Bucket'])) {
                $bucket = $res['Buckets']['Bucket'];
            }
            return $bucket;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 创建桶
     * @param string $name
     * @param string $region
     * @param string $acl public-read=公共独写
     * @return bool|mixed
     */
    public function createBucket(string $name, string $region = '', string $acl = 'public-read')
    {
        $regionData = $this->getRegion();
        $regionData = array_column($regionData, 'value');
        if (!in_array($region, $regionData)) {
            return $this->setError('COS:无效的区域!');
        }
        $this->storageRegion = $region;
        $app = $this->app();
        //检测桶
        try {
            $res1 = $app->headBucket($name, $region);

            if ($res1 !== true) {
                return $this->setError('COS:设置的桶名已经存在!');
            }

        } catch (\Throwable $e) {
            //桶不存在返回404
            if (strstr('404', $e->getMessage())) {
                return $this->setError('COS:' . $e->getMessage());
            }
        }
        //创建桶
        try {
            $res = $app->createBucket($name, $region, $acl);
        } catch (\Throwable $e) {
            if (strstr('[curl] 6', $e->getMessage())) {
                return $this->setError('COS:无效的区域!!');
            } else if (strstr('Access Denied.', $e->getMessage())) {
                return $this->setError('COS:无权访问');
            }
            return $this->setError('COS:' . $e->getMessage());
        }
        return $res;
    }

    /**
     * 删除桶
     * @param string $name
     * @return bool|mixed
     */
    public function deleteBucket(string $name)
    {
        try {
            $this->app()->deleteBucket($name);
        } catch (\Throwable $e) {
            return $this->setError($e->getMessage());
        }
        return true;
    }

    /**
     * @param string $name
     * @param string|null $region
     * @return array|object
     */
    public function getDomian(string $name, string $region = null)
    {
        try {
            $res = $this->app()->getBucketDomain($name, $region);
            if (isset($res['DomainRule'])) {
                $domainRules[] = $res['DomainRule']['Name'];
            } else {
                $domainRules = array_column($res['DomainRules'], 'Name');
            }
            return $domainRules;
        } catch (\Throwable $e) {
        }
        return [];
    }

    /**
     * 绑定域名
     * @param string $name
     * @param string $domain
     * @param string|null $region
     * @return bool|mixed
     */
    public function bindDomian(string $name, string $domain, string $region = null)
    {
        $this->storageRegion = $region;
        $parseDomin = parse_url($domain);
        try {
            $this->app()->putBucketDomain($name, $region, [
                'Name' => $parseDomin['host'],
                'Status' => 'ENABLED',
                'Type' => 'REST',
                'ForcedReplacement' => 'CNAME'
            ]);
            return true;
        } catch (\Throwable $e) {
            if ($message = $this->setMessage($e->getMessage())) {
                return $this->setError($message);
            }
            return $this->setError($e->getMessage());
        }
        return false;
    }

    /**
     * 处理
     * @param string $message
     * @return string
     */
    protected function setMessage(string $message)
    {
        $data = [
            'The specified bucket does not exist.' => '指定的存储桶不存在。',
            'Please add CNAME/TXT record to DNS then try again later. Please allow up to 10 mins before your DNS takes effect.' => '请将CNAME记录添加到DNS，然后稍后重试。在DNS生效前，请等待最多10分钟。'
        ];
        $msg = $data[$message] ?? '';
        if ($msg) {
            return $msg;
        }
        foreach ($data as $item) {
            if (strstr($message, $item)) {
                return $item;
            }
        }
        return '';
    }

    /**
     * 设置跨域
     * @param string $name
     * @param string $region
     * @return bool
     */
    public function setBucketCors(string $name, string $region)
    {
        $this->storageRegion = $region;
        try {
            $this->app()->putBucketCors($name, [
                'AllowedHeader' => ['*'],
                'AllowedMethod' => ['PUT', 'GET', 'POST', 'DELETE', 'HEAD'],
                'AllowedOrigin' => ['*'],
                'ExposeHeader' => ['ETag', 'Content-Length', 'x-cos-request-id'],
                'MaxAgeSeconds' => 12
            ], $region);
        } catch (\Throwable $e) {
            return $this->setError($e->getMessage());
        }
        return true;
    }

    /**
     * 地域
     * @return mixed|\string[][]
     */
    public function getRegion()
    {
        return [
            [
                'value' => 'ap-chengdu',
                'label' => '成都'
            ],
            [
                'value' => 'ap-shanghai',
                'label' => '上海'
            ],
            [
                'value' => 'ap-nanjing',
                'label' => '南京'
            ],
            [
                'value' => 'ap-beijing',
                'label' => '北京'
            ],
            [
                'value' => 'ap-chongqing',
                'label' => '重庆'
            ],
            [
                'value' => 'ap-shenzhen-fsi',
                'label' => '深圳金融'
            ],
            [
                'value' => 'ap-shanghai-fsi',
                'label' => '上海金融'
            ],
            [
                'value' => 'ap-beijing-fsi',
                'label' => '北京金融'
            ],
            [
                'value' => 'ap-hongkong',
                'label' => '中国香港'
            ],
            [
                'value' => 'ap-singapore',
                'label' => '新加坡'
            ],
            [
                'value' => 'ap-mumbai',
                'label' => '孟买'
            ],
            [
                'value' => 'ap-jakarta',
                'label' => '雅加达'
            ],
            [
                'value' => 'ap-seoul',
                'label' => '首尔'
            ],
            [
                'value' => 'ap-bangkok',
                'label' => '曼谷'
            ],
            [
                'value' => 'ap-tokyo',
                'label' => '东京'
            ],
            [
                'value' => 'na-siliconvalley',
                'label' => '硅谷（美西）'
            ],
            [
                'value' => 'na-ashburn',
                'label' => '弗吉尼亚（美东）'
            ],
            [
                'value' => 'na-toronto',
                'label' => '多伦多'
            ],
            [
                'value' => 'sa-saopaulo',
                'label' => '圣保罗'
            ],
            [
                'value' => 'eu-frankfurt',
                'label' => '法兰克福'
            ],
            [
                'value' => 'eu-moscow',
                'label' => '莫斯科'
            ]
        ];
    }
}
