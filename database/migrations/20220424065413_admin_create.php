<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class AdminCreate extends Migrator
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
        $table = $this->table('admin',['engine'=>'innodb','charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','auto_increment'=>true,'comment'=>'管理员表']);
        $table
            ->addColumn('username','string',['limit'=>60,'null'=>false,'default'=>'','comment'=>'用户名'])
            ->addColumn('password','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'密码'])
            ->addColumn('salt','string',['limit'=>32,'null'=>false,'default'=>'','comment'=>'密码盐'])
            ->addColumn('nickname','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'真实姓名'])
            ->addColumn('avatar','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'头像'])
            ->addColumn('desc','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'描述'])
            ->addColumn('role_id','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'角色组id'])
            ->addColumn('phone','string',['limit'=>20,'null'=>false,'default'=>'','comment'=>'手机'])
            ->addColumn('email','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'邮箱'])
            ->addColumn('extension','json',['null'=>true,'default'=>null,'comment'=>'扩展信息'])
            ->addColumn('sort','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>1,'comment'=>'排序0-255(越小越前)'])
            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>1,'comment'=>'状态：为1正常，为0禁用'])
            ->addColumn('login_ip','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'最近登录IP'])
            ->addColumn('login_failure','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'登录失败次数'])
            ->addColumn('login_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'最后一次登录时间'])
            ->addColumn('create_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->setPrimaryKey('id')
            ->addIndex('id')
            ->addIndex('username')
            ->addIndex('nickname')
            ->addIndex('phone')
            ->addIndex('email')
            ->addIndex('create_time')
            ->addIndex('update_time')
            ->create();
    }
}
