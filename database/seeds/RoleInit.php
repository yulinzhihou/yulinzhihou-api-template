<?php

use think\migration\Seeder;

class RoleInit extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $table = $this->table('role');
        $data = [
            'id'	    =>	1,
            'name'	    =>	'超级管理员',
            'value'	    =>	'Super',
            'menu'	    =>	'*',
            'remark'	=>	'拥有最高权限',
            'sort'	    =>	1,
            'status'	=>	1,
            'create_time'	=>	time(),
            'update_time'	=>	time(),

        ];

        $table->insert($data)->saveData();
    }
}