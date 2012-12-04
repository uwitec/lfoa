<?php
// 配置类型模型
class PressNuminfoModel extends CommonModel
{
    protected $_validate = array(array('postCode', 'require', '请选择报刊!'), array('termID', 'require', '名称必须且唯一!', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT), array('printNum', '', '请填写要上报的份数!'));
    
    protected $_auto = array(array('insertTime', 'time', self::MODEL_INSERT, 'function'));
}