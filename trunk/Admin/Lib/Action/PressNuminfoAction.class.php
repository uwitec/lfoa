<?php
// 期刊印刷信息
class PressNuminfoAction extends CommonAction
{
    /* index页面的搜索列表赋值 */
    private function indexSearchList()
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        /* 业务经理报刊信息列表 */
        $Magazine     = D("Magazine");
        $MagazineList = $Magazine->getMagazineListByEmId($EmployeeId);
        $this->assign('MagazineList', $MagazineList);
        
        $MagazineTerrmList = array();
        $this->assign('MagazineTerrmList', $MagazineTerrmList);
    }
    
    
    /* 组织index页面的搜索语句 */
    private function indexSearch(&$map, &$searchStr)
    {
        if (isset($_REQUEST['postCode']) && !empty($_REQUEST['postCode'])) {
            $map['magazine.postCode'] = array(
                'eq',
                $_REQUEST['postCode']
            );
            $searchStr .= 'postCode/' + $_REQUEST['postCode'] + '/';
        }
        
        if ($_REQUEST['month']) {
            $map['magazine_terrm.month'] = array(
                'eq',
                $_REQUEST['month']
            );
            $searchStr .= 'month/' + $_REQUEST['month'] + '/';
        }
        
        if ($_REQUEST['year']) {
            $map['magazine_terrm.year'] = array(
                'eq',
                $_REQUEST['year']
            );
            $searchStr .= 'month/' + $_REQUEST['year'] + '/';
        }
    }
    
    private function listFilter(&$map)
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $map['employee_newspaper.personID'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
    }
    
    public function index()
    {
        $map       = array();
        $searchStr = '';
        $this->indexSearch($map, $searchStr);
        
        $this->indexSearchList();
        
        if (method_exists($this, 'listFilter')) {
            $this->listFilter($map);
        }
        
        $PressNuminfo = D('PressNuminfo');
        if (!empty($PressNuminfo)) {
            $this->_list($PressNuminfo, $map);
        }
        
        $this->display();
    }
    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {
        //取得满足条件的记录数
        $count = $model->table(array(
            'tb_press_numinfo' => 'press_numinfo'
        ))->join('tb_magazine  magazine on magazine.postCode = press_numinfo.postCode')->join('tb_magazine_terrm magazine_terrm on magazine_terrm.id = press_numinfo.termID')->join('tb_employee_newspaper employee_newspaper on magazine.postcode = employee_newspaper.postcode')->where($map)->count('press_numinfo.id');
        
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
            
            $fieldStr = 'press_numinfo.id as id, magazine.name as magazineName, magazine_terrm.name as magazineTerrmName, magazine_terrm.month as month, magazine_terrm.year as year, press_numinfo.printNum as printNum';
            $orderStr = 'magazine_terrm.year asc, magazine_terrm.month asc, magazine_terrm.id asc';
            
            $voList = $model->table(array(
                'tb_press_numinfo' => 'press_numinfo'
            ))->join('tb_magazine  magazine on magazine.postCode = press_numinfo.postCode')->join('tb_magazine_terrm magazine_terrm on magazine_terrm.id = press_numinfo.termID')->join('tb_employee_newspaper employee_newspaper on magazine.postcode = employee_newspaper.postcode')->where($map)->order($orderStr)->limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
            
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
    
    private function exportList($model, $map, $sortBy = '', $asc = false)
    {
        $voList = array();
        
        //取得满足条件的记录数
        $count = $model->table(array(
            'tb_press_numinfo' => 'press_numinfo'
        ))->join('tb_magazine  magazine on magazine.postCode = press_numinfo.postCode')->join('tb_magazine_terrm magazine_terrm on magazine_terrm.id = press_numinfo.termID')->join('tb_employee_newspaper employee_newspaper on magazine.postcode = employee_newspaper.postcode')->where($map)->count('press_numinfo.id');
        
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
            
            $fieldStr = 'press_numinfo.id as id, magazine.name as magazineName, magazine_terrm.name as magazineTerrmName, magazine_terrm.month as month, magazine_terrm.year as year, press_numinfo.printNum as printNum';
            
            $orderStr = 'magazine_terrm.year asc, magazine_terrm.month asc, magazine_terrm.id asc';
            
            $voList = $model->table(array(
                'tb_press_numinfo' => 'press_numinfo'
            ))->join('tb_magazine  magazine on magazine.postCode = press_numinfo.postCode')->join('tb_magazine_terrm magazine_terrm on magazine_terrm.id = press_numinfo.termID')->join('tb_employee_newspaper employee_newspaper on magazine.postcode = employee_newspaper.postcode')->where($map)->order($orderStr)->field($fieldStr)->select();
        }
        
        return $voList;
    }
    
    
    public function export()
    {
        $map       = array();
        $searchStr = '';
        $voList    = array();
        
        $this->indexSearch($map, $searchStr);
        
        if (method_exists($this, 'listFilter')) {
            $this->listFilter($map);
        }
        
        $PressNuminfo = D('PressNuminfo');
        if (!empty($PressNuminfo)) {
            $voList = $this->exportList($PressNuminfo, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "印刷期数.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "报刊名称" . "\t";
            $HeaderStr .= "报刊期数" . "\t";
            $HeaderStr .= "月份" . "\t";
            $HeaderStr .= "年度" . "\t";
            $HeaderStr .= "份数" . "\t\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                $ContentStr .= $vo['magazineName'] . "\t";
                $ContentStr .= $vo['magazineTerrmName'] . "\t";
                $ContentStr .= $vo['month'] . "\t";
                $ContentStr .= $vo['year'] . "\t";
                $ContentStr .= $vo['printNum'] . "\t\n";
            }
            
            $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
            $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
            
            echo $HeaderStr . $ContentStr;
            
            exit();
        } else {
            $this->error('没有数据!');
        }
    }
    
    public function _before_add()
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        /* 业务经理报刊信息列表 */
        $Magazine     = D("Magazine");
        $MagazineList = $Magazine->getMagazineListByEmId($EmployeeId);
        $this->assign('MagazineList', $MagazineList);
    }
    
    public function insert()
    {
        $PressNuminfo = D('PressNuminfo');
        
        $data['postCode'] = $_REQUEST['postCode'];
		$data['makeID']   = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);

		foreach ($_REQUEST['termIDArr'] as $termIDKey => $termIDValue)
		{
			$data['termID']   = $_REQUEST['termIDArr'][$termIDKey];
			$data['printNum'] = $_REQUEST['printNumArr'][$termIDKey];

			$PressNuminfo->add($data);
		}   
        
        $this->success('添加成功!');
    }
    
    
    public function _before_edit()
    {
    }
    
    public function update()
    {
        $PressNuminfo = D('PressNuminfo');
        
        $data['id']       = $_REQUEST['id'];
        $data['printNum'] = $_REQUEST['printNum'];
        $data['makeID']   = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $PressNuminfo->save($data);
        
        $this->success('保存成功!');
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
    
    public function getSelect()
    {
        $type = $_REQUEST['type'];
        
        switch ($type) {
            case '1':
                /* 单期选择 */ {
                $postCode      = $_REQUEST['postCode'];
                $MagazineTerrm = M("MagazineTerrm");
                if ($postCode) {
                    $year  = $_REQUEST['year'];
                    $month = $_REQUEST['month'];
                    $map   = array();
                    
                    $map['postCode'] = $postCode;
                    if ($year) {
                        $map['year'] = $year;
                    }
                    if ($month) {
                        $map['month'] = $month;
                    }
                    $MagazineTerrmList = $MagazineTerrm->where($map)->field('id, name')->select();
                } else {
                    break;
                }

                $select[] = array(
                    'id' => '',
                    'title' => '--请选择--'
                );


                foreach ($MagazineTerrmList as $MagazineTerrmVo) {
                    $select[] = array(
                        'id' => $MagazineTerrmVo['id'],
                        'title' => $MagazineTerrmVo['name']
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