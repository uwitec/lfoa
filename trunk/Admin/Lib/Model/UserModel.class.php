<?php
// 用户模型
class UserModel extends CommonModel
{
    public $_validate = array(array('account', 'require', '帐号必须'), array('password', 'require', '密码必须'), array('repassword', 'require', '确认密码必须'), array('repassword', 'password', '确认密码不一致', self::EXISTS_VALIDATE, 'confirm'), array('account', '', '帐号已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT));
    
    public $_auto = array(array('password', 'pwdHash', self::MODEL_BOTH, 'callback'), array('create_time', 'time', self::MODEL_INSERT, 'function'), array('update_time', 'time', self::MODEL_UPDATE, 'function'));
    
    protected function pwdHash()
    {
        if (isset($_POST['password'])) {
            return pwdHash($_POST['password']);
        } else {
            return false;
        }
    }
    
    public function getUserByRoleName($roleName)
    {
        $rs = array();
        
        if ($roleName) {
            $sql = 'select distinct(a.employeeName) name, a.id as id from ' . $this->tablePrefix . 'employee as a 
			,' . $this->tablePrefix . 'user as b ,' . $this->tablePrefix . 'role as c ,' . $this->tablePrefix . 'role_user as d
			where c.ename = "' . $roleName . '" and d.role_id = c.id and b.id = d.user_id and a.id = b.employeeID ';
            
            $rs = $this->db->query($sql);
        }
        
        return $rs;
    }
    
    public function getUserByDutyName($dutyName)
    {
        $rs = array();
        if ($dutyName) {
            $sql = 'select distinct(a.employeeName) as name, a.id as id from ' . $this->tablePrefix . 'employee as a 
			,' . $this->tablePrefix . 'duty as b where b.name = "' . $dutyName . '" and a.dutyID= b.id';
            
            $rs = $this->db->query($sql);
        }
        
        return $rs;
    }
}