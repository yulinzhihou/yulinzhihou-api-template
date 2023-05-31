<?php

use think\migration\Seeder;

class SystemConfigInit extends Seeder
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


        $json = [
            [
                "id" => 1,
                "name" => "config_group",
                "group" => "basics",
                "title" => "Config group",
                "tip" => "",
                "type" => "array",
                "value" => '[{"key":"basics","value":"Basics"},{"key":"mail","value":"Mail"},{"key":"config_quick_entrance","value":"Config Quick entrance"}]',
                "content" => null,
                "rule" => "required",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 2,
                "name" => "site_name",
                "group" => "basics",
                "title" => "Site Name",
                "tip" => "站点名称",
                "type" => "string",
                "value" => "BuildAdmin",
                "content" => null,
                "rule" => "required",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 99
            ],[
                "id" => 3,
                "name" => "record_number",
                "group" => "basics",
                "title" => "Record number",
                "tip" => "域名备案号",
                "type" => "string",
                "value" => "渝ICP备8888888号-1",
                "content" => null,
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 4,
                "name" => "version",
                "group" => "basics",
                "title" => "Version number",
                "tip" => "系统版本号",
                "type" => "string",
                "value" => "v1.0.0",
                "content" => null,
                "rule" => "required",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 5,
                "name" => "time_zone",
                "group" => "basics",
                "title" => "time zone",
                "tip" => "",
                "type" => "string",
                "value" => "Asia/Shanghai",
                "content" => null,
                "rule" => "required",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 6,
                "name" => "no_access_ip",
                "group" => "basics",
                "title" => "No access ip",
                "tip" => "禁止访问站点的ip列表,一行一个",
                "type" => "textarea",
                "value" => "",
                "content" => null,
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 7,
                "name" => "smtp_server",
                "group" => "mail",
                "title" => "smtp server",
                "tip" => "",
                "type" => "string",
                "value" => "smtp.qq.com",
                "content" => null,
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 99
            ],[
                "id" => 8,
                "name" => "smtp_port",
                "group" => "mail",
                "title" => "smtp port",
                "tip" => "",
                "type" => "string",
                "value" => "465",
                "content" => null,
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 9,
                "name" => "smtp_user",
                "group" => "mail",
                "title" => "smtp user",
                "tip" => "",
                "type" => "string",
                "value" => "",
                "content" => null,
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 10,
                "name" => "smtp_pass",
                "group" => "mail",
                "title" => "smtp pass",
                "tip" => "",
                "type" => "string",
                "value" => "",
                "content" => null,
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 11,
                "name" => "smtp_verification",
                "group" => "mail",
                "title" => "smtp verification",
                "tip" => "",
                "type" => "select",
                "value" => "SSL",
                "content" => '{"SSL":"SSL","TLS":"TLS"}',
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 12,
                "name" => "smtp_sender_mail",
                "group" => "mail",
                "title" => "smtp sender mail",
                "tip" => "",
                "type" => "string",
                "value" => "",
                "content" => null,
                "rule" => "email",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ],[
                "id" => 13,
                "name" => "config_quick_entrance",
                "group" => "config_quick_entrance",
                "title" => "Config Quick entrance",
                "tip" => "",
                "type" => "array",
                "value" => '[{"key":"\u6570\u636e\u56de\u6536\u89c4\u5219\u914d\u7f6e","value":"\/admin\/security\/dataRecycle"},{"key":"\u654f\u611f\u6570\u636e\u89c4\u5219\u914d\u7f6e","value":"\/admin\/security\/sensitiveData"}]',
                "content" => null,
                "rule" => "",
                "extend" => "",
                "allow_del" => 0,
                "weigh" => 0
            ]
        ];
        $table = $this->table('system_config');
        $table->insert($json)->saveData();

    }
}