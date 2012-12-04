<?php
// 表格报刊信息
class MagazineAction extends CommonAction
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

		$_REQUEST['_order'] = "sort desc, typeID ";
    }
    
    public function before_index()
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        $this->assign('roleEname', $roleEname);
    }
    
    /**
    +----------------------------------------------------------
    * 根据表单生成查询条件
    * 进行列表过滤
    +----------------------------------------------------------
    * @access protected
    +----------------------------------------------------------
    * @param Model $model 数据对象
    * @param HashMap $map 过滤条件
    * @param string $sortBy 排序
    * @param boolean $asc 是否正序
    +----------------------------------------------------------
    * @return void
    +----------------------------------------------------------
    * @throws ThinkExecption
    +----------------------------------------------------------
    */
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {
        //排序字段 默认为主键名
        if (isset($_REQUEST['_order'])) {
            $order = $_REQUEST['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        
        //取得满足条件的记录数
        $count = $model->where($map)->count('id');
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($count, $listRows);
            //分页查询数据
            
            $voList = $model->where($map)->order("" . $order . " desc")->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($map as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $page    = $p->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
        }
        $MagazineType     = M("MagazineType");
        $MagazineTypeList = $MagazineType->field('id, name')->select();
        
        $this->assign('MagazineTypeList', $MagazineTypeList);
        $this->assign('totalCount', $count);
        $this->assign('numPerPage', C('PAGE_LISTROWS'));
        $this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
        
        Cookie::set('_currentUrl_', __SELF__);
        return;
    }
    
    public function edit()
    {
        $name     = $this->getActionName();
        $model    = M($name);
        $TableKey = $model->getPk();
        $id       = $_REQUEST[$TableKey];
        $vo       = $model->where("$TableKey = '$id'")->select();
        $this->assign('vo', $vo[0]);
        $this->display();
    }
    
    public function _before_add()
    {
        $Employee         = M("Employee");
        $MagazineType     = M("MagazineType");
        $EmployeeList     = $Employee->field('id, employeeName')->select();
        $MagazineTypeList = $MagazineType->field('id, name')->select();
        
        $this->assign('MagazineTypeList', $MagazineTypeList);
        $this->assign('EmployeeList', $EmployeeList);
        
    }
    
    public function _before_edit()
    {
        $Employee         = M("Employee");
        $MagazineType     = M("MagazineType");
        $EmployeeList     = $Employee->field('id, employeeName')->select();
        $MagazineTypeList = $MagazineType->field('id, name')->select();
        
        $this->assign('MagazineTypeList', $MagazineTypeList);
        $this->assign('EmployeeList', $EmployeeList);
    }
    
    function detail()
    {
        $name     = $this->getActionName();
        $model    = M($name);
        $TableKey = $model->getPk();
        $id       = $_REQUEST[$TableKey];
        $vo       = $model->where("$TableKey = '$id'")->select();
        $this->assign('vo', $vo[0]);
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