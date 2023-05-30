# yulinzhihou_api 后台接口脚手架

> 基于 `thinkphp 6.1.3`
>
> 运行环境要求PHP7.2+，兼容PHP8.1
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


## 集成功能
1. 数据迁移功能 `think-migration`
2. 登录验证 `JWT`
3. 自动生成 `RSA` 证书。使用姿势 `php think key:generate`
4. 异步队列执行
5. 接口监控日志，耗时
6. 增加 `CURD` 生成控制器，模型，验证器命令分别是 `php think yc:create` `php think ym:create` `php think yv:create`
7. 增加提交脚本 `push.sh`,方便提交代码，使用姿势：`bash push.sh '第一次提交'` 即可推送到仓库
8. 增加自动生成 `控制器` `模型` `验证器` 使用姿势：`bash make.sh admin v1 Goods` 默认生成 `Goods` `控制器` `模型` `验证器`
9. 增加异常日志及数据管理，线上项目异常的时候会记录到异常日志管理模块。从而更加好的去修复出现的问题

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
$table = $this->table('goods',['engine'=>'InnoDB','auto_increment'=>true,'charset'=>'utf8','primary_key'=>'id','comment'=>'商品表'])->addIndex('id');
$table
    ->addColumn('platform_id','integer',['limit'=>10,'default'=>0,'null'=>false,'comment'=>'平台ID'])
    ->addColumn('language_id','integer',['limit'=>MysqlAdapter::INT_TINY,'default'=>0,'null'=>false,'comment'=>'语言包ID'])
    ->addColumn('name','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'产品标题'])
    ->addColumn('model_number','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'产品型号'])
    ->addColumn('main_img','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'产品主图'])
    ->addColumn('goods_category_id','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'商品分类ID'])
    ->addColumn('price','decimal',['scale'=>2,'precision'=>10,'signed'=>false,'default'=>0.00,'null'=>false,'comment'=>'商品价格'])
    ->addColumn('details','text',['comment'=>'商品详情'])
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