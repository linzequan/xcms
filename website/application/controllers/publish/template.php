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
        // $this->load->model('template');
    }


    public function index() {
        $this->load->view('publish/template');
    }

    public function slist() {
        $data['sid'] = $this->get_request('sid');
        $this->load->view('publish/site', $data);
    }


    public function get() {
        $actionxm=$this->get_request('actionxm');
        $result=array();
        switch($actionxm) {
            case 'search':
                $params = $this->input->post('rs');
                $order  = get_datagrid_order();
                $page   = get_datagrid_page();
                $result = $this->site_model->search($params, $order, $page);
                break;
        }
        echo json_encode($result);
    }


    public function post() {
        $actionxm = $this->get_request('actionxm');
        $result = array();
        switch($actionxm) {
            case 'insert':
                $info['site_name'] = $this->input->post('site_name');
                $info['alias_name'] = $this->input->post('alias_name');
                $info['domain'] = $this->input->post('domain');
                $info['description'] = $this->input->post('description');
                $result = $this->site_model->insert($info);
                break;
            case 'update':
                $site_id = $this->input->post('site_id');
                $info['site_name'] = $this->input->post('site_name');
                $info['alias_name'] = $this->input->post('alias_name');
                $info['domain'] = $this->input->post('domain');
                $info['description'] = $this->input->post('description');
                $result = $this->site_model->update($site_id, $info);
                break;
            case 'delete':
                $site_id = $this->input->post('site_id');
                $result = $this->site_model->delete($site_id);
                break;
        }
        $this->output_result($result);
    }
}
