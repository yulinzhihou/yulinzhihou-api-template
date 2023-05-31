<?php

use think\migration\Migrator;
use think\migration\db\Column;

class ApiLogCreate extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('api_log',['engine'=>'innodb','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','primary_key'=>'id','comment'=>'日志记录表'])->addIndex('id');
        $table
            ->addColumn('admin_id','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'管理员ID'])
            ->addColumn('admin_name','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'管理员名称'])
            ->addColumn('user_agent','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'请求代理'])
            ->addColumn('method','string',['limit'=>30,'default'=>'','null'=>false,'comment'=>'请求方式'])
            ->addColumn('version','string',['limit'=>30,'default'=>'','null'=>false,'comment'=>'接口版本'])
            ->addColumn('controller','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'操作模块'])
            ->addColumn('action','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'操作方法'])
            ->addColumn('url','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'请求的URL'])
            ->addColumn('params','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'请求参数'])
            ->addColumn('title','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'页面标题'])
            ->addColumn('code','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'响应状态'])
            ->addColumn('result','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'响应结果'])
            ->addColumn('sql','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'执行的sql'])
            ->addColumn('ip','string',['limit'=>15,'default'=>'','null'=>false,'comment'=>'访问IP'])
            ->addColumn('waste_time','decimal',['limit'=>10,'precision'=>8,'scale'=>2,'signed'=>false,'default'=>0.00,'null'=>false,'comment'=>'接口处理时间'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'更新时间'])
            ->addIndex('code')
            ->addIndex('admin_id')
            ->addIndex('admin_name')
            ->addIndex('code')
            ->addIndex('method')
            ->addIndex('title')
            ->addIndex('controller')
            ->addIndex('action')
            ->addIndex('ip')
            ->addIndex('create_time')
            ->create();
    }
}
