<?php
// 配置类型模型
class GroupModel extends CommonModel
{
    protected $_validate = array(array('name', 'require', '名称必须'), array('name', '', '帐号已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT));
    
    protected $_auto = array(array('status', 1, self::MODEL_INSERT, 'string'), array('create_time', 'time', self::MODEL_INSERT, 'function'), array('update_time', 'time', self::MODEL_UPDATE, 'function'));
    
    public function show($options, $field = 'show')
    {
        if (FALSE === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }
    
    public function hide($options, $field = 'show')
    {
        if (FALSE === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }
}