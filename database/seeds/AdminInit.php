<?php

use think\migration\Seeder;

class AdminInit extends Seeder
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
        $table = $this->table('admin');
        $data = [
            [
                "id" => null,
                "username" => "admin",
                "password" => '$argon2i$v=19$m=65536,t=4,p=1$czNwT21yRHo5bWJBeThiOQ$zghwv1kcDJYC9FEa2AQwu4Nn0Gcles3c7PMpmxdHc8U',
                "salt" => "LEbxBkws",
                "nickname" => "系统管理员",
                "avatar" => "https://ghproxy.com/https://raw.githubusercontent.com/yulinzhihou706/gsgameshare/master/wp-content/uploads/2022/09/1663558974-3989f02efe32482.png",
                "desc" => "系统杠霸子，统领整个系统所有功能",
                "phone" => "18888888888",
                "email" => "yulinzhihou@163.com",
                "extension" => "[]",
                "sort" => 1,
                "status" => 1,
                "login_ip" => "127.0.0.1",
                "create_time" => 1653899819,
                "update_time" => 1661831033
            ]
        ];
        $table->insert($data)->saveData();
    }
}