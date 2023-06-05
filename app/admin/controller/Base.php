<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\library\DragonAuth;
use app\admin\model\ApiLog;
use app\admin\model\ExceptionLog;
use app\BaseController;
use app\library\AsyncBase;
use app\library\Upload;
use Baiy\ThinkAsync\Facade\Async;
use Exception;
use think\exception\FileException;
use think\facade\Env;
use think\facade\Filesystem;
use think\facade\Log;
use think\response\Json;
use WebPConvert\WebPConvert;

/**
 * 后台接口基类
 */
class Base extends BaseController
{
    /**
     * 管理员信息
     * @var array|null
     */
    protected array|null $adminInfo = [];

    /**
     * 需要额外加入的请求数据，
     * 如：前端请求过来10个字段，需要接口自动补齐数据2个死数据，则在这个地方进行设置。
     * @var array
     */
    protected array $params = [];

    /**
     * 查询过滤字段，需要的字段请写入
     * @var array
     */
    protected array $field = [];

    /**
     * 定义精准搜索条件
     * @var array
     */
    protected array $focus = [];

    /**
     * 定义模糊搜索条件
     * @var array
     */
    protected array $vague = [];

    /**
     * 定义区间查询条件
     * @var array
     */
    protected array $range = [];

    /**
     * 定义排序字段
     * @var array
     */
    protected array $order = [];

    /**
     * 自定义查询条件,查询条件遵循 tp6.1 的查询语法
     *
     * @var array
     */
    protected array $rawWhere = [];

    /**
     * 分页页码
     * @var integer
     */
    protected int $page = 0;

    /**
     * 分页数量
     * @var int
     */
    protected int $size = 0;

    /**
     * 模型单例
     */
    protected mixed $model = null;

    /**
     * 验证器单例
     */
    protected mixed $validate = null;

    /**
     * 返回给客户端请求的数据
     * @var array
     */
    protected array $returnData = [];

    /**
     * http返回状态码，200表示请求成功，504表示 请求失败
     */
    protected int $status = 504;

    /**
     * 提示信息
     * @var string
     */
    protected string $msg = '';

    /**
     * 返回给前端的状态码。0表示请求数据失败，1表示请求数据成功
     * @var int
     */
    protected int $code = 1;

    /**
     * 反馈开发提示信息
     * @var array
     */
    protected array $sysMsg = [
        'SUCCESS','ERROR'
    ];

    /**
     * 定义JWT
     * @var null
     */
    protected mixed $jwt = null;

    /**
     * 接收请求的数据
     * @var array
     */
    protected array $inputData = [];

    /**
     * 请求的字段名，如：前端请求过来 a1 b1 c1 d1,接口默认是全接收，如果不需要全接收，则指定["a1","b1"]则只接口这两个字段的值
     * @var array
     */
    protected array $inputField = [];

    /**
     * 列表显示字段，默认为对应数据表全部字段，包括验证规则
     * 默认返回所有数据库字段
     * 如果只需要返回指定字段，则["a1","b1"]指定具体的字段名称，同样要指定验证字段的值
     * @var array
     */
    protected array $outputField = [];

    /**
     * 接口忽略字段，不允许提交过来的字段
     * @var array
     */
    protected array $exceptField = [];

    /**
     * 删除前置检测条件，是否有被使用的数据，如果有，则不让删除。并提示，默认不检测，直接删除数据 false = 未被使用
     * @var bool
     */
    protected bool $isDeleteUsed = false;

    /**
     * 用于记录执行的sql语句
     * @var string
     */
    protected string $sql = '';

    /**
     * 请求的资源ID
     * @var int
     */
    protected int $pkId;

    /**
     * 需要额外加入的请求数据，
     * 如：前端请求过来10个字段，需要接口自动补齐数据2个死数据，则在这个地方进行设置。['name' => 'root','key'=>'server']
     * @param array $params
     * @return Base
     */
    public function setParams(array $params = []):Base
    {
        $this->inputData = array_merge($this->inputData,$params);
        return $this;
    }

    /**
     * 设置指定字段请求来的值
     * @param array $fields
     * @return $this
     */
    public function setInputField(array $fields = []):Base
    {
        $this->inputField = $fields;
        return $this;
    }

    /**
     * 设置返回给前端接口里面列表字段
     * 如：数据查出来有20个字段，前端只需要4个，则会删除其他16个字段不返回给前端，默认是全返回
     * @param array $fields
     * @return $this
     */
    public function setOutputField(array $fields = []):Base
    {
        $this->outputField = $fields;
        return $this;
    }

    /**
     * 初始化方法
     */
    public function initialize():void
    {
        /**
         * 全局接收请求的参数
         */
        if (!empty($this->inputField)) {
            $tmpField = $this->inputField;
        } else {
            $tmpField = '';
        }
        // 初始化请求过来的数据
        $this->inputData = $this->request->param($tmpField);

        // 提取请求条件 模糊查询 精准查询
        if (isset($this->inputData['search']) && $this->inputData['search'] != '') {
            foreach ($this->inputData['search'] as $item) {
                $search = json_decode($item,true);
                if (isVarExists($search,'operator')) {
                    switch ($search['operator']) {
                        case '=':
                            $this->focus[$search['field']] = $search['val'];
                            break;
                        case 'LIKE':
                        case 'like' :
                            $this->vague[$search['field']] = $search['val'];
                            break;
                        case 'RANGE' :
                        case 'range' :
                            if (false !== strrpos($search['val'],',')) {
                                $rangeArr = explode(',',$search['val']);
                                foreach ($rangeArr as $key => $range) {
                                    $rangeArr[$key] = strtotime($range);
                                }
                                $this->range[$search['field']] = $rangeArr;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        // 提取请求条件 排序字段
        if (isset($this->inputData['order']) && $this->inputData['order'] != '' && (false !== strrpos($this->inputData['order'],','))) {
            $order = explode(',',$this->inputData['order']);
            if (count($order) === 2) {
                $this->order[$order[0]] = $order[1];
            }
        }

        /**
         * 回写管理员信息
         */
        $this->adminInfo = $this->request->user_info??[];
        /**
         * 更新登录用户的token有效时间
         */
        $this->pkId = $this->inputData['id'] ?? null;
        // 初始化分页当前页数据
        $this->page = (int)$this->inputData['page'] ?? 0;
        // 初始化分页数量
        $this->size = (int)$this->inputData['size'] ?? 0;

        // 保存单独的请求参数
        if (!empty($this->addField)) {
            $this->inputData = array_merge($this->inputData,$this->addField);
        }
        // 忽略指定忽略字段
        if (!empty($this->exceptField)) {
            foreach ($this->exceptField as $field) {
                if (isset($this->inputData[$field])) {
                    unset($this->inputData[$field]);
                }
            }
        }
        // 输出字段字段
        if (!empty($this->outputField)) {
            $this->field = $this->outputField;
        }

    }

    /**
     * 公共方法验证器
     * @param string $sceneName 对应场景
     * @param array $data   需要验证的数据，数组结构
     */
    public function commonValidate(string $sceneName,array $data) :bool
    {
        if(!$this->validate->scene($sceneName)->check($data)) {
            if (false === strrpos($this->validate->getError(),'|')) {
                $this->code = -1;
                $this->msg  = $this->validate->getError();
            } else {
                $err = explode('|',$this->validate->getError());
                $this->code = (int)$err[1];
                $this->msg  = $err[0];
            }
            return true;
        }
        return false;
    }

    /**
     * 公共方法返回数据结构
     * @param bool $validate    表示是否是验证器异常信息
     */
    public function message(bool $validate = false): Json
    {
        $this->sysMsg[0] = $this->sysMsg[0]  ?? 'invalid';
        $this->sysMsg[$this->code] = $this->sysMsg[$this->code] ?? 'validate invalid';

        $data = [
            'status'        => $this->status,
            'code'          => $this->code,
            'data'          => $this->returnData,
            'message'       => $this->msg,
            'type'          => $validate ? $this->sysMsg[0] : $this->sysMsg[$this->code],
            'time'          => time(),
            'date'          => date('Y-m-d H:i:s',time())
        ];
        return json($data);
    }

    /**
     * 公共的返回数据接口
     * @param array|string $msg     返回的消息
     * @param array|bool $result    返回的结果
     * @param bool $validate        是否是验证器
     */
    public function jr(array|string $msg,bool|array $result = false,bool $validate = false): Json
    {
        if (is_array($msg)) {
            if (count($msg) === 2) {
                $this->msg  = $result ? $msg[1] : $msg[0];
            } else {
                //如果只传一个值。
                $this->msg = $msg[0];
            }
        } elseif (is_string($msg)) {
            $this->msg = $msg;
        } else {
            $this->msg = 'error invalid';
        }
        $this->code     = $result ? 0 : 1;
        $this->status   = $result ? 200 : 504;
        $this->returnData = !is_array($result) ? [] : $result;
        // 存入接口请求日志
        $pathInfo = $this->request->pathinfo();
        $routeArr = explode('/',$pathInfo);
        if (count($routeArr) === 2) {
            $version = $routeArr[count($routeArr) - 2]??'';
            $controller = $routeArr[count($routeArr) - 1]??'';
            // 取方法名，
            $action = match ($this->request->method()) {
                'POST' => 'save',
                'GET' => 'index',
                'PUT' => 'read',
                'DELETE' => 'delete',
                default => '',
            };
        } else {
            $version = $routeArr[count($routeArr) - 3]??'';
            $controller = $routeArr[count($routeArr) - 2]??'';
            $action = $routeArr[count($routeArr) - 1]??'';
        }

        // 接口日志
        $logData = [
            "admin_id"	=>	$this->adminInfo['id'] ?? 0,
            "admin_name"=>	$this->adminInfo['username'] ?? '未登录',
            "version"	=>	$version??'',
            "method"	=>	$this->request->method(),
            "code"	    =>	$result ? 200 : 504,
            "url"	    =>	$this->request->url(true),
            "params"	=>	json_encode($this->request->param()),
            "user_agent"=>	$this->request->header('user_agent'),
            "result"	=>  json_encode(!is_array($result) ? [] : $result),
            "sql"	    =>	$this->sql,
            "controller"=>	$controller??'',
            "action"	=>	$action??'',
            "ip"	    =>	$this->request->ip(),
            "waste_time"	=>	round(microtime(true) - $this->app->getBeginTime(),2),
            "create_time"	=>	time(),
            "update_time"	=>	time()
        ];
        ApiLog::create($logData);
        return $this->message($validate);
    }

    /**
     * 显示资源列表
     */
    public function index() :Json
    {
        try {
            // 验证器验证
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            // 查询模型输出
            $result = $this->model->getIndexList($this->page,$this->size,$this->field,$this->vague,$this->focus,$this->order,$this->range);

            $this->sql = $this->model->getLastSql();
            //构建返回数据结构
            return $this->jr('获取成功',!empty($result) ? $result : true);
        } catch (Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 显示指定的资源
     */
    public function read():Json
    {
        try {
            //前置拦截
            if (!$this->pkId) {
                return $this->jr('【详情】请输入需要获取的id值');
            }
            // 验证器验证
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            // 模型查询数据
            $result = $this->model->getInfo($this->pkId,[],$this->field);
            // 返回最后一条执行的sql
            $this->sql = $this->model->getLastSql();
            // 返回最终结果
            return $this->jr(['获取失败','获取成功'],$result);

        } catch (Exception $e) {
            // 接口异常写入
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 保存新建的资源
     */
    public function save():Json
    {
        try {
            //前置拦截
            if (empty($this->inputData)) {
                return $this->jr('请检查提交过来的数据');
            }
            // 保存单独的请求参数
            if (!empty($this->addField)) {
                $this->inputData = array_merge($this->inputData,$this->addField);
            }
            // 验证器
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            // 模型处理数据
            $result = $this->model->addData($this->inputData);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['新增失败','新增成功'],$result);
        } catch (Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('新增异常，请查看异常日志或者日志文件进行修复');
        }
    }

    /**
     * 保存更新的资源
     */
    public function update():Json
    {
        try {
            //前置拦截
            if (!$this->pkId) {
                return $this->jr('【更新】请输入正确的需要修改的ID值');
            }
            // 保存单独的请求参数
            if (!empty($this->editField)) {
                $this->inputData = array_merge($this->inputData,$this->editField);
            }
            // 通用验证
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            $result = $this->model->editData($this->inputData);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['修改失败','修改成功'],$result);
        } catch (Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('修改数据异常，请查看异常日志或者日志文件进行修复');
        }
    }

    /**
     * 删除指定资源
     */
    public function delete():Json
    {
        try {
            //前置拦截
            if (!$this->pkId) {
                return $this->jr('【删除】请输入需要删除的ID值');
            }
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            // 增加删除关联引用查询
            if (!$this->isDeleteUsed) {
                $result = $this->model->delData($this->inputData);
            } else {
                return $this->jr('删除失败！该数据被引用，不能删除，请解除引用关系再删除！');
            }
            $this->sql = $this->model->getLastSql();
            return $this->jr(['删除失败','删除成功'],$result);
        } catch (Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('删除异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 通用上传类，主要是本地文件
     */
    public function upload():Json|array
    {
        try {
            $file = $this->request->file('file');
            $isFakeUpload = $this->request->post('is_fake_upload',false);
            $upload     = new Upload($file);
            $attachment = $upload->upload(null, $this->adminInfo['id'],0,$isFakeUpload);
            unset($attachment['create_time'], $attachment['quote']);
            $this->sql = $this->model->getLastSql();
            if (!$isFakeUpload) {
                return $this->jr(['上传文件失败','上传文件成功'],$attachment);
            } else {
                return $attachment;
            }
        } catch (Exception|FileException $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('上传异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 上传图片并转换成webp格式，支持多图上传
     */
    public function uploadImage():Json
    {
        try {
            $files = $this->request->file();
            if (!$files) {
                return $this->jr("请选择上传的文件");
            }
            $data = $localFile = [];

            foreach ($files as $key => $file) {
                if (is_array($file)) {
                    // 单上传名称，多图上传 同一名称的数组文件上传.image[0] image[1]
                    foreach ($file as $key1 => $file1) {
                        if ($this->commonValidate(__FUNCTION__, [$key => $file1])) {
                            return $this->jr($this->validate->getError());
                        }
                        //上传本地
                        $tempFile = Filesystem::disk('public')->putFile('',$file1,'unique_id');
                        $localFile[$key1] = [
                            'file'       => $tempFile,
                            'real_name' => $file1->getOriginalName(),
                            'md5'       => md5_file($file1->getPathname())
                        ];
                    }
                } else {
                    //多上传名称，单图上传  //不同字段文件名上传 image img icon
                    // 单名称，单图上传
                    if ($this->commonValidate(__FUNCTION__, [$key => $file])) {
                        return $this->jr($this->validate->getError());
                    }
                    //上传本地
                    $tempFile = Filesystem::disk('public')->putFile('',$file,'unique_id');
                    $localFile[$key] = [
                        'file'       => $tempFile,
                        'real_name' => $file->getOriginalName(),
                        'md5'       => md5_file($file->getPathname())
                    ];
                }
            }
            // 上传云存储或者本地数据组装
            if (!empty($localFile)) {
                foreach ($localFile as $filename) {
                    $localPath = Env::get('upload.local_path','storage').DIRECTORY_SEPARATOR.$filename['file'];
                    $source = public_path() . $localPath;
                    $destination = $source . '.webp';
                    $options = [];
                    WebPConvert::convert($source, $destination, $options);
                    $data[] = [
                        'cdn'       => Env::get('upload.cdn'),
                        'origin'    => $filename['real_name'],
                        'file'       => $filename['file'],
                        'md5'       => $filename['md5'],
                        'url'       => Env::get('upload.cdn').$localPath,
                        'r_path'    => $localPath,
                        'w_path'    => $localPath.'.webp'
                    ];
                    // 表示是否要本地存储，如果不需要，则删除本地
                    if (!Env::get('upload.is_local_exists',true)) {
                        @unlink($source);
                    }
                }
            }
            $this->sql = $this->model->getLastSql();
            return $this->jr('上传成功',$data);
        } catch (Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('上传文件异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 拖拽排序
     */
    public function sortable():Json
    {
        try {
            //前置拦截
            if (empty($this->inputData)) {
                return $this->jr('请检查提交过来的数据');
            }
            // 验证器
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }

            $result = $this->model->sortable($this->inputData);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['更新排序失败','更新排序成功'],$result);
        } catch (Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('新增异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * curl请求
     * @param $url  string 请求的url链接
     * @param $data string|array|mixed 请求的数据
     * @param bool $is_post 是否是post请求，默认false
     * @param array $options 是否附带请求头
     * @return array
     */
    public function http(string $url, array $data, bool $is_post=false, array $options=[]):array
    {
        $data  = json_encode($data);
        $headerArray = [
            'Content-type: application/json;charset=utf-8',
            'Accept: application/json'
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
        if ($is_post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($options['cookie'])) {
            curl_setopt($curl, CURLOPT_COOKIE, $options['cookie']);
        } else {
            $headerArray = array_merge($headerArray,$options);
        }
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($curl);
        $http_status = curl_errno($curl);
        $http_msg = curl_error($curl);
        curl_close($curl);
        if ($http_status == 0) {
            return json_decode($output, true);
        } else {
            return ['status' => $http_status, 'message' => $http_msg, 'data' => []];
        }
    }

    /**
     * 打印调试信息到日志
     * @param mixed $data
     * @param string $string
     */
    public function dLog(mixed $data, string $string = 'debug'): void
    {
        if (is_array($data)) {
            $newData = json_encode($data);
        } else {
            $newData = $data;
        }
        Log::record($string. '==' . $newData);
    }

    /**
     * 触发异步队列方法【仅用于演示，正常使用请更方法及传参数】
     * 参数类型和个数根据业务情况来设定
     */
    public function doAsyncFunc(string $model, int $versionId, string $fileType, string $index, array $data):string
    {
        // 异步执行
        Async::exec(AsyncBase::class, 'doAsyncItemToMysql', $model,$versionId,$fileType,$index,$data);
//        Async::execUseCustomQueue(AsyncBase::class, 'doAsyncItemToMysql', $model,$versionId,$fileType,$index,$data);
        // 异步延迟执行 延迟20秒
//        Async::delay(mt_rand(1,20), AsyncBase::class, 'doAsyncItemToMysql', $model,$versionId,$fileType,$index,$data);
        // 异步延迟执行 延迟20秒
//        Async::delayUseCustomQueue(20, AsyncBase::class, 'doAsyncItemToMysql', $model,$versionId,$fileType,$index,$data);
        return "执行成功！";
    }

}
