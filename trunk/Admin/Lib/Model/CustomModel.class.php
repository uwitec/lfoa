<?php
// 配置类型模型
class CustomModel extends CommonModel
{
    protected $_validate = array(array('name', 'require', '名称必须'));
    
    protected $_auto = array(array('insertTime', 'time', self::MODEL_INSERT, 'function'));
}