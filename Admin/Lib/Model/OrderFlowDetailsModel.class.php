<?php
// 订单流转模型
class OrderFlowDetailsModel extends CommonModel
{
    public function getUserByRoleName($roleName)
    {
        $sql = 'select a.id as id, a.employeeName as name from ' . $this->tablePrefix . 'employee as a 
		,' . $this->tablePrefix . 'user as b ,' . $this->tablePrefix . 'role as c ,' . $this->tablePrefix . 'role_user as d
		where c.name = "' . $roleName . '" and d.role_id = c.id and b.id = d.user_id and a.id = b.employeeID ';
        
        $rs = $this->db->query($sql);
        
        return $rs;
    }
}