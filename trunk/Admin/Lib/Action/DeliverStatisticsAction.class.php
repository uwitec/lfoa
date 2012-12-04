<?php
// 发货统计信息
class DeliverStatisticsAction extends CommonAction
{
    private $defaultShowCols = array(array('name' => 'postPeopleName', 'chName' => '发行员', 'colWidth' => '80'), array('name' => 'customName', 'chName' => '客户名', 'colWidth' => '80'), array('name' => 'recPeople', 'chName' => '收货人', 'colWidth' => '80'), array('name' => 'postCodeName', 'chName' => '报刊名称', 'colWidth' => '100'), array('name' => 'quantity', 'chName' => '数量', 'colWidth' => '80'), array('name' => 'provinceName', 'chName' => '省份', 'colWidth' => '80'), array('name' => 'cityName', 'chName' => '城市', 'colWidth' => '80'), array('name' => 'orderYear', 'chName' => '年份', 'colWidth' => '80'), array('name' => 'beginOrderDate', 'chName' => '起始月份', 'colWidth' => '80'), array('name' => 'endOrderDate', 'chName' => '截止月份', 'colWidth' => '80'));
    
    private $allShowCols = array(array('name' => 'postPeopleName', 'chName' => '发行员', 'colWidth' => '80'), array('name' => 'customName', 'chName' => '客户名', 'colWidth' => '80'), array('name' => 'recPeople', 'chName' => '收货人', 'colWidth' => '80'), array('name' => 'postCodeName', 'chName' => '报刊名称', 'colWidth' => '100'), array('name' => 'quantity', 'chName' => '数量', 'colWidth' => '80'), array('name' => 'provinceName', 'chName' => '省份', 'colWidth' => '80'), array('name' => 'cityName', 'chName' => '城市', 'colWidth' => '80'), array('name' => 'orderYear', 'chName' => '年份', 'colWidth' => '80'), array('name' => 'beginOrderDate', 'chName' => '起始月份', 'colWidth' => '80'), array('name' => 'endOrderDate', 'chName' => '截止月份', 'colWidth' => '80'), array('name' => 'orderTime', 'chName' => '签单日期', 'colWidth' => '80'), array('name' => 'sendGoodsSortName', 'chName' => '发货类型', 'colWidth' => '80'), array('name' => 'sendGoodsTypeName', 'chName' => '发货方式', 'colWidth' => '80'), array('name' => 'payPersonName', 'chName' => '付款人', 'colWidth' => '80'), array('name' => 'sendCyleName', 'chName' => '发货周期', 'colWidth' => '80'), array('name' => 'schoolName', 'chName' => '单位', 'colWidth' => '100'), array('name' => 'classroom', 'chName' => '班级', 'colWidth' => '100'), array('name' => 'zipCode', 'chName' => '邮编', 'colWidth' => '80'), array('name' => 'employeeName', 'chName' => '所属业务经理', 'colWidth' => '100'), array('name' => 'isCheckOut', 'chName' => '是否出库', 'colWidth' => '60'), array('name' => 'isPrintSendLabel', 'chName' => '是否发货', 'colWidth' => '60'), array('name' => 'orderStatus', 'chName' => '订单状态', 'colWidth' => '100'));
    
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
        }
		else
		{
			$MagazineList = $Magazine->field('postCode, name')->select();
		}
        $this->assign('MagazineList', $MagazineList);
        
        /* 客户信息 */
        $Custom = M('Custom');
        $map    = array();
        
        $CustomList = $Custom->where($map)->field('id, name')->select();
        $this->assign('CustomList', $CustomList);
        unset($map);
        
        /* 报刊来源 */
        $MagazineOrigin     = M('MagazineOrigin');
        $MagazineOriginList = $MagazineOrigin->field('id, name')->select();
        $this->assign('MagazineOriginList', $MagazineOriginList);
        
        /* 发行员 */
        $User           = D('User');
        $PostPeopleList = $User->getUserByDutyName('物流部发行员');
        $this->assign('PostPeopleList', $PostPeopleList);
        
        /* 省份信息 */
        $Province     = M("Province");
        $ProvinceList = $Province->field('id, name')->select();
        $this->assign('ProvinceList', $ProvinceList);
    }
    
    
    /* 组织index页面的搜索语句 */
    private function indexSearch(&$map, &$searchStr)
    {
        $BeginMonthTemp = '';
        $EndMonthTemp   = '';
        
        if (isset($_REQUEST['orderYear']) && !empty($_REQUEST['orderYear'])) {
            $map['orderYear'] = array(
                'eq',
                $_REQUEST['orderYear']
            );
            $searchStr .= 'orderYear/' . $_REQUEST['orderYear'] . '/';
        }
        
        if (isset($_REQUEST['postCode']) && !empty($_REQUEST['postCode'])) {
            $map['postCode'] = array(
                'eq',
                $_REQUEST['postCode']
            );
            $searchStr .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
        
        if (isset($_REQUEST['provinceID']) && !empty($_REQUEST['provinceID'])) {
            $map['provinceID'] = array(
                'eq',
                $_REQUEST['provinceID']
            );
            $searchStr .= 'provinceID/' . $_REQUEST['provinceID'] . '/';
        }
        
        if (isset($_REQUEST['postPeople']) && !empty($_REQUEST['postPeople'])) {
            $map['postPeople'] = array(
                'eq',
                $_REQUEST['postPeople']
            );
            $searchStr .= 'postPeople/' . $_REQUEST['postPeople'] . '/';
        }
        
        if (isset($_REQUEST['customID']) && !empty($_REQUEST['customID'])) {
            $map['customID'] = array(
                'eq',
                $_REQUEST['customID']
            );
            $searchStr .= 'customID/' . $_REQUEST['customID'] . '/';
        }
        
        if (isset($_REQUEST['cityName']) && !empty($_REQUEST['cityName'])) {
            $map['cityName'] = array(
                'eq',
                $_REQUEST['cityName']
            );
            $searchStr .= 'cityName/' . $_REQUEST['cityName'] . '/';
        }
        
        
        if (isset($_REQUEST['isCheckOut']) && !empty($_REQUEST['isCheckOut'])) {
            $map['isCheckOut'] = array(
                'eq',
                $_REQUEST['isCheckOut']
            );
            $searchStr .= 'isCheckOut/' . $_REQUEST['isCheckOut'] . '/';
        }
        
        if (isset($_REQUEST['isPrintSendLabel']) && !empty($_REQUEST['isPrintSendLabel'])) {
            $map['isPrintSendLabel'] = array(
                'eq',
                $_REQUEST['isPrintSendLabel']
            );
            $searchStr .= 'isPrintSendLabel/' . $_REQUEST['isPrintSendLabel'] . '/';
        }
        
        if (isset($_REQUEST['beginMonth']) && !empty($_REQUEST['beginMonth'])) {
            $BeginMonthTemp = $_REQUEST['beginMonth'];
            $searchStr .= 'beginMonth/' . $_REQUEST['beginMonth'] . '/';
        }
        
        if (isset($_REQUEST['endMonth']) && !empty($_REQUEST['endMonth'])) {
            $EndMonthTemp = $_REQUEST['endMonth'];
            $searchStr .= 'endMonth/' . $_REQUEST['endMonth'] . '/';
        }
        
        
        if ($BeginMonthTemp || $EndMonthTemp) {
            if ($BeginMonthTemp && $EndMonthTemp) {
                $map['termMonth'] = array(
                    'between',
                    "$BeginMonthTemp, $EndMonthTemp"
                );
            } else if ($BeginDateTemp) {
                $map['termMonth'] = array(
                    'egt',
                    $BeginMonthTemp
                );
            } else {
                $map['termMonth'] = array(
                    'elt',
                    $EndMonthTemp
                );
            }
        }
    }
    
    private function listFilter(&$map)
    {
        $map['isReceive'] = 1;
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
        
        $ViewSendGoodsDetails = D('ViewSendGoodsDetails');
        if (!empty($ViewSendGoodsDetails)) {
            $this->_list($ViewSendGoodsDetails, $map, $searchStr);
        }
        
        $this->assign('searchStr', $searchStr);
        
        $this->display();
    }
    
    private function getListFieldStr($colList, &$searchStr)
    {
        $fieldStr  = '';
        $searchStr .= 'selectColArr/';
        foreach ($colList as $key => $col) {
            $fieldStr .= $col['name'] . ',';
            $searchStr .= $key . ',';
        }
        $searchStr = substr($searchStr, 0, strlen($searchStr) - 1) . '/';
        $fieldStr  = substr($fieldStr, 0, strlen($fieldStr) - 1);
        
        return $fieldStr;
    }
    
    private function getListSortStr()
    {
        $sortStr = 'orderYear desc, termMonth desc, termName desc';
		
		return $sortStr;
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
        
        $fieldStr = 'contractID, orderID,';
        
        $fieldStr .= $this->getListFieldStr($colList, $searchStr);
        
        $orderStr = $this->getListSortStr();

		$model->where($map)->field('*')->select();
		
		$whereStr = $model->getLastSql();
		$whereStr = substr($whereStr, 49);
		
		if ($whereStr)
		{
			//取得满足条件的记录数
			$sql = 'select '.$fieldStr.', sum(quantity) AS quantity from tb_view_send_goods_details where '.$whereStr.' group by customID, postCodeName order by '.$orderStr.' ';
		}
		else
		{
			//取得满足条件的记录数
			$sql = 'select '.$fieldStr.', sum(quantity) AS quantity from tb_view_send_goods_details  group by customID, postCodeName order by '.$orderStr.' ';
		}

		$countArr = $model->query($sql);        
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
            
            //分页查询数据        
			$sql .= 'limit '.$p->firstRow . ',' . $p->listRows;
			$voListTemp = $model->query($sql);   
            
            foreach ($voListTemp as $voRow) {
                if ($this->isInShowCols($colList, 'orderTime')) {
                    $voRow['orderTime'] = date('Y-m-d', $voRow['orderTime']);
                }
                
                if ($this->isInShowCols($colList, 'isCheckOut')) {
                    if ($voRow['isCheckOut']) {
                        $voRow['isCheckOut'] = '是';
                    } else {
                        $voRow['isCheckOut'] = '否';
                    }
                }
                
                if ($this->isInShowCols($colList, 'isPrintSendLabel')) {
                    if ($voRow['isPrintSendLabel']) {
                        $voRow['isPrintSendLabel'] = '是';
                    } else {
                        $voRow['isPrintSendLabel'] = '否';
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
        
        $fieldStr = 'contractID, orderID,';
        
        $fieldStr .= $this->getListFieldStr($colList, $searchStr);
        
        $orderStr = $this->getListSortStr();

		$model->where($map)->field('*')->select();
		
		$whereStr = $model->getLastSql();
		$whereStr = substr($whereStr, 49);
        
        //取得满足条件的记录数
		$sql = 'select '.$fieldStr.', sum(quantity) AS quantity from tb_view_send_goods_details where '.$whereStr.' group by customID, postCode order by '.$orderStr.' ';
        
        $countArr = $model->query($sql);        
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
			$voListTemp = $model->query($sql);
            
            foreach ($voListTemp as $voRow) {
                if ($this->isInShowCols($colList, 'orderTime')) {
                    $voRow['orderTime'] = date('Y-m-d', $voRow['orderTime']);
                }
                
                if ($this->isInShowCols($colList, 'isCheckOut')) {
                    if ($voRow['isCheckOut']) {
                        $voRow['isCheckOut'] = '是';
                    } else {
                        $voRow['isCheckOut'] = '否';
                    }
                }
                
                if ($this->isInShowCols($colList, 'isPrintSendLabel')) {
                    if ($voRow['isPrintSendLabel']) {
                        $voRow['isPrintSendLabel'] = '是';
                    } else {
                        $voRow['isPrintSendLabel'] = '否';
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
        
        $ViewSendGoodsDetails = D('ViewSendGoodsDetails');
        if (!empty($ViewSendGoodsDetails)) {
            $voList = $this->exportList($ViewSendGoodsDetails, $map, $colList);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "发货统计.xls";
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