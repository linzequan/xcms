<?php
/**
 * 模板管理模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class template_model extends MY_Model {

    private $table = 'template';
    private $fields = 'tpl_id, tpl_name, alias_name, url_rule, tpl_content, create_user, create_time, update_user, update_time';

    public function __construct() {
        parent::__construct();
    }


    public function search($params, $order, $page) {
        $where = array();
        if(count($order)==0) {
            $order[] = ' tpl_id desc ';
        }
        $datas = $this->app_get_page($this->table, $this->fields, $where, $order, $page);
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
            $datas['rows'][$k]['vid'] = $v['tpl_id'];
        }
        return $datas;
    }


    public function insert($info) {
        $query = $this->appdb->where('tpl_name', $info['tpl_name'])->select('tpl_id')->get($this->table);
        $count = $query->num_rows();
        $query->free_result();
        if($count>0) {
            return $this->create_result(false, 1, '模板名称已经存在');
        }
        $info['create_user'] = $this->session->userdata('user_id');
        $info['create_time'] = time();
        $this->appdb->insert($this->table, $info);
        $id = $this->appdb->insert_id();

        return $this->create_result(true, 0, array('id'=>$id));
    }


    public function update($id, $info) {
        $query = $this->appdb->where(array('tpl_name'=>$info['tpl_name'], 'tpl_id !='=>$id))->select('tpl_id')->get($this->table);
        $count = $query->num_rows();
        $query->free_result();
        if($count>0) {
            return $this->create_result(false, 1, '模板名称已经存在');
        }
        $info['update_user'] = $this->session->userdata('user_id');
        $info['update_time'] = time();
        $this->appdb->update($this->table, $info, array('tpl_id'=>$id));
        return $this->create_result(true, 0, array('tpl_id'=>$id));
    }


    public function delete($id) {
        $this->appdb->delete($this->table, array('tpl_id'=>$id));
        return $this->create_result(true, 0, array('tpl_id'=>$id));
    }

}
