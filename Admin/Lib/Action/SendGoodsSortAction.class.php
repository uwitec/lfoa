<?php
// 发货类型
class SendGoodsSortAction extends CommonAction
{
    function _filter(&$map)
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if (($roleEname != 'admin') || ($roleEname != 'customCenterWorker')) {
            $map['status'] = array(
                'neq',
                -1
            );
        }
    }
    
    public function before_index()
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        $this->assign('roleEname', $roleEname);
    }
    
    public function _before_add()
    {
    }
    
    public function _before_edit()
    {
    }
    
    function detail()
    {
        $name  = $this->getActionName();
        $model = M($name);
        $id    = $_REQUEST[$model->getPk()];
        $vo    = $model->getById($id);
        $this->assign('vo', $vo);
        $this->display();
    }
}