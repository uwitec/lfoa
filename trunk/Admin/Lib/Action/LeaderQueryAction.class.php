<?php
// 领导信息查询
class LeaderQueryAction extends CommonAction
{
    private $defaultShowCols = array(
		array('name' => 'employeeName', 'tableName' => 'employee.employeeName as employeeName', 'chName' => '业务经理', 'colWidth' => '100'), 
		array('name' => 'postCodeName', 'tableName' => 'magazine.name as postCodeName', 'chName' => '报刊名称', 'colWidth' => '100'), 
		array('name' => 'orderYear', 'tableName' => 'order_base.orderYear as orderYear', 'chName' => '年份', 'colWidth' => '80'), 
		array('name' => 'orderMonth', 'tableName' => "FROM_UNIXTIME( order_base.orderTime, '%m' )  as orderMonth", 'chName' => '月份', 'colWidth' => '100'),
		array('name' => 'quantity', 'tableName' => 'sum((order_base.endOrderDate - order_base.beginOrderDate + 1) * orderNum) as quantity', 'chName' => '数量', 'colWidth' => '80')		
	);
    
    private $allShowCols = array(
		array('name' => 'employeeName', 'tableName' => 'employee.employeeName as employeeName', 'chName' => '业务经理', 'colWidth' => '100'), 
		array('name' => 'postCodeName', 'tableName' => 'magazine.name as postCodeName', 'chName' => '报刊名称', 'colWidth' => '100'),
		array('name' => 'orderYear', 'tableName' => 'order_base.orderYear as orderYear', 'chName' => '年份', 'colWidth' => '80'), 
		array('name' => 'orderMonth', 'tableName' => 'FROM_UNIXTIME( order_base.orderTime, %m )  as orderMonth', 'chName' => '月份', 'colWidth' => '100'),
		array('name' => 'quantity', 'tableName' => 'sum((order_base.endOrderDate - order_base.beginOrderDate + 1) * orderNum) as quantity', 'chName' => '数量', 'colWidth' => '80')	,		 
		array('name' => 'magazineOriginName', 'tableName' => 'magazine_origin.name as magazineOriginName', 'chName' => '报刊来源', 'colWidth' => '80'), 
		array('name' => 'provinceName', 'tableName' => 'province.name as provinceName', 'chName' => '省份', 'colWidth' => '80'), 
		array('name' => 'cityName', 'tableName' => 'order_base.cityName as cityName', 'chName' => '城市', 'colWidth' => '80'),				 
		array('name' => 'sendGoodsSortName', 'tableName' => 'send_goods_sort.name as sendGoodsSortName', 'chName' => '发货类型', 'colWidth' => '80'), 
		array('name' => 'sendGoodsTypeName', 'tableName' => 'send_goods_type.name as sendGoodsTypeName', 'chName' => '发货方式', 'colWidth' => '80'), 
		array('name' => 'payPersonName', 'tableName' => 'order_base.payPerson as payPersonName',  'chName' => '付款人', 'colWidth' => '80'), 
		array('name' => 'sendCyleName', 'tableName' => 'send_order_cyle.name as sendCyleName', 'chName' => '发货周期', 'colWidth' => '80'), 
		array('name' => 'schoolName', 'tableName' => 'custom_unit.name as schoolName', 'chName' => '单位', 'colWidth' => '100'), 
		array('name' => 'classroom', 'tableName' => 'order_base.class as classroom', 'chName' => '班级', 'colWidth' => '100'), 
		array('name' => 'zipCode', 'tableName' => 'order_base.zipCode as zipCode', 'chName' => '邮编', 'colWidth' => '80'), 
		array('name' => 'isTrans', 'tableName' => 'order_base.isTrans as isTrans', 'chName' => '是否转换', 'colWidth' => '60'), 
		array('name' => 'isSingle', 'tableName' => 'order_base.isSingle as isSingle', 'chName' => '是否单期', 'colWidth' => '60'), 
		array('name' => 'isPay', 'tableName' => 'order_base.isPay as isPay', 'chName' => '是否付款', 'colWidth' => '60'), 
	);

    
    private function isInShowCols($list, $value)
    {
        foreach ($list as $key => $val) {
            if ($value == $val['name']) {
                return true;
            }
        }
        
        return false;
    }
    
    /* index页面的搜索列表赋值 */
    private function indexSearchList()
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $roleEname  = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        /* 业务经理报刊信息列表 */
        $Magazine = D("Magazine");
        if ($roleEname == 'businessManager') {
			$MagazineList = $Magazine->getMagazineListByEmId($EmployeeId);            
        } else {
            $MagazineList = $Magazine->field('postCode, name')->select();
        }
        $this->assign('MagazineList', $MagazineList);
        
        /* 省份信息 */
        $Province     = M("Province");
        $ProvinceList = $Province->field('id, name')->select();
        $this->assign('ProvinceList', $ProvinceList);
        
        /* 业务经理 */
        $User        = D('User');
        $ManagerList = $User->getUserByRoleName('businessManager');
        $this->assign('ManagerList', $ManagerList);
    }
    
    
    /* 组织index页面的搜索语句 */
    private function indexSearch(&$map, &$searchStr)
    {
        $BeginMonthTemp = '';
        $EndMonthTemp   = '';
		$OrderYear = date('Y');
        
        if (isset($_REQUEST['orderYear']) && !empty($_REQUEST['orderYear'])) {
            $map['order_base.orderYear'] = array(
                'eq',
                $_REQUEST['orderYear']
            );
            $searchStr .= 'orderYear/' . $_REQUEST['orderYear'] . '/';
			$OrderYear = $_REQUEST['orderYear'];
        }
        
        if (isset($_REQUEST['postCode']) && !empty($_REQUEST['postCode'])) {
            $map['order_base.postCode'] = array(
                'eq',
                $_REQUEST['postCode']
            );
            $searchStr .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
        
        if (isset($_REQUEST['provinceID']) && !empty($_REQUEST['provinceID'])) {
            $map['order_base.provinceID'] = array(
                'eq',
                $_REQUEST['provinceID']
            );
            $searchStr .= 'provinceID/' . $_REQUEST['provinceID'] . '/';
        }
        
        if (isset($_REQUEST['employeeID']) && !empty($_REQUEST['employeeID'])) {
            $map['order_base.employeeID'] = array(
                'eq',
                $_REQUEST['employeeID']
            );
            $searchStr .= 'employeeID/' . $_REQUEST['employeeID'] . '/';
        }
        
        if (isset($_REQUEST['beginMonth']) && !empty($_REQUEST['beginMonth'])) {
            $BeginMonthTemp = strtotime($OrderYear.'-'.$_REQUEST['beginMonth'].'-1');
            $searchStr .= 'beginMonth/' . $_REQUEST['beginMonth'] . '/';
        }
        
        if (isset($_REQUEST['endMonth']) && !empty($_REQUEST['endMonth'])) {
			$endMonthTempAdd = $_REQUEST['endMonth'] + 1;
            $EndMonthTemp = strtotime($OrderYear.'-'.$endMonthTempAdd.'-1');
            $searchStr .= 'endMonth/' . $_REQUEST['endMonth'] . '/';
        }        
        
        if ($BeginMonthTemp || $EndMonthTemp) {
            if ($BeginMonthTemp && $EndMonthTemp) {
                $map['order_base.orderTime'] = array(
                    'between',
                    "$BeginMonthTemp, $EndMonthTemp"
                );
            } else if ($BeginDateTemp) {
                $map['order_base.orderTime'] = array(
                    'egt',
                    $BeginMonthTemp
                );
            } else {
                $map['order_base.orderTime'] = array(
                    'elt',
                    $EndMonthTemp
                );
            }
        }
    }
    
    private function listFilter(&$map)
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $roleEname  = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if (($roleEname == 'businessManager')) {
            $map['order_base.employeeID'] = $EmployeeId;
        }
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
        
        $OrderBase = M('OrderBase');
        if (!empty($OrderBase)) {
            $this->_list($OrderBase, $map, $searchStr);
        }
        
        $this->assign('searchStr', $searchStr);
        
        $this->display();
    }
    
    private function getListFieldStr($colList, &$searchStr)
    {
        $fieldStr  = '';
        $searchStr .= 'selectColArr/';
        foreach ($colList as $key => $col) {
            $fieldStr .= $col['tableName'] . ',';
            $searchStr .= $key . ',';
        }

        $searchStr = substr($searchStr, 0, strlen($searchStr) - 1) . '/';
        $fieldStr  = substr($fieldStr, 0, strlen($fieldStr) - 1);
        
        return $fieldStr;
    }
    
    private function getListOrderStr()
    {
        $sortStr = 'order_base.orderYear desc, order_base.orderTime desc';

		return $sortStr;
    }

	private function getListGroupByStr()
	{
		$groupStr = '';		

		if (isset($_REQUEST['selectColArr[9]']))
		{
			$groupStr .= 'province.name,';
		}
		else
		{
			if (isset($_REQUEST['beginMonth']) || isset($_REQUEST['endMonth']))
			{
				$groupStr .= "FROM_UNIXTIME( order_base.orderTime, '%m'),";
			}
			else
			{
				$groupStr .= 'employee.employeeName,';
			}
		}

		$groupStr .= 'order_base.orderYear, orderMonth, magazine.name';

		return $groupStr;
	}

	private function getConditionModel(&$model)
	{
		$model->table(array('tb_order_base' => 'order_base'))->
			join('tb_employee employee on employee.id = order_base.employeeID')->
			join('tb_custom custom on custom.id = order_base.customID')->
			join('tb_magazine magazine on magazine.postCode =  order_base.postCode')->
			join('tb_magazine_origin magazine_origin on magazine_origin.id = order_base.magazineOriginNameID')->
			join('tb_province province on province.id = order_base.provinceID')->
			join('tb_send_goods_sort send_goods_sort on send_goods_sort.id = order_base.sendGoodsSortID')->
			join('tb_send_goods_type send_goods_type on send_goods_type.id = order_base.sendGoodsTypeID')->
			join('tb_send_order_cyle send_order_cyle on send_order_cyle.id = order_base.sendCyleID')->
			join('tb_custom_unit custom_unit on custom_unit.id = order_base.schoolID');
	}
    
    protected function _list($model, $map, &$searchStr)
    {
        $colList    = array();
        $colAllList = array();
        $voList     = array();
        $voListTemp = array();
        
        $colAllList = $this->allShowCols;
        if (isset($_REQUEST['selectColArr']) && !empty($_REQUEST['selectColArr'])) {
            $selectColArr = $_REQUEST['selectColArr'];
            
            foreach ($selectColArr as $key => $value) {
                $colAllList[$key]['selected'] = 1;
                $colList[$key]                = $this->allShowCols[$key];
            }
        } else {
            $colList = $this->defaultShowCols;
        }
        $this->assign('colList', $colList);
        $this->assign('colAllList', $colAllList);
        
        $fieldStr = $this->getListFieldStr($colList, $searchStr);        
        $orderStr = $this->getListOrderStr();
		$groupStr = $this->getListGroupByStr();
		$this->getConditionModel($model);

		$countArr = $model->where($map)->field($fieldStr)->order($orderStr)->group($groupStr)->select();
		$count = count($countArr);	
        
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($count, $listRows);
			
			$this->getConditionModel($model);
            //分页查询数据           
			$voListTemp = $model->where($map)->field($fieldStr)->order($orderStr)->group($groupStr)->
				limit($p->firstRow . ',' . $p->listRows)->select();
            
            foreach ($voListTemp as $voRow) {
                if ($this->isInShowCols($colList, 'orderTime')) {
                    $voRow['orderTime'] = date('Y-m-d', $voRow['orderTime']);
                }
                
                if ($this->isInShowCols($colList, 'isTrans')) {
                    if ($voRow['isTrans']) {
                        $voRow['isTrans'] = '是';
                    } else {
                        $voRow['isTrans'] = '否';
                    }
                }
                
                $voList[] = $voRow;
            }            
            
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
    
    private function exportList($model, $map, $colList)
    {
        $voList     = array();
        $searchStr  = '';
        $voList     = array();
        $voListTemp = array();
        
        $fieldStr = $this->getListFieldStr($colList, $searchStr);        
        $orderStr = $this->getListOrderStr();
		$groupStr = $this->getListGroupByStr();
		$this->getConditionModel($model);

		$countArr = $model->where($map)->field($fieldStr)->order($orderStr)->group($groupStr)->select();
		$count = count($countArr);
        
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($count, $listRows);
			
			$this->getConditionModel($model);
            //分页查询数据           
			$voListTemp = $model->where($map)->field($fieldStr)->order($orderStr)->group($groupStr)->select();
            
            foreach ($voListTemp as $voRow) {
                if ($this->isInShowCols($colList, 'orderTime')) {
                    $voRow['orderTime'] = date('Y-m-d', $voRow['orderTime']);
                }
                
                if ($this->isInShowCols($colList, 'isTrans')) {
                    if ($voRow['isTrans']) {
                        $voRow['isTrans'] = '是';
                    } else {
                        $voRow['isTrans'] = '否';
                    }
                }
                
                $voList[] = $voRow;
            }
            
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
        
        $colList    = array();
        $colAllList = array();
        
        $colAllList = $this->allShowCols;
        if (isset($_REQUEST['selectColArr']) && !empty($_REQUEST['selectColArr'])) {
            $selectColArr  = $_REQUEST['selectColArr'];
            $selectColArrs = explode(',', $selectColArr);
            
            foreach ($selectColArrs as $value) {
                $colList[$value] = $this->allShowCols[$value];
            }
        } else {
            $colList = $this->defaultShowCols;
        }
        
        $OrderBase = D('OrderBase');
        if (!empty($OrderBase)) {
            $voList = $this->exportList($OrderBase, $map, $colList);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "领导查询.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = '';
            foreach ($colList as $colVo) {
                $HeaderStr .= $colVo['chName'] . "\t";
            }
            $HeaderStr .= "\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                foreach ($colList as $colVo) {
                    $ContentStr .= $vo[$colVo['name']] . "\t";
                }
                $ContentStr .= "\n";
            }
            
            $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
            $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
            
            echo $HeaderStr . $ContentStr;
            
            exit();
        } else {
            $this->error('没有数据!');
        }
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