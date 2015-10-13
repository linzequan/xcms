<?php
/**
 * 模板管理模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class template_model extends MY_Model {

    private $table = 'site';
    private $fields = 'site_id, site_name, alias_name, domain, description, create_user, create_time, update_user, update_time';

    public function __construct() {
        parent::__construct();
    }


    public function search($params, $order, $page) {
        $where = array();
        if(count($order)==0) {
            $order[] = ' site_id desc ';
        }
        $datas = $this->get_page($this->table, $this->fields, $where, $order, $page);
        $this->load->model('sys/user_model', 'user_model');
        $CI = &get_instance();
        foreach($datas['rows'] as $k=>$v) {
            if($userinfo=$CI->user_model->get_userinfo_by_id($v['create_user'])) {
                $datas['rows'][$k]['create_user'] = $userinfo['user_name'];
            } else {
                $datas['rows'][$k]['create_user'] = '';
            }
            if($userinfo=$CI->user_model->get_userinfo_by_id($v['update_user'])) {
                $datas['rows'][$k]['update_user'] = $userinfo['user_name'];
            } else {
                $datas['rows'][$k]['update_user'] = '';
            }
            $v['create_time']==null ? '' : $datas['rows'][$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            $v['update_time']==null ? '' : $datas['rows'][$k]['update_time'] = date('Y-m-d H:i:s', $v['update_time']);
            $datas['rows'][$k]['vid'] = $v['site_id'];
        }
        return $datas;
    }


    public function insert($info) {
        $query = $this->db->where('site_name', $info['site_name'])->select('site_id')->get($this->table);
        $count = $query->num_rows();
        $query->free_result();
        if($count>0) {
            return $this->create_result(false, 1, '站点名称已经存在');
        }
        $info['create_user'] = $this->session->userdata('user_id');
        $info['create_time'] = time();
        $this->db->insert($this->table, $info);
        $id = $this->db->insert_id();

        // 创建应用数据库
        if(!$this->create_sitedb($id)) {
            $this->delete($id);
            return $this->create_result(false, 2, '创建应用数据库失败');
        }
        return $this->create_result(true, 0, array('id'=>$id));
    }


    public function update($site_id, $info) {
        $query = $this->db->where(array('site_name'=>$info['site_name'], 'site_id !='=>$site_id))->select('site_id')->get($this->table);
        $count = $query->num_rows();
        $query->free_result();
        if($count>0) {
            return $this->create_result(false, 1, '站点名称已经存在');
        }
        $this->db->update($this->table, $info, array('site_id'=>$site_id));
        return $this->create_result(true, 0, array('site_id'=>$site_id));
    }


    public function delete($site_id) {
        $this->db->delete($this->table, array('site_id'=>$site_id));
        return $this->create_result(true, 0, array('site_id'=>$site_id));
    }


    private function create_sitedb($site_id) {
        $dbname = 'xcms_db' . $site_id;
        $query = $this->db->query('CREATE DATABASE IF NOT EXISTS `' . $dbname . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');
        return $query;
    }

}
