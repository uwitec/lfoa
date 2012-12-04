<?php
// 发货公司信息
class SendGoodsTypeAction extends CommonAction
{
    function _filter(&$map)
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if (($roleEname != 'admin') || ($roleEname != 'customCenterWorker')) {
            $map['status'] = array(
                'neq',
                -1
            );
        }
    }
    
    public function before_index()
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        $this->assign('roleEname', $roleEname);
    }
    
    public function _before_add()
    {
        $SendGoodsSort     = M("SendGoodsSort");
        $SendGoodsSortList = $SendGoodsSort->field('id, name')->select();
        
        $this->assign('SendGoodsSortList', $SendGoodsSortList);
    }
    
    public function _before_edit()
    {
        $SendGoodsSort     = M("SendGoodsSort");
        $SendGoodsSortList = $SendGoodsSort->field('id, name')->select();
        
        $this->assign('SendGoodsSortList', $SendGoodsSortList);
    }
    
    function detail()
    {
        $name  = $this->getActionName();
        $model = M($name);
        $id    = $_REQUEST[$model->getPk()];
        $vo    = $model->getById($id);
        $this->assign('vo', $vo);
        $this->display();
    }
}