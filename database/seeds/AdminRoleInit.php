<?php

use think\migration\Seeder;

class AdminRoleInit extends Seeder
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
        $table = $this->table('admin_role');
        $data = [
            'id'	        =>	null,
            'admin_id'	    =>	1,
            'role_id'	    =>	1,
            'create_time'	=>	time(),
            'update_time'	=>	time()
        ];

        $table->insert($data)->saveData();
    }
}