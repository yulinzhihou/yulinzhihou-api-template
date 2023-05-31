<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class RoleCreate extends Migrator
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
        $table = $this->table('role',['engine'=>'innodb','charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','auto_increment'=>true,'comment'=>'角色表']);
        $table
            ->addColumn('pid','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'父级角色'])
            ->addColumn('name','string',['limit'=>32,'null'=>false,'default'=>'','comment'=>'角色组名称'])
            ->addColumn('value','string',['limit'=>50,'null'=>false,'default'=>'','comment'=>'角色值'])
            ->addColumn('menu','text',['null'=>true,'default'=>null,'comment'=>'权限菜单ID列表'])
            ->addColumn('remark','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'简介'])
            ->addColumn('sort','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>1,'comment'=>'排序0-255(越小越前)'])
            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>1,'comment'=>'状态：为1正常，为0禁用'])
            ->addColumn('create_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->setPrimaryKey('id')
            ->addIndex('id')
            ->addIndex('name')
            ->addIndex('create_time')
            ->addIndex('update_time')
            ->create();
    }
}
