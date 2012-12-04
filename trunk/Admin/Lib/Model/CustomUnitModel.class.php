<?php
// 配置类型模型
class CustomUnitModel extends CommonModel
{
    protected $_validate = array(array('name', 'require', '名称必须'), array('name', '', '客户单位已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT));
    
    protected $_auto = array();
}