<?php
/**
 * 应用基础模型
 *
 * @author linzequan <lowkey361@gmail.com>
 *
 */
class MY_Model extends CI_Model {

    public function __construct() {
        parent::__construct();
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
}
