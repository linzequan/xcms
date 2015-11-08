<?php
/**
 * 模板控制器
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class template extends MY_Controller {

    public function __construct() {
        parent::__construct(__FILE__);
        $this->load->model('template_model');
    }


    public function index() {
        $this->load->view('publish/template');
    }

    public function slist() {
        $data['sid'] = $this->get_request('sid');
        $this->load->view('publish/template', $data);
    }


    public function get() {
        $actionxm = $this->get_request('actionxm');
        $result = array();
        switch($actionxm) {
            case 'search':
                $params = $this->input->post('rs');
                $order  = get_datagrid_order();
                $page   = get_datagrid_page();
                $result = $this->template_model->search($params, $order, $page);
                break;
        }
        echo json_encode($result);
    }


    public function post() {
        $actionxm = $this->get_request('actionxm');
        $result = array();
        switch($actionxm) {
            case 'insert':
                $info['tpl_name'] = $this->input->post('tpl_name');
                $info['alias_name'] = $this->input->post('alias_name');
                $info['url_rule'] = $this->input->post('url_rule');
                $info['tpl_content'] = $this->input->post('tpl_content');
                $result = $this->template_model->insert($info);
                break;
            case 'update':
                $tpl_id = $this->input->post('tpl_id');
                $info['tpl_name'] = $this->input->post('tpl_name');
                $info['alias_name'] = $this->input->post('alias_name');
                $info['url_rule'] = $this->input->post('url_rule');
                $info['tpl_content'] = $this->input->post('tpl_content');
                $result = $this->template_model->update($tpl_id, $info);
                break;
            case 'delete':
                $tpl_id = $this->input->post('tpl_id');
                $result = $this->template_model->delete($tpl_id);
                break;
        }
        $this->output_result($result);
    }
}
