<?php
// 配置类型模型
class MagazineTerrmModel extends CommonModel
{
    protected $_validate = array(array('name', 'require', '期数名称必须'));
    
    protected $_auto = array(array('insertTime', 'time', self::MODEL_INSERT, 'function'));
    
    function copyMagazineTerrmByMonth($sourcePostCode, $destPostCode, $year, $month)
    {
        if (!$year && !$month) {
            return -1;
        }
        
        $sql = 'delete from ' . $this->tablePrefix . 'magazine_terrm where year = ' . $year . ' and month =' . $month . ' and postCode = "' . $destPostCode . '"';
        $this->db->query($sql);
        
        $sql    = 'insert into ' . $this->tablePrefix . 'magazine_terrm  (name, termList, longName, month, year, insertTime, insertPerson, postCode) (select name, termList, longName, month, year, insertTime, insertPerson, "' . $destPostCode . '" from ' . $this->tablePrefix . 'magazine_terrm where year = ' . $year . ' and month =' . $month . ' and postCode = "' . $sourcePostCode . '" )';
        $result = $this->db->query($sql);
    }
    
    
    function copyMagazineTerrmByYear($sourcePostCode, $destPostCode, $year)
    {
        if (!$year && !$month) {
            return -1;
        }
        
        $sql = 'delete from ' . $this->tablePrefix . 'magazine_terrm where year = ' . $year . ' and postCode = "' . $destPostCode . '"';
        $this->db->query($sql);
        
        $sql    = 'insert into ' . $this->tablePrefix . 'magazine_terrm  (name, termList, longName, month, year, insertTime, insertPerson, postCode) (select name, termList, longName, month, year, insertTime, insertPerson, "' . $destPostCode . '" from ' . $this->tablePrefix . 'magazine_terrm where year = ' . $year . ' and postCode = "' . $sourcePostCode . '" )';
        $result = $this->db->query($sql);
    }
}