<?php
declare(strict_types=1);

namespace app\library;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use think\facade\Config;
use Vtiful\Kernel\Excel;

/**
 * Excel 处理类
 * 读取Excel图片并保存其路径
 * Class ExcelImagePathServer
 * @package App\Services
 */
class ExcelHandle
{
    /**
     * 表格文件的相对路径
     * @var string
     */
    protected string $relative_path;

    /**
     * 文件名
     * @var string
     */
    protected string $filename;

    /**
     * php spreadsheet 类
     * @var Spreadsheet
     */
    protected Spreadsheet $spreadsheet;

    /**
     * xls writer类
     * @var Excel
     */
    protected Excel $xls_writer;

    /**
     * xls writer类
     * @var Excel
     */
    protected Excel $sheet_writer;

    /**
     * 图片的相对路径
     * @var string
     */
    protected string $image_path;

    /**
     * 文件后缀
     */
    protected string $ext;


    /**
     * 图片保存在本地的绝对路径
     * @var string
     */
    protected string $absolute_image_path;


    /**
     *
     */
    protected \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $imageReader;

    /**
     * ExcelImagePathServer constructor.
     * @param string $excel_file 文件在本地的绝对路径
     *
     * //图片
     * 'images' => [
     * // 磁盘类型
     * 'type'       => 'local',
     * // 磁盘路径
     * 'root'       => app()->getRootPath() . "public".DIRECTORY_SEPARATOR.Env::get('upload.local_path','storage').DIRECTORY_SEPARATOR.'images',
     * // 磁盘路径对应的外部URL路径
     * 'url'        => DIRECTORY_SEPARATOR.Env::get('upload.local_path','storage').DIRECTORY_SEPARATOR.'images',
     * // 可见性
     * 'visibility' => 'public',
     * ],
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct(string $excel_file)
    {
        // 图片保存路径的相对路径前缀
        $this->image_path = Config::get('filesystem.disks.images.url','').DIRECTORY_SEPARATOR.date('Ymd',time()).DIRECTORY_SEPARATOR;
        // 图片保存在本地的绝对路径
        $this->absolute_image_path =  rtrim(public_path(),DIRECTORY_SEPARATOR).$this->image_path;
        // 存储文件名
        $this->filename = basename($excel_file);

        // 获取文件后缀，根据后缀实例化 php spreadsheet 读取类
        $this->ext = strtolower(pathinfo($excel_file,PATHINFO_EXTENSION));

        $this->imageReader = IOFactory::createReader(ucfirst($this->ext))->load($excel_file)->getSheet(0);

        $reader = new Xlsx();

        $this->spreadsheet = $reader->load($excel_file);
        // 取文件路径和文件名
        $config = ['path' => dirname($excel_file)];
        //
        $this->xls_writer = new Excel($config);

        if (!is_dir(rtrim(public_path(),DIRECTORY_SEPARATOR).$this->image_path)) {
            @mkdir(rtrim(public_path(),DIRECTORY_SEPARATOR).$this->image_path, 0755,true);
        }
    }

    /**
     * 处理类
     * @throws Exception
     */
    public function handle(array $setType = []):array
    {
        // 处理时间字符串
        if (!empty($setType)) {
            $data = $this->xls_writer->openFile($this->filename)->openSheet()->setType($setType)->getSheetData();
        } else {
            // 获取表格非图片数据
            $data = $this->xls_writer->openFile($this->filename)->openSheet()->getSheetData();
        }

        $imagesCollection = $this->imageReader->getDrawingCollection();

        $row = [];

        foreach ($imagesCollection as $images) {
            list($startColumn, $startRow) = Coordinate::coordinateFromString($images->getCoordinates());
            $imagesFileName = $images->getCoordinates().'-'. md5(chr(mt_rand(1,256)));
            switch ($images->getExtension()) {
                case 'jpg':
                case 'jpeg':
                    $imagesFileName .= '.jpeg';
                    $source = imagecreatefromjpeg($images->getPath());
//                    imagejpeg($source,$this->absolute_image_path.$imagesFileName);
                    imagewebp($source,$this->absolute_image_path.$imagesFileName.'.webp',90);
                    break;
                case 'gif' :
                    $imagesFileName .= '.gif';
                    $source = imagecreatefromgif($images->getPath());
//                    imagegif($source,$this->absolute_image_path.$imagesFileName);
                    imagewebp($source,$this->absolute_image_path.$imagesFileName.'.webp',90);
                    break;
                case 'png':
                    $imagesFileName .= '.png';
                    $source = imagecreatefrompng($images->getPath());
//                    imagepng($source,$this->absolute_image_path.$imagesFileName);
                    imagewebp($source,$this->absolute_image_path.$imagesFileName.'.webp',90);
                    break;
            }
            $source = $this->image_path.$imagesFileName.'.webp';

            $row[$startRow.'-'.$this->ABC2decimal($startColumn)] = ltrim($source,'/');

        }

        return ['data' =>$data,'image' =>$row];
    }

    /**
     * 保存图片
     *
     * @param Drawing $drawing
     * @param $image_filename
     * @return string
     */
    public function saveImage(Drawing $drawing, $image_filename):string
    {
        $image_filename .= '.' . $drawing->getExtension();
        switch ($drawing->getExtension()) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($drawing->getPath());
                imagejpeg($source,  $image_filename);
                break;
            case 'gif':
                $source = imagecreatefromgif($drawing->getPath());
                imagegif($source, $image_filename);
                break;
            case 'png':
                $source = imagecreatefrompng($drawing->getPath());
                imagepng($source, $image_filename);
                break;
        }

        return $drawing->getExtension();
    }

    /**
     * 批量删除本地图片
     * @param $data
     * @return bool
     */
    public function delImage($data):bool
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $datum) {
            if (file_exists(public_path().$datum)) {
                unlink(public_path().$datum);
            }
        }
        return true;
    }

    /**
     * 坐标转换
     *
     * @param $abc
     * @return float|int
     */
    protected function ABC2decimal($abc)
    {
        $startIndex = 0;
        $len = strlen($abc);
        for ($i = 1; $i <= $len; $i++) {
            $char = substr($abc, 0 - $i, 1);//反向获取单个字符
            $int = ord($char);
            $startIndex += ($int - 65) * pow(26, $i - 1);
        }
        return $startIndex;
    }
}