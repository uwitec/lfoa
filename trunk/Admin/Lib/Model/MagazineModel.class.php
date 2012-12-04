<?php
// 配置类型模型
class MagazineModel extends CommonModel
{
    protected $_validate = array(array('postCode', 'require', '发行代码'), array('name', 'require', '名称必须'), array('postCode', '', '发行代码已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT));
    
    protected $_auto = array();
    
    function getMagazineListByEmId($EmployeeId)
    {
		$sql = 'select a.postCode as postCode, a.name as name from ' . $this->tablePrefix . 'magazine as a right join ' . $this->tablePrefix . 'employee_newspaper b on a.postCode = b.postCode where b.personID = ' . $EmployeeId.' order by name desc';

        $rs = $this->db->query($sql);


        
        return $rs;
    }
}