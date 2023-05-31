<?php
// 应用公共文件

/**
 * 分类树形结构方法，需要有id 与 pid 的上下级对应关系
 */
if (!function_exists('tree')) {
    /**
     * 以pid——id对应，生成树形结构
     * @param array $array
     * @return array|bool
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
        switch ($type) {
            // 验证数组存在值，并且不能为空字符串，不能为空数组
            case 1:
                return isset($arr[$filed]) && !empty($arr[$filed]) && $arr[$filed] != '';
            case 2:
                return isset($arr[$filed]) && !empty($arr[$filed]);
            case 3:
                return isset($arr[$filed]) && $arr[$filed] != '';
            case 4:
                return isset($arr[$filed]);
            default:
                return false;
        }
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
 * 将一个文件单位转为字节
 */
if (!function_exists('file_unit_to_byte')) {
    /**
     * 将一个文件单位转为字节
     * @param string $unit 将b、kb、m、mb、g、gb的单位转为 byte
     */
    function file_unit_to_byte(string $unit): int
    {
        preg_match('/([0-9\.]+)(\w+)/', $unit, $matches);
        if (!$matches) {
            return 0;
        }
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        return (int)($matches[1] * pow(1024, $typeDict[strtolower($matches[2])] ?? 0));
    }
}


if (!function_exists('hsv2rgb')) {

    function hsv2rgb($h, $s, $v): array
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}


if (!function_exists('build_suffix_svg')) {
    /**
     * 构建文件后缀的svg图片
     * @param string $suffix     文件后缀
     * @param string|null $background 背景颜色，如：rgb(255,255,255)
     * @return string
     */
    function build_suffix_svg(string $suffix = 'file', string $background = null) : string
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total  = unpack('L', hash('adler32', $suffix, true))[1];
        $hue    = $total % 360;
        [$r, $g, $b] = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ?: "rgb({$r},{$g},{$b})";

        return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:' . $background . ';" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">' . $suffix . '</tspan></text></g>
        </svg>';
    }
}


if (!function_exists('full_url')) {
    /**
     * 获取资源完整url地址
     * @param string  $relativeUrl 资源相对地址 不传入则获取域名
     * @param boolean $domain      是否携带域名 或者直接传入域名
     * @return string
     */
    function full_url($relativeUrl = false, bool $domain = true, $default = '')
    {
        $cdnUrl = \think\facade\Env::get('upload.CDN');
        if (!$cdnUrl) $cdnUrl = request()->upload['cdn'] ?? request()->domain();
        if ($domain === true) {
            $domain = $cdnUrl;
        } elseif ($domain === false) {
            $domain = '';
        }

        $relativeUrl = $relativeUrl ?: $default;
        if (!$relativeUrl) return $domain;

        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        if (preg_match('/^http(s)?:\/\//', $relativeUrl) || preg_match($regex, $relativeUrl) || $domain === false) {
            return $relativeUrl;
        }
        return $domain . $relativeUrl;
    }
}
