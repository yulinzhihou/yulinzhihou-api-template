# yulinzhihou-template-api 接口脚手架

> 基于 `thinkphp 6.1.3`
>
> 运行环境要求PHP8.1+
>
> 开发环境
>
> OS: MAC OS Ventura 13.3.1(a)
>
> PHP: PHP 8.1.19 (cli) (built: May 12 2023 08:29:35) (NTS)
>
> Nginx: nginx/1.23.4
>
> Mysql: mysql  Ver 8.0.33 for macos13.3 on x86_64 (Homebrew)
> 
>
## 接口文档
https://apifox.com/apidoc/shared-0b705c39-8573-494a-b594-731dd1604c2e?pwd=yulinzhihou.com
接口文档默认需要密码，密码进群免费获取：849144754

## `PHP拓展` 自行安装，不懂的百度
- `gd`
- `xlswriter` 
- `curl`
- `fileinfo`
- `redis`
- `json`
- `openssl`

## 集成功能
1. 数据迁移功能 `think-migration`
2. 登录验证 `JWT`
3. 自动生成 `RSA` 证书。使用姿势 `php think key:generate` 默认生成的证书名称为 `jwt.name` 的名称
4. 异步队列执行
5. 接口监控日志，耗时
6. 增加 `CURD` 生成控制器，模型，验证器命令分别是 `php think yc:create` `php think ym:create` `php think yv:create`
7. 增加提交脚本 `push.sh`,方便提交代码，使用姿势：`bash push.sh '第一次提交'` 即可推送到仓库
8. 增加自动生成 `控制器` `模型` `验证器` 使用姿势：`bash make.sh admin v1 Goods` 默认生成 `Goods` `控制器` `模型` `验证器`
9. 增加异常日志及数据管理，线上项目异常的时候会记录到异常日志管理模块。从而更加好的去修复出现的问题
10. `think-whoops` 插件更加优雅的进行调试报错，可以直接定义错误行号，点击可以跳转到 IDE 指定文件位置
11. 同时兼容 `session-cookie` 和 `token` 模式请求，`token` 支持刷新等
12. 接口非对称加密，服务端完成。客户端对应需要配合公钥进行加密，支持请求数据加密和接口返回数据加密。开头在服务端配置文件 `.env` 里面进行设置 [app] 下有 `is_admin_api_secret` `is_api_secret` `is_index_secret`。默认是关闭的
13. 支持 `RSA` 证书加密接口数据，支持公钥加解密，支持私钥加解密。对应的应用场景不一样
14. 

## 部署
- 第一步：下载或者克隆代码
```bash
git clone https://github.com/yulinzhihou/yulinzhihou-api-template.git
```
或者
```bash
git clone https://gitee.com/yulinzhihou/yulinzhihou-api-template.git
```
- 第二步：安装依赖
```shell
cd yulinzhihou-api-template && composer install
```
- 第三步：复制 `.env.sample` 为 `.env` 并创建一个指定的数据库。配置好 `mysql` , `redis` 相关配置
  会初始化数据表以及基础数据，`admin`,`menu`,`role` `api_log` 表里面
```bash
# 进入项目目录执行
cd yulinzhihou-api-template 
php think migrate:run
php think seed:run
```

- 第四步：正常使用开发，先建立数据迁移文件。如：增加商品表的数据迁移文件，我这里命名为 `GoodsCreate` 相关使用技巧请参考[官方手册](https://www.kancloud.cn/manual/thinkphp6_0/1037481), [Phinx官方手册](http://docs.phinx.org),[大佬基于 phinx 翻译出的中文手册](https://tsy12321.gitbooks.io/phinx-doc/content/)

```bash
php think migrate:create GoodsCreate
```
示例：
```php
$table = $this->table('goods',['engine'=>'InnoDB','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','primary_key'=>'id','comment'=>'商品表'])->addIndex('id');
$table
    ->addColumn('platform_id','integer',['limit'=>10,'default'=>0,'null'=>false,'comment'=>'平台ID'])
    ->addColumn('language_id','integer',['limit'=>MysqlAdapter::INT_TINY,'default'=>0,'null'=>false,'comment'=>'语言包ID'])
    ->addColumn('name','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'产品标题'])
    ->addColumn('model_number','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'产品型号'])
    ->addColumn('main_img','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'产品主图'])
    ->addColumn('goods_category_id','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'商品分类ID'])
    ->addColumn('price','decimal',['scale'=>2,'precision'=>10,'signed'=>false,'default'=>0.00,'null'=>false,'comment'=>'商品价格'])
    ->addColumn('details','text',['comment'=>'商品详情'])
    ->addColumn('details','text',['limit'=>16,'comment'=>'商品详情'])
    ->addColumn('params','text',['comment'=>'商品参数'])
    ->addColumn('title','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'页面title'])
    ->addColumn('keywords','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'关键词'])
    ->addColumn('description','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'页面描述SEO用'])
    ->addColumn('sort','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'排序'])
    ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'状态，0=正常,1=禁用'])
    ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'创建时间'])
    ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'更新时间'])
    ->create();
```

- 第五步：生成对应的控制器，模型，验证器
```bash
bash make.sh admin v1 Goods
```
执行上述命令会生成如下文件
```bash
app\admin\controller\v1\Goods.php
app\admin\model\Goods.php
app\admin\validate\Goods.php
```
1. `app\admin\controller\v1\Goods.php` 内容如下

```php
<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\Controller\Base;
use app\admin\model\Goods as GoodsModel;
use app\admin\validate\Goods as GoodsValidate;

/**
 * Goods
 */
class Goods extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new GoodsModel();
        $this->validate = new GoodsValidate();
    }

}
```

2. `app\admin\model\Goods.php` 内容如下
```php
<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;

/**
 * @mixin \think\Model
 */
class Goods extends Base
{
    //
}

```


3. `app\admin\validate\Goods.php` 内容如下

```php
<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class Goods extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];
}

```

自动生成继承基类控制器和基类模型，如果无特殊关联关系，互此，`增删改查` 接口基本完成

- 第六步：在 `app\admin\route\v1.php` 增加指定的资源路由
```php
……
……
……
Route::resource('goods','Goods');
……
……
……
```

## 集成异步队列功能+守护进程，成功解决大文件大数据执行超时的问题

> 实测，异步队列方法不适用于循环数据，因为一个方法里面如果执行循环，如果守护进程开了多个进程的话，还是会卡死，并且方法不一定能跑完。可能会因单个进程资源开销过大，直接被系统 kill 掉
> 
> 异步方法里面建议执行单一方法，不建议使用循环执行操作数据。
>
> 守护进程可以根据电脑配置开多进程进行执行异步队列。

异步方法所涉及的方法有任何代码变更的时候，**建议重启守护进程,建议重启守护进程,建议重启守护进程,建议重启守护进程** 重要的事情多说

异步方法所涉及的方法有任何代码变更的时候，**建议重启守护进程,建议重启守护进程,建议重启守护进程,建议重启守护进程** 重要的事情多说

异步方法所涉及的方法有任何代码变更的时候，**建议重启守护进程,建议重启守护进程,建议重启守护进程,建议重启守护进程** 重要的事情多说

异步方法所涉及的方法有任何代码变更的时候，**建议重启守护进程,建议重启守护进程,建议重启守护进程,建议重启守护进程** 重要的事情多说

异步方法所涉及的方法有任何代码变更的时候，**建议重启守护进程,建议重启守护进程,建议重启守护进程,建议重启守护进程** 重要的事情多说

异步方法所涉及的方法有任何代码变更的时候，**建议重启守护进程,建议重启守护进程,建议重启守护进程,建议重启守护进程** 重要的事情多说

异步方法所涉及的方法有任何代码变更的时候，**建议重启守护进程,建议重启守护进程,建议重启守护进程,建议重启守护进程** 重要的事情多说


### 实用步骤：

- 第一步：创建异步触发方法。 `app\admin\Base.php`

```php
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
```

- 第二步：`app\library\AsyncBase.php` 创建异步队列核心执行方法，先定义一个类,这个类里面的核心执行方法必须是静态方法，且里面处理的业务逻辑尽量是单条数据。如果有多条或者需要循环处理，建议在第一步的时候切分成单点。
```php
    /**
     * 执行单条异步数据到数据库。支持多并发
     * @param string $model 模型编号
     * @param int $versionId 版本ID
     * @param string $fileType 文件类型
     * @param string $index 文件对应的索引编号字段，如：item_id,gem_id
     * @param array $data   行数据
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function doAsyncItemToMysql(string $model, int $versionId, string $fileType, string $index, array $data):void
    {
        $isDataExists = $model::where('version_id',$versionId)->where($index,$data[$index])->find();
        if ($isDataExists) {
            try {
                $isDataExists->save($data);
            } catch (\Exception $e) {
                ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model','');
                Log::record($e->getMessage());
            }
        } else {
            try {
                $model::create($data);
            } catch (\Exception $e) {
                ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model','');
                Log::record($e->getMessage());
            }
        }
        Cache::delete($fileType.':version_'.$versionId.':'.$data[$index]);
    }
```

- 第三步：确定具体调用的位置， `app\admin\controller\v1\AsyncDemo.php` 即业务逻辑需要处理大数据的位置。比如你访问一个URL需要进行循环拉取更新数据。这个时候在浏览器里面打开，就会无限转圈，甚至还会报500，40x 等一系列问题。这个时候你就可以把这一块的逻辑扔给异步队列进行处理。先给浏览器返回一个结果

```php
<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\ExceptionLog;
use think\facade\Cache;

/**
 * 异步队列业务逻辑演示类-
 * 非正式业务逻辑代码，只是一个演示案例，切务直接使用。
 */
class AsyncDemo extends Base
{
    /**
     * 保存新建的资源
     */
    public function save():\think\Response\Json
    {
        try {
            $this->inputData = array_merge($this->inputData,$this->request->post());
            //前置拦截
            if (empty($this->inputData)) {
                return $this->jr('请检查提交过来的数据');
            }
            // 额外增加请求参数
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }

            // 验证器
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }

            $result = $this->model->addData($this->inputData);
            // 根据新增结果触发异步导入数据
            if ($result) {
                $modelName = $this->model->getFileModelById($this->inputData['file_type']);
                // 改写数据
                $fileTypeStr = (int)$this->inputData['file_type'];
                $versionId = (int)$this->inputData['version_id'];

                // 假如需要根据数据库所有数据进行联动更新操作。先获取所有数据，在这个业务方法里面进行所有数据切分成单一执行逻辑交与异步队列
                $allData = $this->model->getAllData();

                if (!empty($allData)) {
                    foreach ($allData as $data) {
                        if ((int)$data > 0) {
                            $rData = Cache::get($fileTypeStr.':version_'.$versionId.':'.$data);
                            if (!empty($rData)) {
                                // 每循环一次，将对应需要异步处理的第一次方法交给异步队列进行处理，
                                // 这是一个折中的办法 ，目前我用这个方法进行队列处理，还是可以很丝滑的。但还是有一些坑，不过勉强能用了。
                                // 这里面的坑主要是数据库长连接的问题，还有就是守护进程可能会处于僵尸进程状态，需要重启。暂时是可以用这个文案进行处理
                                // 目前主要处理大文件excel进行导入数据，大概陆续处理了近1千多万条数据到数据库，文件有1400多个文件。目前没啥大问题
                                $this->doAsyncFunc($modelName['modelName'],$versionId,$fileTypeStr,(string)array_keys($rData)[0],$rData);
                            }
                        }
                    }
                }

            }
            $this->sql = $this->model->getLastSql();
            return $this->jr(['新增失败','新增成功-请等候3-5分钟刷新查看！'],true);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('新增异常，请查看异常日志或者日志文件进行修复');
        }

    }

}
```

