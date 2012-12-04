<?php
// 客户单位信息模块
class CustomUnitAction extends CommonAction
{
    function _filter(&$map)
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);

		/**
         * 业务经理只能看自己的信息和修改自己的客户信息
		 * 其他可以查看客户单位信息和管理客户单位信息
         */
        if ($roleEname == 'businessManager') {
            $map['employeeID'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        } else {
            
        }
        
        if ($roleEname != 'admin') {
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

		$User = D('User');		
		$businessManagerList = $User->getUserByRoleName('businessManager');
		$this->assign('businessManagerList', $businessManagerList);
        
    }
    
    public function _before_add()
    {
		$employeeID = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);

		$this->assign('employeeID', $employeeID);
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