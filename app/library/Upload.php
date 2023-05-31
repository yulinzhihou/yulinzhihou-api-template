<?php

namespace app\library;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use \think\File;
use think\Exception;
use think\facade\Config;
use think\file\UploadedFile;
use think\Model;

/**
 * 上传类
 */
class Upload
{
    /**
     * 配置信息
     * @var array
     */
    protected array $config = [];

    /**
     * 文件上传类
     */
    protected UploadedFile|null $file = null;

    /**
     * 是否是图片
     * @var bool
     */
    protected bool $isImage = false;

    /**
     * 文件信息
     */
    protected $fileInfo = null;

    /**
     * 细目
     * @var string
     */
    protected string $topic = 'upload';

    /**
     * 构造方法
     * @param UploadedFile|null $file
     * @throws \Exception
     */
    public function __construct(UploadedFile $file = null, $config = [])
    {
        $this->config = Config::get('upload');
        if ($config) {
            $this->config = array_merge($this->config, $config);
        }

        if ($file) {
            $this->setFile($file);
        }
    }

    /**
     * 设置文件
     * @param UploadedFile $file
     * @throws \Exception
     */
    public function setFile(UploadedFile $file): void
    {
        if (empty($file)) {
            throw new Exception('没有文件被上传', 10001);
        }

        $suffix             = strtolower($file->extension());
        $suffix             = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        $fileInfo['suffix']  = $suffix;
        $fileInfo['type']   = $file->getOriginalMime();
        $fileInfo['size']   = $file->getSize();
        $fileInfo['name']   = $file->getOriginalName();
        $fileInfo['sha1']   = $file->sha1();

        $this->file     = $file;
        $this->fileInfo = $fileInfo;
    }

    /**
     * 检查文件类型
     * @return bool
     * @throws \Exception
     */
    protected function checkMimetype(): bool
    {
        $mimetypeArr = explode(',', strtolower($this->config['mimetype']));
        $typeArr     = explode('/', $this->fileInfo['type']);
        //验证文件后缀
        if ($this->config['mimetype'] === '*'
            || in_array($this->fileInfo['suffix'], $mimetypeArr) || in_array('.' . $this->fileInfo['suffix'], $mimetypeArr)
            || in_array($this->fileInfo['type'], $mimetypeArr) || in_array($typeArr[0] . "/*", $mimetypeArr)) {
            return true;
        }
        throw new Exception('上传的文件格式未被允许');
    }

    /**
     * 是否是图片并设置好相关属性
     * @throws \Exception
     */
    protected function checkIsImage():bool
    {
        if (
            in_array($this->fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) ||
            in_array($this->fileInfo['suffix'], ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])
        ) {
            $imgInfo = getimagesize($this->file->getPathname());
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                throw new Exception('上传的图片文件不是有效的图像');
            }
            $this->fileInfo['width']  = $imgInfo[0];
            $this->fileInfo['height'] = $imgInfo[1];
            $this->isImage            = true;
            return true;
        }
        return false;
    }

    /**
     * 是否将指定图片转换成WEBP格式
     */
    public function isImageToWebp() : bool
    {
        return in_array($this->fileInfo['suffix'], $this->config['is_to_webp']);
    }

    /**
     * 上传的文件是否为图片
     * @return bool
     */
    public function isImage():bool
    {
        return $this->isImage;
    }

    /**
     * 检查文件大小
     * @throws \Exception
     */
    protected function checkSize()
    {
        $size = file_unit_to_byte($this->config['maxsize']);
        if ($this->fileInfo['size'] > $size) {
            throw new Exception('上传的文件太大(%sM)，最大文件大小：%sM', [
                round($this->fileInfo['size'] / pow(1024, 2), 2),
                round($size / pow(1024, 2), 2)
            ]);
        }
    }

    /**
     * 获取文件后缀
     * @return mixed|string
     */
    public function getSuffix():mixed
    {
        return $this->fileInfo['suffix'] ?: 'file';
    }

    /**
     * 获取文件保存名
     * @param null $saveName
     * @param null $filename
     * @param null $sha1
     * @return array|mixed|string|string[]
     */
    public function getSaveName($saveName = null, $filename = null, $sha1 = null):mixed
    {
        if ($filename) {
            $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        } else {
            $suffix = $this->fileInfo['suffix'];
        }
        $filename   = $filename ?: ($suffix ? substr($this->fileInfo['name'], 0, strripos($this->fileInfo['name'], '.')) : $this->fileInfo['name']);
        $sha1       = $sha1 ?: $this->fileInfo['sha1'];
        $replaceArr = [
            '{topic}'    => $this->topic,
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => StrRandom::build(),
            '{random32}' => StrRandom::build('alnum', 32),
            '{filename}' => substr($filename, 0, 100),
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filesha1}' => $sha1,
        ];
        $saveName   = $saveName ?: $this->config['savename'];
        return str_replace(array_keys($replaceArr), array_values($replaceArr), $saveName);
    }

    /**
     * 上传文件
     * @param null $saveName
     * @param int $adminId
     * @param int $userId
     * @param bool $isFakeUpload 是否为伪装成用户的上传的本地文件上传
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws \Exception
     * @throws ModelNotFoundException
     */
    public function upload($saveName = null, int $adminId = 0, int $userId = 0,bool $isFakeUpload = false): array
    {
        if (empty($this->file)) {
            throw new Exception('没有文件被上传或文件大小超出服务器上传限制！');
        }

        $this->checkSize();
        $this->checkMimetype();
        $this->checkIsImage();
        $params = [
            'topic'    => $this->topic,
            'url'      => $this->getSaveName(),
            'width'    => $this->fileInfo['width'] ?? 0,
            'height'   => $this->fileInfo['height'] ?? 0,
            'name'     => substr(htmlspecialchars(strip_tags($this->fileInfo['name'])), 0, 100),
            'size'     => $this->fileInfo['size'],
            'mimetype' => $this->fileInfo['type'],
            'storage'  => 'local',
            'sha1'     => $this->fileInfo['sha1']
        ];

        $result = $this->doSaveToAttachment($params,$saveName,$isFakeUpload);

        return $result ? $params : [];
    }

    /**
     * 新增到附件表的数据
     * @param $params
     * @param $saveName
     * @param $isFakeUpload
     * @return bool
     */
    private function doSaveToAttachment($params,$saveName,$isFakeUpload):bool
    {
        $destDir = root_path() . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $params['url']);
        if (!is_file($destDir) && !$isFakeUpload) {
            return $this->move($saveName);
        }
        // 兼容模式
        // 伪装成用户上传的本地文件
        if ($isFakeUpload) {
            return $this->move($saveName,$isFakeUpload);
        }

        return false;
    }

    /**
     * 移动并保存上传文件到指定位置
     * @param null $saveName
     * @param bool $isFakeUpload
     * @return bool
     */
    public function move($saveName = null, bool $isFakeUpload = false): bool
    {
        $saveName  = $saveName ?: $this->getSaveName();
        $saveName  = '/' . ltrim($saveName, '/');
        $uploadDir = substr($saveName, 0, strripos($saveName, '/') + 1);
        $fileName  = substr($saveName, strripos($saveName, '/') + 1);
        $destDir   = root_path() . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $uploadDir);
        if (!is_dir($destDir)) {
            @mkdir($destDir,0777,true);
        }
        if (!$isFakeUpload) {
            return (bool)$this->file->move($destDir, $fileName);
        } else {
            return @rename($this->file->getPathname(),$destDir.DIRECTORY_SEPARATOR.$fileName);
        }
    }
}