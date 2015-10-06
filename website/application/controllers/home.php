<?php
/**
 * 入口控制器
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class home extends MY_Controller {

    public function __construct() {
        parent::__construct(__FILE__);
        $user_id = $this->session->userdata('user_id');
        if(empty($user_id)) {
            header('Location:' . base_url('login'));
            exit;
        }
    }


    public function index() {
        $user_id = $this->session->userdata('user_id');
        $this->load->model('sys/pms_model');
        $is_admin = $this->session->userdata('is_admin');
        $list = $this->pms_model->get_user_menu_pms($user_id, $is_admin);
        $tree = array();
        create_tree_list($list, $tree, 0, 0, array('id_key'=>'menu_id', 'pid_key'=>'pid'));
        $this->load->view('index', array('app_menu'=>$tree));
    }
}
