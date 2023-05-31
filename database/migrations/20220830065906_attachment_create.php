<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AttachmentCreate extends Migrator
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
        $table = $this->table('attachment',['engine'=>'innodb','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','primary_key'=>'id','comment'=>'上传附件表'])->addIndex('id');
        $table
            ->addColumn('name','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'原始名称'])
            ->addColumn('method','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'上传方法'])
            ->addColumn('topic','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'细目'])
            ->addColumn('admin_id','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'上传管理员ID'])
            ->addColumn('user_id','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'上传用户ID'])
            ->addColumn('url','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'物理路径'])
            ->addColumn('width','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'宽度'])
            ->addColumn('height','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'高度'])
            ->addColumn('size','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'大小'])
            ->addColumn('mimetype','string',['limit'=>128,'default'=>'','null'=>false,'comment'=>'mime类型'])
            ->addColumn('use_count','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'引用次数'])
            ->addColumn('storage','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'存储方式'])
            ->addColumn('sha1','string',['limit'=>255,'default'=>'','null'=>false,'comment'=>'sha1编码'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'default'=>0,'null'=>false,'comment'=>'更新时间'])
            ->addIndex('name')
            ->addIndex('mimetype')
            ->addIndex('create_time')
            ->create();
    }
}
