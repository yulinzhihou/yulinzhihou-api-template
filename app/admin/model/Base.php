<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Log;
use think\Model;

/**
 * 后台模型基类
 */
class Base extends Model
{

    /**
     * 查看列表数据
     * @param int $page 分页页码
     * @param int $pageSize 分页类目数量
     * @param array $field 需要输入的字段，默认为全部输出，筛选表字段
     * @param array $vague 需要进行模糊查询的字段，如： a like %a%
     * @param array $focus 精确搜索条件，如a=1,b=2这种精确等于某个值的
     * @param array $order 排序字段，如：['id'=>'desc','sort'=>'desc']
     * @param array $range 区间字段，如：['create_time'=>['2022','2023']]
     * @return array
     */
    public function getIndexList(int $page,int $pageSize,array $field = [], array $vague = [],array $focus = [],array $order = [],array $range = []):array
    {
        try {
            $fields = array_keys($this->schema);
            // 排序条件整合
            if (!empty($order)) {
                $orderFields = array_keys($order);
                foreach ($orderFields as $orderField) {
                    if (!in_array($orderField,$fields)) {
                        unset($order[$orderField]);
                    }
                }
            }

            $collection = $this->field($field)->where(function ($query) use ($fields,$focus) {
                //精准查询
                if (!empty($focus)) {
                    foreach ($focus as $key => $item) {
                        if (in_array($key,$fields,true)) {
                            $query->where($key,$item);
                        }
                    }
                }
            })->where(function($query) use ($fields,$vague) {
                //模糊查询
                if (!empty($vague)) {
                    foreach ($vague as $key => $item) {
                        if (in_array($key,$fields,true)) {
                            $query->where($key,'like',"%".$item."%");
                        }
                    }
                }
            })->when(!empty($range),function ($query) use($fields,$range) {
                foreach ($range as $key => $item) {
                    if (in_array($key,$fields,true) && count($item) == 2) {
                        $query->whereBetween($key,$item);
                    }
                }
            } )->order($order);
            //判断是否需要分页
            if (0 == $page || 0 == $pageSize) {
                //不需要分页
                return $collection->select()->toArray();
            } else {
                //需要分布
                $totalNum = $collection->count();
                $data = $collection->page($page)->limit($pageSize)->select()->toArray();
                return ['total'=>$totalNum,'list'=>$data];
            }
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return [];
        }

    }

    /**
     * 新增数据
     * @param array $data
     * @return bool
     */
    public function addData(array $data) : bool
    {
        try {
            $result = $this->create($data);
            return (bool)$result;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 编辑数据
     * @param array $data
     * @return bool
     */
    public function editData(array $data) : bool
    {
        try {
            if (isset($data['id']) && $data['id'] > 0) {
                $result = $this->find($data['id']);
                return $result && $result->save($data);
            }
            return false;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 编辑状态
     * @param array $data
     * @return bool
     */
    public function changeStatus(array $data) : bool
    {
        return $this->editData($data);
    }

    /**
     * 拖拽排序
     * @param array $data
     * @return bool
     */
    public function sortable(array $data):bool
    {
        try {
            if (isset($data['id']) && $data['id'] > 0 && isset($data['targetId']) && $data['targetId'] > 0 ) {
                // 获取排序字段
                $sortFields = array_keys($this->schema);
                $sortField = in_array('weigh',$sortFields,true) ? 'weigh' : (in_array('sort',$sortFields,true) ? 'sort' : '');
                if ($sortField) {
                    // 获取当前
                    $current = $this->find($data['id']);
                    $target = $this->find($data['targetId']);
                    // 表示查询失败
                    if (!$current && !$target) {
                        return false;
                    }

                    if ($current[$sortField] == $target[$sortField])  {
                        $allData = $this->select();
                        foreach ($allData as $datum) {
                            $datum[$sortField] = 1000-$datum['id'];
                            $datum->save();
                        }
                        unset($allData);
                        // 互换排序
                        $current = $this->find($data['id']);
                        $target = $this->find($data['targetId']);
                    }
                    $tmpSortValue = $current[$sortField];
                    $result = $current->save([$sortField=>$target[$sortField]]);
                    $result1 = $target->save([$sortField=>$tmpSortValue]);
                    return $result && $result1;
                } else {
                    return false;
                }
            }
            return false;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 获取详情
     * @param int $id 主键
     * @param array $condition 查询条件,默认查全部 ，如状态['status' => 1]
     * @param array $field  字段筛选
     * @return array
     */
    public function getInfo(int $id,array $condition = [],array $field = []) : array
    {
        try {
            $result = $this->field($field)->where($condition)->find($id);
            return $result ? $result->toArray() : [];
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return [];
        }
    }

    /**
     * 通过关系获取详情
     * @param array $data 通过数据字段获取详情 ['name' = 'abc' ]
     * @param array $field  字段筛选，
     * @return array
     */
    public function getInfoByField(array $data,array $field = []) : array
    {
        try {
            if (!empty($data)) {
                $result = $this->field($field)->where($data)->find();
                return $result ? $result->toArray() : [];
            }
            return [];
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return [];
        }
    }

    /**
     * 删除
     * @param array $data
     * @return bool
     */
    public function delData(array $data) : bool
    {
        try {
            if (isset($data['id'])) {
                $result = $this->find($data['id']);
                return $result && $result->delete();
            }
            return false;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 通过ID获取数据
     * @param $ids
     * @return array
     */
    public function getExportData($ids): array
    {
        try {
            $result = $this->whereIn($this->pk,$ids['ids'])->order('create_time','desc')->select();
            return $result ? $result->toArray() : [];
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return [];
        }
    }

    /**
     * 通过主键进进行字段值的增加
     * @param string $field 需要自增的字段名
     * @param int $id   主键ID的值
     * @param int $value 步进数量，默认1
     * @return bool
     */
    public function setInc(string $field,int $id,int $value = 1): bool
    {
        try {
            $result = $this->findOrEmpty($id);
            if (!empty($result)) {
                $newValue = $result[$field] + $value;
                $res = $result->save([$field=>$newValue]);
                return (bool)$res;
            }
            return false;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 通过主键进进行字段值的减少
     * @param string $field 需要自减的字段名
     * @param int $id   主键ID的值
     * @param int $value 步进减少的值，默认为1
     * @return bool
     */
    public function setDec(string $field,int $id,int $value = 1): bool
    {
        try {
            $result = $this->findOrEmpty($id);
            if (!empty($result)) {
                $newValue = $result[$field] - $value;
                $res = $result->save([$field=>$newValue]);
                return (bool)$res;
            }
            return false;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 批量修改
     * @param $data
     * @return bool
     */
    public function batchEditData($data):bool
    {
        try {
            $result = $this->saveAll($data);
            return (bool)$result;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 批量删除
     * @param array $data
     * @return bool
     */
    public function batchDelData(array $data) : bool
    {
        try {
            $res = true;
            foreach ($data as $item) {
                if (isset($item['id']) && $item['id'] > 0) {
                    $result = $this->find($item['id']);
                    $result = $result->delete();
                    $res = $res && $result;
                }
            }
            return $res;
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }

    /**
     * 根据 ID 查询菜单名称
     * @param $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuNameById($id) :string
    {
        $data = Menu::where('id',$id)->limit(1)->select()->toArray();
        return !empty($data) ? $data[0]['title'] : '';
    }


    /**
     * 通过文件类型ID，得到对应的模型名
     * @param $fileTypeId
     * @return array
     */
    public function getFileModelById($fileTypeId):array
    {
        $name = [];
        switch ($fileTypeId) {
            case 1:
                $name['modelName'] = '\app\admin\model\CommonItem';
                $name['name'] = 'CommonItem';
                break;
            case 2:
                $name['modelName'] = '\app\admin\model\GemInfo';
                $name['name'] = 'GemInfo';
                break;
            case 3:
                $name['modelName'] = '\app\admin\model\EquipBase';
                $name['name'] = 'EquipBase';
                break;
            case 4:
                $name['modelName'] = '\app\admin\model\PetAttr';
                $name['name'] = 'PetAttr';
                break;
            case 5:
                $name['modelName'] = '\app\admin\model\Monster';
                $name['name'] = 'Monster';
                break;
            case 6:
                $name['modelName'] = '\app\admin\model\DropBox';
                $name['name'] = 'DropBox';
                break;
            case 7:
                $name['modelName'] = '\app\admin\model\MonsterDrop';
                $name['name'] = 'MonsterDrop';
                break;
            default:
                break;
        }
        return $name;
    }
}
