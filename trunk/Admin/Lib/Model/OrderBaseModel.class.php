<?php

class OrderBaseModel extends CommonModel
{
    public $_validate = array(array('postCode', 'require', '报刊名称必须'), array('orderNum', 'require', '报刊份数必须'));
    
    public $_auto = array(array('insertTime', 'time', self::MODEL_INSERT, 'function'), array('orderTime', 'strtotime', self::MODEL_BOTH, 'function'), array('validateDate', 'strtotime', self::MODEL_BOTH, 'function'), array('payTime', 'strtotime', self::MODEL_BOTH, 'function'));
}
?>