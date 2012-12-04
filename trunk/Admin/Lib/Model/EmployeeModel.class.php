<?php
// 配置类型模型
class EmployeeModel extends CommonModel
{
    protected $_validate = array(array('name', 'require', '名称必须'), array('name', '', '该职员已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT));
    
    protected $_auto = array();
}