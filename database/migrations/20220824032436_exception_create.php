<?php

use think\migration\Migrator;
use think\migration\db\Column;

class ExceptionCreate extends Migrator
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
        $table = $this->table('exception_log',['engine'=>'innodb','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','primary_key'=>'id','comment'=>'异常日志表'])->addIndex('id');
        $table
            ->addColumn('admin_id','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'管理员ID'])
            ->addColumn('admin_name','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'管理员名称'])
            ->addColumn('url','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'请求的URL'])
            ->addColumn('params','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'请求参数'])
            ->addColumn('class','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'异常模块'])
            ->addColumn('action','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'异常方法'])
            ->addColumn('type','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'文件类型,controller,model,validate,service,common等文件类型'])
            ->addColumn('file','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'异常文件'])
            ->addColumn('line','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'异常行号'])
            ->addColumn('error_file','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'报错文件'])
            ->addColumn('error_line','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'报错行号'])
            ->addColumn('message','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'异常信息'])
            ->addColumn('sql','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'执行的sql'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'更新时间'])
            ->addIndex('class')
            ->addIndex('type')
            ->addIndex('action')
            ->addIndex('create_time')
            ->create();
    }
}
