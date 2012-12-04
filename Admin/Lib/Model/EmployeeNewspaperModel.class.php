<?php
// 角色模型
class EmployeeNewspaperModel extends CommonModel
{
    function getEmployeeNespapers($employeeId)
    {
        $rs = $this->db->query('select b.postCode, b.name from ' . $this->tablePrefix . 'employee_newspaper as a ,' . $this->tablePrefix . 'magazine as b where a.postCode = b.postCode and a.personID =' . $employeeId .' order by a.operatingFrequency desc, b.name desc');
        
        return $rs;
    }
    
    function delEmployeeNewspaper($employeeId)
    {
        $table = $this->tablePrefix . 'employee_newspaper';
        
        $result = $this->db->execute('delete from ' . $table . ' where personID=' . $employeeId);
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }
    
    function setEmployeeMagazine($employeeId, $postCode)
    {
        $sql    = "INSERT INTO " . $this->tablePrefix . 'employee_newspaper (personID, postCode) values (' . $employeeId . ',' . $postCode . ')';
        $result = $this->execute($sql);
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }
    
    function setEmployeeMagazines($employeeId, $postCodeList, $operatingFrequencyList)
    {
        if (empty($employeeId)) {
            return -1;
        }
        if (empty($postCodeList)) {
            return -2;
        }
        
		if (empty($operatingFrequencyList))
		{
			return -3;
		}

		$EmployeeNewspaper = D('EmployeeNewspaper');

		foreach ($postCodeList as $key => $postCode)
		{
			 $sql    = "INSERT INTO " . $this->tablePrefix . "employee_newspaper (personID, postCode, operatingFrequency) values (" . $employeeId .",'" .$postCode . "','".$operatingFrequencyList[$key]."')";

			 $EmployeeNewspaper->execute($sql);
		}  

		return 0;

    }
    
    protected function fieldFormat(&$value)
    {
        if (is_int($value)) {
            $value = intval($value);
        } else if (is_float($value)) {
            $value = floatval($value);
        } else if (is_string($value)) {
            $value = '"' . addslashes($value) . '"';
        }
        return $value;
    }
    
}
?>