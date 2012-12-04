<?php
// 后台用户模块
class UserAction extends CommonAction
{
    //列出用户时,对ID进行>2限制,对account进行like匹配
    function _filter(&$map)
    {
        $map['id']      = array(
            'egt',
            2
        );
        $map['account'] = array(
            'like',
            "%" . $_POST['account'] . "%"
        );
    }
    
    // 检查帐号
    public function checkAccount()
    {
        if (!preg_match('/^[a-z]\w{4,}$/i', $_POST['account'])) {
            $this->error('用户名必须是字母，且5位以上！');
        }
        $User   = M("User");
        // 检测用户名是否冲突
        $name   = $_REQUEST['account'];
        $result = $User->getByAccount($name);
        if ($result) {
            $this->error('该用户名已经存在！');
        } else {
            $this->success('该用户名可以使用！');
        }
    }
    
    // 插入数据
    public function insert()
    {
        // 创建数据对象
        $User = D("User");
        if (!$User->create()) {
            $this->error($User->getError());
        } else {
            // 写入帐号数据
            if ($result = $User->add()) {
                if ($_POST['role_id']) {
                    $this->addRole($result, $role_id);
                }
                $this->success('用户添加成功！');
            } else {
                $this->error('用户添加失败！');
            }
        }
    }
    
    //添加角色
    protected function addRole($userId, $role_id)
    {
        //新增用户自动加入相应权限组
        $RoleUser          = M("RoleUser");
        $RoleUser->user_id = $userId;
        $RoleUser->role_id = $role_id;
        $RoleUser->add();
    }
    
    public function _before_add()
    {
        $Role     = D("Role");
        $RoleList = $Role->field('id,name')->select();
        $this->assign('rolelist', $RoleList);
    }
    
    public function _before_edit()
    {
        $Role     = D("Role");
        $RoleList = $Role->field('id,name')->select();
        $Id       = $_GET["id"];
        
        $RoleUser = D("Role_user");
        $RoleId   = $RoleUser->where("user_id = $Id")->getField('role_id');
        
        $this->assign('rolelist', $RoleList);
        $this->assign('roleid', $RoleId);
    }
    
    public function _after_update()
    {
        $RoleId     = $_POST["role_id"];
        $UserId     = $_POST['id'];
        $RoleUser   = D("Role_user");
        $RoleLastId = $RoleUser->where('user_id = ' . $UserId)->getField('user_id');
        
        if ($RoleId) {
            if ($RoleLastId) {
                $RoleUser->where('user_id = ' . $UserId)->setField('role_id', $RoleId);
            }
            $RoleUser->user_id = $UserId;
            $RoleUser->role_id = $RoleId;
            $RoleUser->add();
        } else {
            if ($RoleLastId) {
                $RoleUser->where('user_id = ' . $UserId)->delete();
            }
        }
    }
    
    /* 用户负责报刊列表 只有业务经理 */
    public function magazine()
    {
        $EmployeeId = $_REQUEST['id'];
        
        if ($EmployeeId) {
            $Magazine          = M('Magazine');
            $EmployeeNewspaper = D('EmployeeNewspaper');

			$MagazineList = $Magazine->field('postCode, name')->select();

			$NewspaperList = $EmployeeNewspaper->field('postCode, operatingFrequency')->where('personID = '.$EmployeeId)->select();
			if ($NewspaperList)
			{
				foreach ($NewspaperList as $EmployeeNewspaperListVo)
				{
					$EmployeeNewspaperList[] = $EmployeeNewspaperListVo['postCode'];
					$operatingFrequencyList[$EmployeeNewspaperListVo['postCode']] = $EmployeeNewspaperListVo['operatingFrequency'];
				}
			}

            $this->assign('EmployeeId', $EmployeeId);
			$this->assign('MagazineList', $MagazineList);
			$this->assign('operatingFrequencyList', $operatingFrequencyList);
            $this->assign('EmployeeNewspaperList', $EmployeeNewspaperList);
            
            $this->display();
        }
    }
    
    public function setMagazine()
    {
        $EmployeeId        = $_POST['id'];
        $postCode          = $_POST['postCode'];
		$operatingFrequency = $_POST['operatingFrequency'];

        $EmployeeNewspaper = D("EmployeeNewspaper");
        $EmployeeNewspaper->delEmployeeNewspaper($EmployeeId);
        $result = $EmployeeNewspaper->setEmployeeMagazines($EmployeeId, $postCode, $operatingFrequency);
        
         $this->success('设置成功！');

    }
    
    //重置密码
    public function resetPwd()
    {
        $id       = $_POST['id'];
        $password = $_POST['password'];
        if ('' == trim($password)) {
            $this->error('密码不能为空！');
        }
        $User           = M('User');
        $User->password = md5($password);
        $User->id       = $id;
        $result         = $User->save();
        if (false !== $result) {
            $this->success("密码修改为$password");
        } else {
            $this->error('重置密码失败！');
        }
    }
    
}