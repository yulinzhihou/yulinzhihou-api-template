<?php
declare (strict_types = 1);

namespace app\command;

use think\console\command\Make;
use think\facade\Config;
use think\facade\Db;
use think\helper\Str;

class YV extends Make
{
    protected $type = "Validate";

    protected function configure()
    {
        parent::configure();
        $this->setName('yv:create')
            ->setDescription('Create a validate class for yulinzhihou Restful Api');
    }


    /**
     * 获取数据表字段相关系统
     * @param $db string 数据表名
     * @return array
     */
    public function getField($db) : array
    {
        $dbconnect = Db::connect('mysql');
        $prefix    = Config::get('database.connections.mysql.prefix');
        $dbname    = Config::get('database.connections.mysql.database');
        // 从数据库中获取表字段信息
        $sql = 'SELECT * FROM `information_schema`.`columns` '
            . 'WHERE TABLE_SCHEMA = ? AND table_name = ? '
            . 'ORDER BY ORDINAL_POSITION';
        // 加载主表的列
        $columnList = $dbconnect->query($sql, [$dbname,Str::snake($db)]);
        $priKey = 'id';
        foreach ($columnList as $v) {
            if ($v['COLUMN_KEY'] == 'PRI') {
                $priKey = $v['COLUMN_NAME'];
                break;
            }
        }

        // 生成验证规则
        $rules = [];
        $allFields = array_column($columnList,'COLUMN_NAME');
        $updateFields = $allFields;
        unset($allFields[array_search($priKey,$allFields)]);
        $saveFields = $allFields;
        // 场景验证
        $scenes = [
            'index'     => [],
            'save'      => $saveFields,
            'update'    => $updateFields,
            'read'      => [$priKey],
            'delete'    => [$priKey],
            'changeStatus' => [$priKey],
            'sortable'  => [$priKey],
        ];
        // 模型表单
        $schemas = [];

        foreach ($columnList as $column) {
            if (!in_array($column['COLUMN_NAME'],['create_time','update_time','delete_time'])) {
                $key = $column['COLUMN_NAME'] . "|" . $column['COLUMN_COMMENT'];
                // 取范围
                if ($column['DATA_TYPE'] == 'json') {
                    $value = ['length:0,'.($column['CHARACTER_MAXIMUM_LENGTH']-1)];
                } elseif ($column['DATA_TYPE'] == 'int') {
                    $value = false === strrpos($column['COLUMN_TYPE'],'unsigned') ? ['number','integer'] : ['egt:0','number','integer'];
                } elseif ($column['DATA_TYPE'] == 'tinyint') {
                    if ($column['COLUMN_NAME'] == 'status') {
                        $value = false === strrpos($column['COLUMN_TYPE'],'unsigned') ? ['number','integer','in:0,1'] : ['egt:0','in:0,1','number','integer'];
                    } else {
                        $value = false === strrpos($column['COLUMN_TYPE'],'unsigned') ? ['number','integer'] : ['egt:0','number','integer'];
                    }
                } elseif ($column['DATA_TYPE'] == 'varchar') {
                    $value = ['length:0,'.($column['CHARACTER_MAXIMUM_LENGTH']-1)];
                } elseif ($column['DATA_TYPE'] == 'text') {
                    $value = ['length:0,'.($column['CHARACTER_MAXIMUM_LENGTH']-1)];
                } elseif ($column['DATA_TYPE'] == 'float') {
                    $value = false === strrpos($column['COLUMN_TYPE'],'unsigned') ? ['float'] : ['egt:0','float'];
                } elseif ($column['DATA_TYPE'] == 'double') {
                    $value = false === strrpos($column['COLUMN_TYPE'],'unsigned') ? ['double'] : ['egt:0','double'];
                } elseif ($column['DATA_TYPE'] == 'mediumtext') {
                    $value = ['length:0,'.($column['CHARACTER_MAXIMUM_LENGTH']-1)];
                } elseif ($column['DATA_TYPE'] == 'longtext') {
                    $value = ['length:0,'.($column['CHARACTER_MAXIMUM_LENGTH']-1)];
                } elseif ($column['DATA_TYPE'] == 'bigint') {
                    $value = false === strrpos($column['COLUMN_TYPE'],'unsigned') ? ['number','integer'] : ['egt:0','number','integer'];
                } elseif ($column['DATA_TYPE'] == 'smallint') {
                    $value = false === strrpos($column['COLUMN_TYPE'],'unsigned') ? ['number','integer'] : ['egt:0','number','integer'];
                }
                $rules[$key] = $value;
                $schemas[$column['COLUMN_NAME']] = $column['COLUMN_TYPE'];
            }
        }


        return [
            'rules'     => $rules,
            'scenes'    => $scenes,
            'schemas'   => $schemas
        ];
    }

    protected function buildHtmlText($data) : string
    {
        $str = '';
        foreach ($data as $key => $item) {
            $str .= "\t\t'".$key."'\t=>\t";
            if (is_array($item)) {
                $str .= "[";
                foreach ($item as $k1 => $v1) {
                    $str .= !is_int($k1) ? "'".$k1."'\t=>\t".$v1."'," : "'".$v1."',";
                }
                $str= rtrim($str,',');
                $str .= "]";
            } else {
                $str .= "'".$item."'";
            }
            $str .= ",\n";
        }
        return $str;
    }

    protected function buildClass(string $name): string
    {
        $namespace   = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        $baseNamespace = trim(implode('\\', array_slice(explode('\\', $name), 0, 2)), '\\');

        $class = str_replace($namespace . '\\', '', $name);
        $stub  = file_get_contents($this->getStub());
        $namesArr = explode('\\',$name);
        $dbname = array_pop($namesArr);
        $fileds = $this->getField($dbname);
        $rules = $this->buildHtmlText($fileds['rules']);
        $scene = $this->buildHtmlText($fileds['scenes']);

        return str_replace(['{%baseNamespace%}','{%className%}','{%modelNamespace%}','{%validateNamespace%}', '{%namespace%}', '{%app_namespace%}','{%rules%}','{%scene%}'], [
            $baseNamespace,
            $class,
            $baseNamespace.'\model',
            $baseNamespace.'\validate',
            $namespace,
            $this->app->getNamespace(),
            $rules,
            $scene
        ], $stub);
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;

        return $stubPath . 'validate.stub';
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\validate';
    }
}
