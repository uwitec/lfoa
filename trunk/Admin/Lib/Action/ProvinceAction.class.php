<?php
// 省份信息
class ProvinceAction extends CommonAction
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
        $Area      = M("Area");
        $AreaList  = $Area->field('id, name')->select();
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        $this->assign('roleEname', $roleEname);
        $this->assign('AreaList', $AreaList);
    }
    
    public function _before_add()
    {
        $Area     = M("Area");
        $AreaList = $Area->field('id, name')->select();
        
        $this->assign('AreaList', $AreaList);
    }
    
    public function _before_edit()
    {
        $Area     = M("Area");
        $AreaList = $Area->field('id, name')->select();
        
        $this->assign('AreaList', $AreaList);
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