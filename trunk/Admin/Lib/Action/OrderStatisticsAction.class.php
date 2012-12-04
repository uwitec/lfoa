<?php
// 订单统计信息
class OrderStatisticsAction extends CommonAction
{
    private $defaultShowCols = array(
		array('name' => 'batch', 'tableName' => 'order_base.batch as batch', 'chName' => '订单批次', 'colWidth' => '60'),
		array('name' => 'employeeName', 'tableName' => 'employee.employeeName as employeeName', 'chName' => '业务经理', 'colWidth' => '100'), 
		array('name' => 'customName', 'tableName' => 'custom.name as customName', 'chName' => '客户名', 'colWidth' => '80'), 
		array('name' => 'recPeople', 'tableName' => 'order_base.recPeople as recPeople', 'chName' => '收货人', 'colWidth' => '80'),
		array('name' => 'postCodeName', 'tableName' => 'magazine.name as postCodeName', 'chName' => '报刊名称', 'colWidth' => '100'), 
		array('name' => 'provinceName', 'tableName' => 'province.name as provinceName', 'chName' => '省份', 'colWidth' => '80'), 
		array('name' => 'cityName', 'tableName' => 'order_base.cityName as cityName', 'chName' => '城市', 'colWidth' => '80'), 	
	);
    
    private $allShowCols = array(
		array('name' => 'batch', 'tableName' => 'order_base.batch as batch', 'chName' => '订单批次', 'colWidth' => '60'),
		array('name' => 'employeeName', 'tableName' => 'employee.employeeName as employeeName', 'chName' => '业务经理', 'colWidth' => '100'), 
		array('name' => 'customName', 'tableName' => 'custom.name as customName', 'chName' => '客户名', 'colWidth' => '80'), 
		array('name' => 'recPeople', 'tableName' => 'order_base.recPeople as recPeople', 'chName' => '收货人', 'colWidth' => '80'),
		array('name' => 'postCodeName', 'tableName' => 'magazine.name as postCodeName', 'chName' => '报刊名称', 'colWidth' => '100'), 
		array('name' => 'provinceName', 'tableName' => 'province.name as provinceName', 'chName' => '省份', 'colWidth' => '80'), 
		array('name' => 'cityName', 'tableName' => 'order_base.cityName as cityName', 'chName' => '城市', 'colWidth' => '80'), 
		array('name' => 'orderYear', 'tableName' => 'order_base.orderYear as orderYear', 'chName' => '年份', 'colWidth' => '80'), 
		array('name' => 'beginOrderDate', 'tableName' => 'order_base.beginOrderDate as beginOrderDate', 'chName' => '起始月份', 'colWidth' => '80'), 
		array('name' => 'endOrderDate', 'tableName' => 'order_base.endOrderDate as endOrderDate', 'chName' => '截止月份', 'colWidth' => '80'), 		 
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
		array('name' => 'orderStatus', 'tableName' => 'order_base.orderStatus as orderStatus', 'chName' => '订单状态', 'colWidth' => '100')
	);
    
	private $monthArray = array();
	private $month;
	private $orderYear;
	private $beginMonth;

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
        if ($roleEname == 'admin') {
            $MagazineList = $Magazine->field('postCode, name')->select();
        } else {
            $MagazineList = $Magazine->getMagazineListByEmId($EmployeeId);
        }
        $this->assign('MagazineList', $MagazineList);
        
        /* 省份信息 */
        $Province     = M("Province");
        $ProvinceList = $Province->field('id, name')->select();
        $this->assign('ProvinceList', $ProvinceList);
        
        /* 客户信息 */
        $Custom = M('Custom');
        $map    = array();
        if ($roleEname == 'businessManager'){
            $map['employeeID'] = $EmployeeId;
        }
        $CustomList = $Custom->where($map)->field('id, name')->select();
        $this->assign('CustomList', $CustomList);
        unset($map);
        
        /* 报刊来源 */
        $MagazineOrigin     = M('MagazineOrigin');
        $MagazineOriginList = $MagazineOrigin->field('id, name')->select();
        $this->assign('MagazineOriginList', $MagazineOriginList);
        
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
		$OrderMonth = date('m');
        
        if (isset($_REQUEST['orderYear']) && !empty($_REQUEST['orderYear'])) {
            $map['order_base.orderYear'] = array(
                'eq',
                $_REQUEST['orderYear']
            );
            $searchStr .= 'orderYear/' . $_REQUEST['orderYear'] . '/';
			$OrderYear = $_REQUEST['orderYear'];
        }
		else
		{
			$map['order_base.orderYear'] = array(
                'eq',
                $OrderYear
            );
            $searchStr .= 'orderYear/' . $OrderYear . '/';
		}
		$this->orderYear = $OrderYear;
        
        if (isset($_REQUEST['postCode']) && !empty($_REQUEST['postCode'])) {
            $map['order_base.postCode'] = array(
                'in',
                $_REQUEST['postCode']
            );
            $searchStr .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
        
        if (isset($_REQUEST['magazineOriginNameID']) && !empty($_REQUEST['magazineOriginNameID'])) {
            $map['order_base.magazineOriginNameID'] = array(
                'eq',
                $_REQUEST['magazineOriginNameID']
            );
            $searchStr .= 'magazineOriginNameID/' . $_REQUEST['magazineOriginNameID'] . '/';
        }
        
        if (isset($_REQUEST['customID']) && !empty($_REQUEST['customID'])) {
            $map['order_base.customID'] = array(
                'in',
                $_REQUEST['customID']
            );
            $searchStr .= 'customID/' . $_REQUEST['customID'] . '/';
        }
        
        if (isset($_REQUEST['provinceID']) && !empty($_REQUEST['provinceID'])) {
            $map['order_base.provinceID'] = array(
                'eq',
                $_REQUEST['provinceID']
            );
            $searchStr .= 'provinceID/' . $_REQUEST['provinceID'] . '/';
        }
        
        if (isset($_REQUEST['cityName']) && !empty($_REQUEST['cityName'])) {
            $map['order_base.cityName'] = array(
                'eq',
                $_REQUEST['cityName']
            );
            $searchStr .= 'cityName/' . $_REQUEST['cityName'] . '/';
        }
        
        if (isset($_REQUEST['employeeID']) && !empty($_REQUEST['employeeID'])) {
            $map['order_base.employeeID'] = array(
                'eq',
                $_REQUEST['employeeID']
            );
            $searchStr .= 'employeeID/' . $_REQUEST['employeeID'] . '/';
        }
        
        if (isset($_REQUEST['isTrans']) && !empty($_REQUEST['isTrans'])) {
            $map['order_base.isTrans'] = array(
                'eq',
                $_REQUEST['isTrans']
            );
            $searchStr .= 'isTrans/' . $_REQUEST['isTrans'] . '/';
        }

		if (isset($_REQUEST['batch']) && !empty($_REQUEST['batch'])) {
            $map['order_base.batch'] = array(
                'eq',
                $_REQUEST['batch']
            );
            $searchStr .= 'batch/' . $_REQUEST['batch'] . '/';
        }
        
        /* 处理月份 */
		if (isset($_REQUEST['beginMonth']) && !empty($_REQUEST['beginMonth'])) {
			$beginMonth = $_REQUEST['beginMonth'];
			$searchStr .= 'beginMonth/' . $_REQUEST['beginMonth'] . '/';
		}
		else
		{
			$beginMonth = 1;
		}
		$this->beginMonth = $beginMonth;
		if (isset($_REQUEST['endMonth']) && !empty($_REQUEST['endMonth'])) {
			$OrderMonth = $_REQUEST['endMonth'];
			$searchStr .= 'endMonth/' . $_REQUEST['endMonth'] . '/';
		}
		if (isset($_REQUEST['otherMonth']) && !empty($_REQUEST['otherMonth'])) {
			$searchStr .= 'otherMonth/' . $_REQUEST['otherMonth'] . '/';
			$OrderMonth = 12;
		}
		$this->month = $OrderMonth;
		if ($this->month < 7)
		{
			for ($i = $this->beginMonth ; $i <= $this->month; $i++)
			{
				$vo['colNmae'] = $i."月";
				$vo['name'] = "month".$i;
				$this->monthArray[$i] = $vo;
			}			
		}
		else
		{
			if ($this->beginMonth < 7)
			{
				$vo['colNmae'] = "上半年";			
				$vo['name'] = "monthHalfYear";
				$this->monthArray[6] = $vo;
				$i = 7;
			}
			else
			{
				$i = $this->beginMonth;
			}
			for (; $i <= $this->month; $i++)
			{
				$vo['colNmae'] = $i."月";
				$vo['name'] = "month".$i;
				$this->monthArray[$i] = $vo;
			}
		}
		$this->assign('monthArray', $this->monthArray);	
    }
    
    private function listFilter(&$map)
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $roleEname  = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if ($roleEname == 'businessManager')
		{
            $map['order_base.employeeID'] = $EmployeeId;
        }
		$map['employee_newspaper.personID'] = $EmployeeId;
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

		$fieldStr  .= ",order_base.postCode as postCode, order_base.customID as customID";
        
        return $fieldStr;
    }
    
    private function getListOrderStr()
    {
        $sortStr = 'employee.employeeName asc, order_base.batch desc, order_base.postCode desc, order_base.recPeople desc';

		return $sortStr;
    }

	private function getListGroupByStr()
	{
		$groupStr = 'order_base.postCode, order_base.customID';

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
			join('tb_custom_unit custom_unit on custom_unit.id = order_base.schoolID')->
			join('tb_employee_newspaper employee_newspaper on employee_newspaper.postCode = order_base.postCode');
	}
    
    protected function _list($model, $map, &$searchStr)
    {
        $colList    = array();
        $colAllList = array();
        $voList     = array();
        $voListTemp = array();
		$OrderBase = D("OrderBase");
        
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
		
		$customID = '';
		$postCode = '';
		$count = 0;
		$countArr = $model->where($map)->field($fieldStr)->order($orderStr)->group($groupStr)->select();
		foreach ($countArr as $countRow)
		{
			if ($customID == $countRow['customID'] && $postCode == $countRow['postCode'])
			{
				continue;
			}
			else
			{
				$count++;
			}
		}

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

			$customID = '';
			$postCode = '';
            
            foreach ($voListTemp as $voRow) {
				if ($customID == $voRow['customID'] && $postCode == $voRow['postCode'])
				{
					continue;
				}
				$customID = $voRow['customID'];
				$postCode = $voRow['postCode'];
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

				if ($this->month < 7)
				{
					foreach ($this->monthArray as $key => $monthArrayVo)
					{
						$monthIndex = $key;
						$voRow[$monthArrayVo['name']] = $OrderBase->where("beginOrderDate <= $monthIndex and $monthIndex <= endOrderDate and postCode='".$voRow['postCode']."' and orderYear = '".$this->orderYear."' and customID = '".$voRow['customID']."'")->sum('orderNum');						
						$sumQuantity += $voRow[$monthArrayVo['name']];
					}
				}
				else
				{
					foreach ($this->monthArray as $key => $monthArrayVo)
					{
						if ($key == 6)
						{
							for ($i = $this->beginMonth; $i < 7; $i++)
							{
								$voRow[$monthArrayVo['name']] += $OrderBase->where("beginOrderDate <= $i and $i <= endOrderDate and postCode='".$voRow['postCode']."' and orderYear = '".$this->orderYear."' and customID = '".$voRow['customID']."'")->sum('orderNum');
							}
						}
						else
						{
							$monthIndex = $key;
							$voRow[$monthArrayVo['name']] = $OrderBase->where("beginOrderDate <= $monthIndex and $monthIndex <= endOrderDate and postCode='".$voRow['postCode']."' and orderYear = '".$this->orderYear."' and customID = '".$voRow['customID']."'")->sum('orderNum');						
						}
						$sumQuantity += $voRow[$monthArrayVo['name']];
					}
				}
				$voRow['sumQuantity'] = $sumQuantity;
				unset($sumQuantity);
                
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
		$OrderBase = D("OrderBase");
        
        $fieldStr = $this->getListFieldStr($colList, $searchStr);        
        $orderStr = $this->getListOrderStr();
		$groupStr = $this->getListGroupByStr();
		$this->getConditionModel($model);

		$customID = '';
		$postCode = '';
		$count = 0;
		$countArr = $model->where($map)->field($fieldStr)->order($orderStr)->group($groupStr)->select();
		foreach ($countArr as $countRow)
		{
			if ($customID == $countRow['customID'] && $postCode == $countRow['postCode'])
			{
				continue;
			}
			else
			{
				$count++;
			}
		}
        
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
            
			$customID = '';
			$postCode = '';
            foreach ($voListTemp as $voRow) {
				if ($customID == $voRow['customID'] && $postCode == $voRow['postCode'])
				{
					continue;
				}
				$customID = $voRow['customID'];
				$postCode = $voRow['postCode'];
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

				if ($this->month < 7)
				{
					foreach ($this->monthArray as $key => $monthArrayVo)
					{
						$monthIndex = $key;
						$voRow[$monthArrayVo['name']] = $OrderBase->where("beginOrderDate <= $monthIndex and $monthIndex <= endOrderDate and postCode='".$voRow['postCode']."' and orderYear = '".$this->orderYear."' and customID = '".$voRow['customID']."'")->sum('orderNum');						
						$sumQuantity += $voRow[$monthArrayVo['name']];
					}
				}
				else
				{
					foreach ($this->monthArray as $key => $monthArrayVo)
					{
						if ($key == 0)
						{
							for ($i = $this->beginMonth; $i < 7; $i++)
							{
								$voRow[$monthArrayVo['name']] += $OrderBase->where("beginOrderDate <= $i and $i <= endOrderDate and postCode='".$voRow['postCode']."' and orderYear = '".$this->orderYear."' and customID = '".$voRow['customID']."'")->sum('orderNum');
							}
						}
						else
						{
							$monthIndex = $key;
							$voRow[$monthArrayVo['name']] = $OrderBase->where("beginOrderDate <= $monthIndex and $monthIndex <= endOrderDate and postCode='".$voRow['postCode']."' and orderYear = '".$this->orderYear."' and customID = '".$voRow['customID']."'")->sum('orderNum');						
						}
						$sumQuantity += $voRow[$monthArrayVo['name']];
					}
				}
				$voRow['sumQuantity'] = $sumQuantity;
				unset($sumQuantity);
                
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
            $FileName = date('Y-m-d') . "订单统计.xls";
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
			foreach ($this->monthArray as $monthArrayVo)
			{
				$HeaderStr .= $monthArrayVo['colNmae']. "\t";
			}
			$HeaderStr .= "总数\t";
            $HeaderStr .= "\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                foreach ($colList as $colVo) {
                    $ContentStr .= $vo[$colVo['name']] . "\t";
                }
				foreach ($this->monthArray as $monthArrayVo)
				{
					$ContentStr .= $vo[$monthArrayVo['name']] . "\t";
				}
				$ContentStr .= $vo['sumQuantity'] . "\t";
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