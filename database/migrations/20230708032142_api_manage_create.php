<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class ApiManageCreate extends Migrator
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
        $table = $this->table('api_manage',['engine'=>'innodb','charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','auto_increment'=>true,'comment'=>'接口应用管理']);
        $table
            ->addColumn('app_id','string',['limit'=>60,'null'=>false,'default'=>'','comment'=>'应用APPID'])
            ->addColumn('app_secret','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'应用APP_SECRET'])
            ->addColumn('public_key','text',['null'=>true,'default'=>null,'comment'=>'应用公钥'])
            ->addColumn('private_key','text',['null'=>true,'default'=>null,'comment'=>'应用私钥'])
            ->addColumn('username','string',['limit'=>60,'null'=>false,'default'=>'','comment'=>'用户名'])
            ->addColumn('phone','string',['limit'=>20,'null'=>false,'default'=>'','comment'=>'手机'])
            ->addColumn('email','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'邮箱'])
            ->addColumn('nickname','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'真实姓名'])
            ->addColumn('password','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'密码'])
            ->addColumn('salt','string',['limit'=>32,'null'=>false,'default'=>'','comment'=>'密码盐'])
            ->addColumn('avatar','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'头像'])
            ->addColumn('desc','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'描述'])
            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>1,'comment'=>'状态：1=正常，0=禁用'])
            ->addColumn('login_ip','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'最近登录IP'])
            ->addColumn('login_failure','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'登录失败次数'])
            ->addColumn('extension','json',['null'=>true,'default'=>null,'comment'=>'扩展信息'])
            ->addColumn('login_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'最后一次登录时间'])
            ->addColumn('create_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->setPrimaryKey('id')
            ->addIndex('id')
            ->addIndex('app_id')
            ->addIndex('username')
            ->addIndex('nickname')
            ->addIndex('phone')
            ->addIndex('email')
            ->addIndex('create_time')
            ->addIndex('update_time')
            ->create();
    }
}
