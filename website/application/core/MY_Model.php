<?php
/**
 * 应用基础模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class MY_Model extends CI_Model {

    protected static $appdb;

    public function __construct() {
        parent::__construct();
        $sid = $this->input->get('sid') ? $this->input->get('sid') : 0;
        if($sid>0) {
            $this->loadAppDB($sid);
        }
    }


    /**
     * 标准化返回结果
     *
     * @param  boolean
     * @param  integer
     * @param  string
     * @return [type]
     */
    protected function create_result($success=false, $error=0, $data='') {
        return array('success'=>$success, 'error'=>$error, 'data'=>$data);
    }


    public function loadAppDB($sid='') {
        $dbconfig = $this->config->item('appdb');
        $dbconfig['database'] = getAppDB($sid);
        $this->appdb = $this->load->database($dbconfig, true);
        return true;
    }

    /**
     * 获取分页数据
     *
     * @param   string  数据库表
     * @param   string  读取字段
     * @param   mixed   读取数据条件eg: array(array('fieldName', 'fieldValue', 'optionType'))
     * @param   array   排序设置eg: array('fieldname asc', 'fieldname desc')
     * @param   array   分页设置eg: array('index'=>1, 'size'=>10)。$page为空时返回所有数据
     * @return  array   array('total'=>0,'rows'=>array());
     */
    public function get_page($table, $fields, $where=null, $order=array(), $page=array('index'=>1,'size'=>10), $group=array(), $join=array()) {
        $sql = 'select SQL_CALC_FOUND_ROWS ' . $fields . ' from ' . $table;

        if(is_array($join)) {
            foreach($join as $item) {
                $sql .= ' left join ' . $item[0] . ' on ' . $item[1];
            }
        }

        if($where!=null) {
            $str = '';
            if(is_array($where)==true) {
                $arr = array();
                foreach($where as $item) {
                    $field_name = $item[0];
                    $field_value = $item[1];
                    $option_type = isset($item[2]) ? $item[2] : '=';
                    switch($option_type) {
                        case 'like':
                            array_push($arr, $field_name." like '%".$this->db->escape_like_str($field_value)."%'");
                            break;
                        case 'not like':
                            array_push($arr, $field_name." not like '%".$this->db->escape_like_str($field_value)."%'");
                            break;
                        case 'like_r':
                            array_push($arr, $field_name." like '%".$this->db->escape_like_str($field_value)."'");
                            break;
                        case 'like_l':
                            array_push($arr, $field_name." like '".$this->db->escape_like_str($field_value)."%'");
                            break;
                        case 'in':
                        case 'not in':
                            array_push($arr, $field_name.' '.$option_type.'('.$this->db->escape_like_str($field_value).")");
                            break;
                        default:
                            array_push($arr, $field_name.' '.$option_type.' '.$this->db->escape($field_value));
                    }
                }
                $str = implode(' and ',$arr);
            } else {
                $str .= $where;
            }
            if($str!='') {
                $sql .= ' where ' . $str;
            }
        }

        if(count($group)) {
            $sql .= ' group by ' . implode($group, ',');
        }
        if(count($order)) {
            $sql .= ' order by ' . implode($order, ',');
        }

        if(!!count($page) && $page['index']!=-1 && $page['size']!=-1) {
            $sql .= " limit " . ($page['size']*($page['index']-1)) . ',' . $page['size'];
        }
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        $query->free_result();

        $query = $this->db->query('SELECT FOUND_ROWS() as rows_count');
        $total = $query->row(0)->rows_count;
        $query->free_result();
        return array('total'=>$total, 'rows'=>$rows);
    }


    /**
     * 获取分页数据
     *
     * @param   string  数据库表
     * @param   string  读取字段
     * @param   mixed   读取数据条件eg: array(array('fieldName', 'fieldValue', 'optionType'))
     * @param   array   排序设置eg: array('fieldname asc', 'fieldname desc')
     * @param   array   分页设置eg: array('index'=>1, 'size'=>10)。$page为空时返回所有数据
     * @return  array   array('total'=>0,'rows'=>array());
     */
    public function app_get_page($table, $fields, $where=null, $order=array(), $page=array('index'=>1,'size'=>10), $group=array(), $join=array()) {
        $sql = 'select SQL_CALC_FOUND_ROWS ' . $fields . ' from ' . $table;

        if(is_array($join)) {
            foreach($join as $item) {
                $sql .= ' left join ' . $item[0] . ' on ' . $item[1];
            }
        }

        if($where!=null) {
            $str = '';
            if(is_array($where)==true) {
                $arr = array();
                foreach($where as $item) {
                    $field_name = $item[0];
                    $field_value = $item[1];
                    $option_type = isset($item[2]) ? $item[2] : '=';
                    switch($option_type) {
                        case 'like':
                            array_push($arr, $field_name." like '%".$this->appdb->escape_like_str($field_value)."%'");
                            break;
                        case 'not like':
                            array_push($arr, $field_name." not like '%".$this->appdb->escape_like_str($field_value)."%'");
                            break;
                        case 'like_r':
                            array_push($arr, $field_name." like '%".$this->appdb->escape_like_str($field_value)."'");
                            break;
                        case 'like_l':
                            array_push($arr, $field_name." like '".$this->appdb->escape_like_str($field_value)."%'");
                            break;
                        case 'in':
                        case 'not in':
                            array_push($arr, $field_name.' '.$option_type.'('.$this->appdb->escape_like_str($field_value).")");
                            break;
                        default:
                            array_push($arr, $field_name.' '.$option_type.' '.$this->appdb->escape($field_value));
                    }
                }
                $str = implode(' and ',$arr);
            } else {
                $str .= $where;
            }
            if($str!='') {
                $sql .= ' where ' . $str;
            }
        }

        if(count($group)) {
            $sql .= ' group by ' . implode($group, ',');
        }
        if(count($order)) {
            $sql .= ' order by ' . implode($order, ',');
        }

        if(!!count($page) && $page['index']!=-1 && $page['size']!=-1) {
            $sql .= " limit " . ($page['size']*($page['index']-1)) . ',' . $page['size'];
        }
        $query = $this->appdb->query($sql);
        $rows = $query->result_array();
        $query->free_result();

        $query = $this->appdb->query('SELECT FOUND_ROWS() as rows_count');
        $total = $query->row(0)->rows_count;
        $query->free_result();
        return array('total'=>$total, 'rows'=>$rows);
    }
}
