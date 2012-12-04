<?php
// 报刊选择信息
class MagazineSelectAction extends CommonAction
{    
    public function before_index()
    {
      
    }
    

	private function listFilter(&$map)
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if ($roleEname != 'admin') {
            $map['magazine.status'] = array(
                'neq',
                -1
            );
			$map['employee_newspaper.personID'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        }
    }

	private function searchMap(&$map, &$searchStr)
    {      
        if (isset($_REQUEST['typeID']) && !empty($_REQUEST['typeID'])) {
            $map['magazine.typeID'] = array(
                'eq',
                $_REQUEST['typeID']
            );
            $searchStr .= 'typeID/' . $_REQUEST['typeID'] . '/';
        }
        
        if (isset($_REQUEST['sort']) && !empty($_REQUEST['sort'])) {
            $map['magazine.sort'] = array(
                'eq',
                $_REQUEST['sort']
            );
            $searchStr .= 'sort/' . $_REQUEST['sort'] . '/';
        }
		if (isset($_REQUEST['name']) && !empty($_REQUEST['name'])) {
            $map['magazine.name'] = array(
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
            'tb_magazine' => 'magazine'
        ))->join('tb_employee_newspaper  employee_newspaper on employee_newspaper.postCode = magazine.postCode')->join('tb_magazine_type  magazine_type on magazine_type.id = magazine.typeID')->where($map)->count('magazine.postCode');
        
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
            $orderStr = 'magazine.typeID desc';
            $fieldStr = '
				magazine.postCode as postCode, 
				magazine.name as magazineName, 
				magazine_type.name as magazineTypeName, 
				magazine.sort as sort';
            
            $voList = $model->table(array(
            'tb_magazine' => 'magazine'
        ))->join('tb_employee_newspaper  employee_newspaper on employee_newspaper.postCode = magazine.postCode')->join('tb_magazine_type  magazine_type on magazine_type.id = magazine.typeID')->where($map)->order($orderStr)->limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
            
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
   

	public function getSelect()
    {
        $type = $_REQUEST['type'];
        
        switch ($type) {
            case '1': {
                $sort   = $_REQUEST['sort'];
				if ($sort == 1)
				{
					$sortName = '报纸';
				}
				else if ($sort == 2)
				{
					$sortName = '杂志';
				}
				else if ($sort == 3)
				{
					break;
				}
                $MagazineType = M("MagazineType");
                if ($sortName) {
                    $MagazineTypeList = $MagazineType->where("sort = '" . $sortName ."'")->field('id, name')->select();
                } else {
                    break;
                }
                $select[] = array(
                    'id' => '',
                    'title' => '--请选择--'
                );
                foreach ($MagazineTypeList as $MagazineTypeVo) {
                    $select[] = array(
                        'id' => $MagazineTypeVo['id'],
                        'title' => $MagazineTypeVo['name']
                    );
                }
                
                echo json_encode($select);
                return;
            }
                break;
            
            default:
                break;
        }
        $select[] = array(
            'id' => '',
            'title' => '--请选择--'
        );
        echo json_encode($select);
        return;
    }
}