<?php
namespace app\library;

use app\admin\model\ExceptionLog;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\facade\Log;

/**
 * 异步核心类-异步队列执行的方法
 */
class AsyncBase
{
    /**
     * 执行单条异步数据到数据库。支持多并发
     * @param string $model 模型编号
     * @param int $versionId 版本ID
     * @param string $fileType 文件类型
     * @param string $index 文件对应的索引编号字段，如：item_id,gem_id
     * @param array $data   行数据
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function doAsyncItemToMysql(string $model, int $versionId, string $fileType, string $index, array $data):void
    {
        $isDataExists = $model::where('version_id',$versionId)->where($index,$data[$index])->find();
        if ($isDataExists) {
            try {
                $isDataExists->save($data);
            } catch (\Exception $e) {
                ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model','');
                Log::record($e->getMessage());
            }
        } else {
            try {
                $model::create($data);
            } catch (\Exception $e) {
                ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model','');
                Log::record($e->getMessage());
            }
        }
        Cache::delete($fileType.':version_'.$versionId.':'.$data[$index]);
    }

}