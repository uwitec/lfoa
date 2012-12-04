<?php
// 客户选择
class CustomSelectAction extends CommonAction
{
	private function beforeIndex()
    {
        $Province     = M("Province");
        $ProvinceList = $Province->field('id, name')->select();
        $this->assign('ProvinceList', $ProvinceList);
        
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        $this->assign('roleEname', $roleEname);
    }

	private function listFilter(&$map)
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        /**
         * 系统管理员可以查看客户信息和管理客户信息
         * 物流质检员可以查看客户信息，只能修改自己的客户信息
         * 业务经理只能看自己的信息和修改自己的客户信息
         * 其他按照可以看自己的信息和修改自己的客户信息
         */
        if ($roleEname == 'businessManager') {
            $map['custom.employeeID'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        } else {
            ;
        }
        
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if ($roleEname != 'admin') {
            $map['custom.status'] = array(
                'neq',
                -1
            );
        }
    }

	private function searchMap(&$map, &$searchStr)
    {      
        if (isset($_REQUEST['cityName']) && !empty($_REQUEST['cityName'])) {
            $map['custom.cityName'] = array(
                'eq',
                $_REQUEST['cityName']
            );
            $searchStr .= 'cityName/' . $_REQUEST['cityName'] . '/';
        }
        
        if (isset($_REQUEST['provinceID']) && !empty($_REQUEST['provinceID'])) {
            $map['custom.provinceID'] = array(
                'eq',
                $_REQUEST['provinceID']
            );
            $searchStr .= 'provinceID/' . $_REQUEST['provinceID'] . '/';
        }

		if (isset($_REQUEST['isOldClient']) && !empty($_REQUEST['isOldClient'])) {
            $map['custom.isOldClient'] = array(
                'eq',
                $_REQUEST['isOldClient']
            );
            $searchStr .= 'isOldClient/' . $_REQUEST['isOldClient'] . '/';
        }
		if (isset($_REQUEST['name']) && !empty($_REQUEST['name'])) {
            $map['custom.name'] = array(
                'eq',
                $_REQUEST['name']
            );
            $searchStr .= 'name/' . $_REQUEST['name'] . '/';
        }
    }

	protected function _list($model, $map, $sortBy = '', $asc = false)
    {
        //取得满足条件的记录数
        $count = $model->table(array(
            'tb_custom' => 'custom'
        ))->join('tb_custom_unit  custom_unit on custom_unit.id = custom.UnitID')->join('tb_employee employee on employee.id = custom.employeeID')->join('tb_province province on province.id = custom.provinceID')->where($map)->count('custom.id');
        
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '';
            }
            $p        = new Page($count, $listRows);
            //分页查询数据
            $orderStr = 'custom.isOldClient desc, custom.name asc';
            $fieldStr = '
				custom.id as id, 
				custom.name as customName, 
				custom_unit.name as customUnitName, 
				custom.telphone1 as telphone1, 
				employee.employeeName as employeeName, 
				province.name as provinceName, 
				custom.cityName as cityName, 
				custom.isOldClient as isOldClient, 
				custom.address as address';
            
            $voList = $model->table(array(
                'tb_custom' => 'custom'
            ))->join('tb_custom_unit  custom_unit on custom_unit.id = custom.UnitID')->join('tb_employee employee on employee.id = custom.employeeID')->join('tb_province province on province.id = custom.provinceID')->where($map)->order($orderStr)->limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
            
            //分页显示
            $page = $p->show();
            
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign("page", $page);
        }
        $this->assign('totalCount', $count);
        $this->assign('numPerPage', C('PAGE_LISTROWS'));
        $this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
        
        Cookie::set('_currentUrl_', __SELF__);
        return;
    }

	public function index()
	{
		$map       = array();
        $searchStr = '';
        
        if (method_exists($this, 'beforeIndex')) {
            $this->beforeIndex();
        }
        
        if (method_exists($this, 'searchMap')) {
            $this->searchMap($map, $searchStr);
        }
        
        if (method_exists($this, 'listFilter')) {
            $this->listFilter($map);
        }
        
        $Custom = D('Custom');
        if (!empty($Custom)) {
            $this->_list($Custom, $map);
        }
        
        $this->assign('searchStr', $searchStr);
        
        $this->display();
	}
}