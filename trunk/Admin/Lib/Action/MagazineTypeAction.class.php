<?php
// 客户信息模块
class MagazineTypeAction extends CommonAction
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

		$_REQUEST['_order'] = "sort desc, parentID ";
    }
    
    public function before_index()
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        $this->assign('roleEname', $roleEname);
    }
    
    //赋值公司人员和客户单位
    public function _before_add()
    {
        $Department     = M("Department");
        $DepartmentList = $Department->field('id, name')->select();
        
        $this->assign('DepartmentList', $DepartmentList);
        
        $MagazineType     = M("MagazineType");
        $MagazineTypeList = $MagazineType->where('parentID = 0')->field('id, name')->select();
        
        $this->assign('MagazineTypeList', $MagazineTypeList);
    }
    
    //赋值公司人员和客户单位
    public function _before_edit()
    {
        $Department     = M("Department");
        $DepartmentList = $Department->field('id, name')->select();
        
        $this->assign('DepartmentList', $DepartmentList);
        
        $MagazineType     = M("MagazineType");
        $MagazineTypeList = $MagazineType->where('parentID = 0')->field('id, name')->select();
        
        $this->assign('MagazineTypeList', $MagazineTypeList);
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
    
    public function foreverdelete()
    {
        //删除指定记录
        $name  = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if (isset($id)) {
                $condition = array(
                    $pk => array(
                        'in',
                        explode(',', $id)
                    )
                );
                if (false !== $model->where($condition)->delete()) {
                    $condition = array(
                        'parentID' => array(
                            'in',
                            explode(',', $id)
                        )
                    );
                    $model->where($condition)->delete();
                    $this->successNoClose('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
        $this->forward();
    }
    
    public function delete()
    {
        //删除指定记录
        $name  = $this->getActionName();
        $model = M($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if (isset($id)) {
                $condition = array(
                    $pk => array(
                        'in',
                        explode(',', $id)
                    )
                );
                $list      = $model->where($condition)->setField('status', -1);
                if ($list !== false) {
                    $condition = array(
                        'parentID' => array(
                            'in',
                            explode(',', $id)
                        )
                    );
                    $model->where($condition)->setField('status', -1);
                    $this->successNoClose('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
    }
}