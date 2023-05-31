<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class MenuCreate extends Migrator
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
        $table = $this->table('menu',['engine'=>'innodb','charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','auto_increment'=>true,'comment'=>'菜单权限表']);
        $table
            ->addColumn('pid','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'父ID'])
            ->addColumn('name','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'路由名称'])
            ->addColumn('path','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'路由地址'])
            ->addColumn('component','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'组件地址'])
            ->addColumn('title','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'页面title'])
            ->addColumn('icon','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'图标'])
            ->addColumn('url','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'重定向地址'])
            ->addColumn('remark','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'备注标记'])
            ->addColumn('type','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'类型'])
            ->addColumn('menu_type','string',['limit'=>128,'null'=>true,'default'=>null,'comment'=>'菜单类型'])
            ->addColumn('extend','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'拓展'])
            ->addColumn('keep_alive','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'是否需要缓存该路由：1是，0否'])
            ->addColumn('weigh','integer',['limit'=>MysqlAdapter::INT_SMALL,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'菜单排序,只对第一级有效：0-65535'])
            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>1,'comment'=>'状态：为1正常，为0禁用'])
            ->addColumn('create_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->setPrimaryKey('id')
            ->addIndex('id')
            ->addIndex('name')
            ->addIndex('path')
            ->addIndex('title')
            ->addIndex('type')
            ->addIndex('menu_type')
            ->addIndex('status')
            ->addIndex('create_time')
            ->addIndex('update_time')
            ->create();
    }
}
