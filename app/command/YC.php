<?php
declare (strict_types = 1);

namespace app\command;

use think\console\command\Make;

/**
 * Yulinzhihou 专用生成接口控制器
 */
class YC extends Make
{

    protected $type = "Controller";

    protected function configure()
    {
        parent::configure();
        $this->setName('yc:create')
            ->setDescription('Create a new resource route controller class for yulinzhihou Restful Api');
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;
        return $stubPath . 'controller.stub';
    }

    protected function buildClass(string $name): string
    {
        $namespace   = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        $baseNamespace = trim(implode('\\', array_slice(explode('\\', $name), 0, 2)), '\\');

        $class = str_replace($namespace . '\\', '', $name);
        $stub  = file_get_contents($this->getStub());

        return str_replace(['{%baseNamespace%}','{%className%}','{%modelNamespace%}','{%validateNamespace%}', '{%namespace%}', '{%app_namespace%}'], [
            $baseNamespace,
            $class,
            $baseNamespace.'\model',
            $baseNamespace.'\validate',
            $namespace,
            $this->app->getNamespace(),
        ], $stub);
    }

    protected function getClassName(string $name): string
    {
        return parent::getClassName($name) . ($this->app->config->get('route.controller_suffix') ? 'Controller' : '');
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\controller';
    }

}
