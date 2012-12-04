<?php
// 公司人员信息
class EmployeeAction extends CommonAction
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
        $Department     = M("Department");
        $Departmentlist = $Department->field('id, name')->select();
        $this->assign('Departmentlist', $Departmentlist);
        
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        $this->assign('roleEname', $roleEname);
    }
    
    public function _before_add()
    {
        $Duty       = M("Duty");
        $Department = M("Department");
        $Province   = M("Province");
        
        $DutyList       = $Duty->field('id, name')->select();
        $Departmentlist = $Department->field('id, name')->select();
        $ProvinceList   = $Province->field('id, name')->select();
        
        $this->assign('DutyList', $DutyList);
        $this->assign('Departmentlist', $Departmentlist);
        $this->assign('ProvinceList', $ProvinceList);
    }
    
    public function _before_edit()
    {
        $Duty       = M("Duty");
        $Department = M("Department");
        $Province   = M("Province");
        
        $DutyList       = $Duty->field('id, name')->select();
        $Departmentlist = $Department->field('id, name')->select();
        $ProvinceList   = $Province->field('id, name')->select();
        
        $this->assign('DutyList', $DutyList);
        $this->assign('Departmentlist', $Departmentlist);
        $this->assign('ProvinceList', $ProvinceList);
    }
    
    /**
     * @brief 添加职员信息后，若设置为系统用户则自动向系统用户表插入数据
     *        初始化密码为111111
     */
    public function _after_insert($insertId)
    {
        $isSystemUser = $_POST['isSystemUser'];
        if ($isSystemUser) {
            $User                = D("User");
            $Employee            = D("Employee");
            $data['account']     = $_POST['employeeName'];
            $data['userName']    = $_POST['employeeName'];
            $data['password']    = pwdHash('111111');
            $data['create_time'] = time();
            $data['status']      = '1';
            $data['employeeID']  = $insertId;
            $User->add($data);
        }
    }
    
    /**
     * @brief 更新用户信息，则删除系统用户
     */
    public function _before_update()
    {
        $isSystemUser   = $_POST['isSystemUser'];
        $EmployeeId     = $_POST['id'];
        $Employee       = D('Employee');
        $isSystemUserDb = $Employee->where('id = ' . $EmployeeId)->getField('isSystemUser');
        
        if ($isSystemUser) {
            if (!$isSystemUserDb) {
                $User                = D("User");
                $data['account']     = $_POST['employeeName'];
                $data['userName']    = $_POST['employeeName'];
                $data['password']    = pwdHash('111111');
                $data['create_time'] = time();
                $data['status']      = '1';
                $data['employeeID']  = $EmployeeId;
                $UserId              = $User->data($data)->add();
            }
        } else {
            if ($isSystemUserDb) {
                $User     = D("User");
                $RoleUser = D('RoleUser');
                
                $UserId = $User->where('employeeID = ' . $EmployeeId)->getField('id');
                
                $User->where('employeeID = ' . $EmployeeId)->delete();
                $RoleUser->where('user_id = ' . $UserId)->delete();
            }
        }
    }
    
    public function CancelSystem()
    {
        $EmployeeId     = $_GET['id'];
        $Employee       = D('Employee');
        $isSystemUserDb = $Employee->where('id = ' . $EmployeeId)->getField('isSystemUser');
        
        if ($isSystemUserDb) {
            $User     = D("User");
            $RoleUser = D('RoleUser');
            
            $UserId = $User->where('employeeID = ' . $EmployeeId)->getField('id');
            
            $User->where('employeeID = ' . $EmployeeId)->delete();
            $RoleUser->where('user_id = ' . $UserId)->delete();
            
            $EmployeeData['id']           = $EmployeeId;
            $EmployeeData['isSystemUser'] = 0;
            $Employee->save($EmployeeData);
            
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('设置成功!');
        } else {
            $this->error('设置失败!');
        }
        
    }
    
    public function SetSystem()
    {
        $EmployeeId = $_GET['id'];
        if ($EmployeeId) {
            $User     = D("User");
            $Employee = D("Employee");
            
            $EmployeeName        = $Employee->where('id = ' . $EmployeeId)->getField('employeeName');
            $data['account']     = $EmployeeName;
            $data['userName']    = $EmployeeName;
            $data['password']    = pwdHash('111111');
            $data['create_time'] = time();
            $data['status']      = '1';
            $data['employeeID']  = $EmployeeId;
            $UserId              = $User->add($data);
            
            $EmployeeData['id']           = $EmployeeId;
            $EmployeeData['isSystemUser'] = 1;
            $Employee->save($EmployeeData);
            
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('设置成功!');
        } else {
            $this->error('设置失败!');
        }
    }
    
    public function foreverdelete()
    {
        //删除指定记录
        $name  = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if (isset($id)) {
                $User     = D('User');
                $RoleUser = D('RoleUser');
                
                $UserCondition = array(
                    'employeeID' => array(
                        'in',
                        explode(',', $id)
                    )
                );
                $condition     = array(
                    'id' => array(
                        'in',
                        explode(',', $id)
                    )
                );
                $UserIdArray   = $User->where($UserCondition)->field('id')->select();
                
                $UserArray = array();
                foreach ($UserIdArray as $UserRow) {
                    $UserArray[] = $UserRow['id'];
                }
                
                $User->where($UserCondition)->delete();
                $RoleUser->where(array(
                    'user_id' => array(
                        'in',
                        $UserArray
                    )
                ))->delete();
                
                if (false !== $model->where($condition)->delete()) {
                    //echo $model->getlastsql();
                    $this->successNoClose('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
        $this->forward();
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