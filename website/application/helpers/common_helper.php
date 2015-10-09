<?php
/**
 * @author linzequan <lowkey361@gmail.com>
 *
 */
function check_pms($opt) {
    $pms_opts = $GLOBALS['pms_opts'];
    $ctrl_pms = $GLOBALS['ctrl_pms'];
    if(isset($pms_opts[$opt])==false) {
        return false;
    }
    $index = $pms_opts[$opt];
    if($ctrl_pms[$index]=='1') {
        return true;
    } else {
        return false;
    }
}


function get_value(&$arr, $key, $default='') {
    if(isset($arr[$key])) {
        return $arr[$key];
    } else {
        return $default;
    }
}


function create_datagrid_data($total, $rows) {
    return array('total'=>$total, 'rows'=>$rows);
}


function get_datagrid_page() {
    $index = get_value($_POST, 'page', 1);
    $size = get_value($_POST, 'rows', 50);
    if($index<=0) {
        $index = 1;
    }
    return array('index'=>$index, 'size'=>$size);
}


function get_datagrid_order() {
    $sort = get_value($_POST, 'sort', '');
    $order = get_value($_POST, 'order', '');
    if($sort=='') {
        return array();
    } else {
        $result = array();
        $arr_sort = explode(',', $sort);
        $arr_order = explode(',', $order);
        foreach($arr_sort as $key=>$val) {
            array_push($result, $arr_sort[$key].' '.$arr_order[$key]);
        }
        return $result;
    }
}


function create_tree_list(&$input, &$output, $pid=0, $level=0, $config=array('id_key'=>'id', 'pid_key'=>'pid')) {
    $id_key = $config['id_key'];
    $pid_key = $config['pid_key'];
    $num =count($input);
    for($i=0; $i<$num; $i++) {
        if($input[$i][$pid_key]==$pid) {
            $input[$i]['level'] = $level;
            $input[$i]['is_leaf'] = true;
            $output[$input[$i][$id_key]] = $input[$i];
            if($pid>0) {
                $output[$pid]['is_leaf'] = false;
            }
            create_tree_list($input, $output, $input[$i][$id_key], ($level+1), $config);
        }
    }
}


/**
 * 根据过滤$info中的索引值，保留$fields指定的索引值
 */
function filter_fields($fields,$filter) {
    $filters = explode(',', $filter);
    $arr = explode(',', $fields);
    $rul = array();
    foreach($arr as $val) {
        if(in_array($val, $filters)) {
            array_push($rul, $val);
        }
    }
    return implode(',', $rul);
}


/**
 * 根据过滤提供的字段列表$fields，自动过滤掉输入数据库的字段值
 */
function filter_data($rows, $fields) {
    $result = array();
    $arr_fields = explode(',', $fields);
    if(isset($rows[0])==false) {
        foreach($rows as $key=>$val) {
            if(in_array($key, $arr_fields)==true) {
                $result[$key] = $val;
            }
        }
    } else {
        $row = array();
        foreach($rows as $item) {
            foreach($item as $key=>$val) {
                if(in_array($key, $arr_fields)==true) {
                    $row[$key] = $val;
                }
            }
            array_push($result, $row);
        }
    }
    return $result;
}


function fields2array($fields) {
    $arr = explode(',', $fields);
    $result = array();
    foreach($arr as $key) {
        $result[trim($key,' ')] = '';
    }
    return $result;
}


/**
 * 根据相同键值(join_key)合并两个二维数组$list1,$list2
 *
 * @param array $list1
 * @param array $list2
 * @param string $join_key
 * @return array
 */
function array_join($list1, $list2, $join_key, $right_fields='') {
    $def_item = fields2array($right_fields);
    foreach($list1 as &$item1) {
        $val = $item1[$join_key];
        $exist = false;
        foreach($list2 as $item2) {
            if($val==$item2[$join_key]) {
                $item1 = array_merge($item1, $item2);
                $exist = true;
                break;
            }
        }
        if($exist==false) {
            $item1 = array_merge($def_item, $item1);
        }
    }
    return $list1;
}
