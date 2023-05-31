<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SystemConfigCreate extends Migrator
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
        $table = $this->table('system_config',['engine'=>'innodb','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','primary_key'=>'id','comment'=>'系统配置表'])->addIndex('id');
        $table
            ->addColumn('name','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'变量名'])
            ->addColumn('group','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'分组'])
            ->addColumn('title','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'变量标题'])
            ->addColumn('tip','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'变量描述'])
            ->addColumn('type','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'类型:string,number,radio,checkbox,switch,textarea,array,datetime,date,select,selects'])
            ->addColumn('value','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'变量值'])
            ->addColumn('content','text',['limit'=>16777215,'default'=>null,'null'=>true,'comment'=>'字典数据'])
            ->addColumn('rule','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'验证规则'])
            ->addColumn('extend','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'扩展属性'])
            ->addColumn('allow_del','integer',['limit'=>\Phinx\Db\Adapter\MysqlAdapter::INT_TINY,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'允许删除:0=否,1=是'])
            ->addColumn('weigh','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'权重'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'更新时间'])
            ->addIndex('name')
            ->addIndex('type')
            ->addIndex('create_time')
            ->create();
    }
}
