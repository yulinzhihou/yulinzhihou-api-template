<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 应用公共文件
// +----------------------------------------------------------------------

/**
 * 分类树形结构方法，需要有id 与 pid 的上下级对应关系
 */
if (!function_exists('tree')) {
    /**
     * 以pid——id对应，生成树形结构
     * @param array $array
     * @return array
     */
    function tree(array $array):array
    {
        $tree = [];     // 生成树形结构
        $newArray = []; // 中转数组，将传入的数组转换

        if (!empty($array)) {
            foreach ($array as $item) {
                $newArray[$item['id']] = $item;  // 以传入数组的id为主键，生成新的数组
            }
            foreach ($newArray as $k => $val) {
                if ($val['pid'] > 0) {           // 默认pid = 0时为一级
                    $newArray[$val['pid']]['children'][] = &$newArray[$k];   // 将pid与主键id相等的元素放入children中
                } else {
                    $tree[] = &$newArray[$val['id']];   // 生成树形结构
                }
            }
            return $tree;
        } else {
            return [];
        }
    }
}

/**
 * 判断数据是否为空
 * @param $arr array 要检查的数组
 * @param $field string 判断的字段名
 * @param $type int 判断的类型. 1=存在+空+空字符
 * @return bool 验证通过返回true,否则为false
 */
if (!function_exists('isVarExists')) {
    function isVarExists(array $arr,string $filed,int $type = 1):bool {
        return match ($type) {
            1 => !empty($arr[$filed]) && $arr[$filed] != '',
            2 => !empty($arr[$filed]),
            3 => isset($arr[$filed]) && $arr[$filed] != '',
            4 => isset($arr[$filed]),
            default => false,
        };
    }
}

/**
 * 生成符号结构的树形结构的层级关系
 */
if (!function_exists('getTreeRemark')) {
    /**
     * 将数组渲染为树状,需自备children children可通过$this->assembleChild()方法组装
     * @param array  $arr         要改为树状的数组
     * @param string $field       '树枝'字段
     * @param int    $level       递归数组层次,无需手动维护
     * @param false  $superiorEnd 递归上一级树枝是否结束,无需手动维护
     * @return array
     *
     */
    function getTreeRemark(array $arr, string $field = 'name', int $level = 0, bool $superiorEnd = false): array
    {
        $icon = ['│', '├', '└'];
        $level++;
        $number = 1;
        $total  = count($arr);
        foreach ($arr as $key => $item) {
            $prefix = ($number == $total) ? $icon[2] : $icon[1];
            if ($level == 2) {
                $arr[$key][$field] = str_pad('', 4) . $prefix . $item[$field];
            } elseif ($level >= 3) {
                $arr[$key][$field] = str_pad('', 4) . ($superiorEnd ? '' : $icon[0]) . str_pad('', ($level - 2) * 4) . $prefix . $item[$field];
            }

            if (isset($item['children']) && $item['children']) {
                $arr[$key]['children'] = getTreeRemark($item['children'], $field, $level, $number == $total);
            }
            $number++;
        }
        return $arr;
    }

}


/**
 * 递归合并树状数组,多维变二维
 */
if (!function_exists('assembleTree')) {

    /**
     * 递归合并树状数组,多维变二维
     * @param array $data 要合并的数组
     * @return array
     */
    function assembleTree(array $data):array
    {
        $arr = [];
        foreach ($data as $v) {
            $children = $v['children'] ?? [];
            unset($v['children']);
            $arr[] = $v;
            if ($children) {
                $arr = array_merge($arr, assembleTree($children));
            }
        }
        return $arr;
    }
}



/**
 * 递归
 * @param $data array 需要递归的数据
 * @param $pid integer 上级ID
 * @param $pf string 上级的字段名
 * @param $unset bool 为true则去除pid字段
 * @return array
 */
if (!function_exists('recursive')) {
    function recursive($data, $pid = 0, $pf = 'pid', $unset = false): array
    {
        $result = [];
        foreach ($data as $key => $val) {
            if ($val[$pf] == $pid) {
                if ($unset) unset($val[$pf]);
                $result[$key] = $val;
                if ($recur = recursive($data, $val['id'], $pf, $unset)) {
                    $result[$key]['children'] = array_values($recur);
                }
            }
        }
        return array_reverse($result);
    }
}





/**
 * Curl 请求
 */
if (function_exists('http')) {
    /**
     * curl请求
     * @param $url  string 请求的url链接
     * @param $data string|array|mixed 请求的数据
     * @param bool $is_post 是否是post请求，默认false
     * @param array $options 是否附带请求头
     * @return array
     */
    function http(string $url, array $data, bool $is_post=false, array $options=[]):array
    {
        $data  = json_encode($data);
        $headerArray = [
            'Content-type: application/json;charset=utf-8',
            'Accept: application/json'
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
        if ($is_post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($options['cookie'])) {
            curl_setopt($curl, CURLOPT_COOKIE, $options['cookie']);
        } else {
            $headerArray = array_merge($headerArray,$options);
        }
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($curl);
        $http_status = curl_errno($curl);
        $http_msg = curl_error($curl);
        curl_close($curl);
        if ($http_status == 0) {
            return json_decode($output, true);
        } else {
            return ['status' => $http_status, 'message' => $http_msg, 'data' => []];
        }
    }

}