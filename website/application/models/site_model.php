<?php
/**
 * 站点管理模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class site_model extends MY_Model {

    private $table = 'site';
    private $fields = 'site_id, site_name, alias_name, domain, description, create_user, create_time, update_user, update_time';

    public function __construct() {
        parent::__construct();
    }


    /**
     * 查询站点
     * @param  array $params 搜索条件参数
     * @param  array $order  结果排序
     * @param  int $page   结果页数
     * @return array         站点列表
     */
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


    /**
     * 新建新站点
     * @param  array $info 站点信息
     * @return array       操作成功与否反馈
     */
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

        // 应用数据库创建模板信息表
        if(!$this->create_tplinfo($id)) {
            $this->delete($id);
            return $this->create_result(false, 3, '创建模板信息表失败');
        }

        return $this->create_result(true, 0, array('id'=>$id));
    }


    /**
     * 更新站点信息
     * @param  int $site_id 站点id
     * @param  array $info    更新后的站点信息
     * @return array          操作成功与否反馈
     */
    public function update($site_id, $info) {
        $query = $this->db->where(array('site_name'=>$info['site_name'], 'site_id !='=>$site_id))->select('site_id')->get($this->table);
        $count = $query->num_rows();
        $query->free_result();
        if($count>0) {
            return $this->create_result(false, 1, '站点名称已经存在');
        }
        $info['update_user'] = $this->session->userdata('user_id');
        $info['update_time'] = time();
        $this->db->update($this->table, $info, array('site_id'=>$site_id));
        return $this->create_result(true, 0, array('site_id'=>$site_id));
    }


    /**
     * 删除站点
     * @param  int $site_id 站点id
     * @return array          操作成功与否反馈
     */
    public function delete($site_id) {
        // 删除应用数据库
        if(!$this->delete_sitedb($site_id)) {
            return $this->create_result(false, 1, '删除应用数据库失败');
        }
        $this->db->delete($this->table, array('site_id'=>$site_id));
        return $this->create_result(true, 0, array('site_id'=>$site_id));
    }


    /**
     * 创建对应站点数据库
     * @param  int $site_id 站点id
     * @return boolean          成功与否标识
     */
    private function create_sitedb($site_id) {
        $dbname = getAppDB($site_id);
        $query = $this->db->query('CREATE DATABASE IF NOT EXISTS `' . $dbname . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');
        return $query;
    }


    /**
     * 删除对应站点数据库
     * @param  int $site_id 站点id
     * @return boolean          成功与否标志
     */
    private function delete_sitedb($site_id) {
        $dbname = getAppDB($site_id);
        $query = $this->db->query('drop database ' . $dbname);
        return $query;
    }


    /**
     * 初始化站点数据库所需数据表
     * @param  int $site_id 站点id
     * @return boolean          成功与否标志
     */
    private function create_tplinfo($site_id) {
        $this->loadAppDB($site_id);
        // 创建模板表
        $query_tpl = $this->appdb->query("create table if not exists `template` (`tpl_id` int(11) not null auto_increment comment '模板ID', `tpl_name` varchar(255) not null comment '模板名称', `alias_name` varchar(255) not null comment '别名', `url_rule` varchar(255) not null comment '链接规则', `tpl_content` text comment '模板内容', `create_user` int(11) comment '创建者ID', `create_time` int(11) comment '创建时间戳', `update_user` int(11) comment '更新者ID', `update_time` int(11) comment '更新时间戳', primary key (`tpl_id`), unique key (`tpl_name`)) engine=innodb default charset=utf8 comment='模板信息表'");
        // 创建模板域表
        $query_tf = $this->appdb->query("create table if not exists `template_field` ( `tf_id` int(11) not null auto_increment comment '模板域id', `tpl_id` int(11) not null comment '所属模板id', `tf_name` varchar(255) not null comment '模板域名称', `tf_sign` varchar(255) not null comment '模板域标识，由系统自动生成', `type` int(4) not null comment '类型', `is_savedb` int(1) not null default 0 comment '是否入库，默认0保存，1不保存', `status` int(1) not null default 0 comment '是否启用，默认0启用，1不启用', `version` varchar(128) default 0.00 comment '版本号', `sort` int(11) default 100 comment '排序，默认100。数字越大排序越前', `build_sort` int(11) default 100 comment '编译排序，默认100.数字越大越先编译', `create_user` int(11) comment '创建者ID', `create_time` int(11) comment '创建时间戳', `update_user` int(11) comment '更新者ID', `update_time` int(11) comment '更新时间戳', primary key (`tf_id`), unique key (`tf_sign`)) engine=innodb default charset=utf8 comment='模板域信息表'");
        return $query_tpl && $query_tf;
    }

}
