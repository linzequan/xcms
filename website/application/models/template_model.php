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


    /**
     * 查询模板
     * @param  array $params 搜索条件参数
     * @param  array $order  结果排序
     * @param  int $page   结果页数
     * @return array         站点列表
     */
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


    /**
     * 新建新模板
     * @param  array $info 站点信息
     * @return array       操作成功与否反馈
     */
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


    /**
     * 更新模板信息
     * @param  int $site_id 站点id
     * @param  array $info    更新后的站点信息
     * @return array          操作成功与否反馈
     */
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


    /**
     * 删除模板
     * @param  int $site_id 站点id
     * @return array          操作成功与否反馈
     */
    public function delete($id) {
        $this->appdb->delete($this->table, array('tpl_id'=>$id));
        return $this->create_result(true, 0, array('tpl_id'=>$id));
    }


    /**
     * 查询站点对应模板，组装成combox列表
     * @return [type] [description]
     */
    public function getlist() {
        $data = $this->app_get_page($this->table, $this->fields, array(), array(), array('index'=>-1, 'size'=>-1));

        $result = array();
        foreach($data['rows'] as $k=>$v) {
            $result[$k]['id'] = $v['tpl_id'];
            $result[$k]['name'] = $v['alias_name'];
        }

        return $result;
    }

}
