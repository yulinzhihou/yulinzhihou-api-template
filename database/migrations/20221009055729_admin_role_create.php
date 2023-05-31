<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AdminRoleCreate extends Migrator
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
        $table = $this->table('admin_role',['engine'=>'innodb','charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','auto_increment'=>true,'comment'=>'管理员角色表']);
        $table
            ->addColumn('admin_id','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'管理员ID'])
            ->addColumn('role_id','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'角色组id'])
            ->addColumn('create_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->setPrimaryKey('id')
            ->addIndex('id')
            ->addIndex('create_time')
            ->addIndex('update_time')
            ->create();
    }
}
