<?php
// 订单模块
class OrderManageAction extends CommonAction
{
    public function _before_add()
    {
        $PayType        = M('PayType');
        $Magazine       = D('Magazine');
		$EmployeeNewspaper       = D('EmployeeNewspaper');
        $Custom         = M('Custom');
        $SendGoodsSort  = M('SendGoodsSort');
        $SendGoodsType  = M('SendGoodsType');
        $SendOrderCyle  = M('SendOrderCyle');
        $School         = M('CustomUnit');
        $Province       = M('Province');
        $EmployeeId     = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $MagazineOrigin = M('MagazineOrigin');
		$OrderBase		= M('OrderBase');
		$year = date('Y');
		$month = date('m');
		$day = date('d');
        
        $PayTypeList        = $PayType->field('id, name')->order("name desc")->select();
        $MagazineList       = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
        $ProvinceList       = $Province->field('id, name')->order("name desc")->select();
        $SendGoodsSortList  = $SendGoodsSort->field('id, name')->order("name desc")->select();
        $SendGoodsTypeList  = $SendGoodsType->field('id, name')->order("name desc")->select();
        $SendOrderCyleList  = $SendOrderCyle->field('id, name')->order("name desc")->select();
        $SchoolList         = $School->where('employeeID = ' . $EmployeeId)->field('id, name')->order("name desc")->select();
        $MagazineOriginList = $MagazineOrigin->field('id, name')->order("name desc")->select();
		$contractIdMax		= $OrderBase->max('contractID');
		$batchMax			= $OrderBase->where('employeeID = ' . $EmployeeId ." and contractID like '".$year.'_'.$month.'_'.$day."%'")->max('batch');
        
        $this->assign('PayTypeList', $PayTypeList);
        $this->assign('MagazineList', $MagazineList);
        $this->assign('CustomList', $CustomList);
        $this->assign('SendGoodsSortList', $SendGoodsSortList);
        $this->assign('SendGoodsTypeList', $SendGoodsTypeList);
        $this->assign('SendOrderCyleList', $SendOrderCyleList);
        $this->assign('SchoolList', $SchoolList);
        $this->assign('ProvinceList', $ProvinceList);
        $this->assign('EmployeeName', $_SESSION['loginUserName']);
        $this->assign('EmpoyeeId', $EmployeeId);
        $this->assign('insertPerson', $EmployeeId);
        $this->assign('MagazineOriginList', $MagazineOriginList);

		if ( strncmp($year.'_'.$month.'_'.$day, $contractIdMax, 10) == 0)
		{
			$currentIndex = substr($contractIdMax, 11, 4);
			$currentIndex = intval($currentIndex) + 1;
			$contractIdCode = $year.'_'.$month.'_'.$day.'_';
			$contractIdCode = sprintf("$contractIdCode%04d", $currentIndex);
		}
		else
		{
			$contractIdCode = $year.'_'.$month.'_'.$day.'_0001';
			$batchMax = 0;
		}
        
        $this->assign('orderTime', $year.'-'.$month.'-'.$day);
        $this->assign('year', date('Y'));
		$this->assign('contractIdCode', $contractIdCode);
		$this->assign('batch', $batchMax + 1);
    }
    
    public function _before_edit()
    {
        $PayType        = M('PayType');
        $Magazine       = D('Magazine');
        $Custom         = M('Custom');
        $SendGoodsSort  = M('SendGoodsSort');
        $SendGoodsType  = M('SendGoodsType');
        $SendOrderCyle  = M('SendOrderCyle');
        $School         = M('CustomUnit');
        $Province       = M('Province');
        $MagazineOrigin = M('MagazineOrigin');
		$EmployeeNewspaper = D('EmployeeNewspaper');
        
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $PayTypeList        = $PayType->field('id, name')->order("name desc")->select();
        $MagazineList       = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
        $MagazineTerrmList  = array();
        $SendGoodsSortList  = $SendGoodsSort->field('id, name')->order("name desc")->select();
        $SendGoodsTypeList  = $SendGoodsType->field('id, name')->order("name desc")->select();
        $SendOrderCyleList  = $SendOrderCyle->field('id, name')->order("name desc")->select();
        $SchoolList         = $School->where('employeeID = ' . $EmployeeId)->field('id, name')->order("name desc")->select();
        $ProvinceList       = $Province->field('id, name')->order("name desc")->select();
        $MagazineOriginList = $MagazineOrigin->field('id, name')->order("name desc")->select();
        
        $this->assign('PayTypeList', $PayTypeList);
        $this->assign('MagazineList', $MagazineList);
        $this->assign('MagazineTerrmList', $MagazineTerrmList);
        $this->assign('CustomList', $CustomList);
        $this->assign('SendGoodsSortList', $SendGoodsSortList);
        $this->assign('SendGoodsTypeList', $SendGoodsTypeList);
        $this->assign('SendOrderCyleList', $SendOrderCyleList);
        $this->assign('SchoolList', $SchoolList);
        $this->assign('EmployeeName', $_SESSION['loginUserName']);
        $this->assign('EmpoyeeId', $EmployeeId);
        $this->assign('insertPerson', $EmployeeId);
        $this->assign('ProvinceList', $ProvinceList);
        $this->assign('MagazineOriginList', $MagazineOriginList);
    }
    
    public function index()
    {
        $SearchStr     = '';
        $DateTemp      = '';
		$BeginDateTemp = '';
        $EndDateTemp   = '';
		$MagazineSelectStr = '';
        $Magazine      = D("Magazine");
        $Custom        = M("Custom");
        $SendGoodsType = M('SendGoodsType');
		$MagazineType  = D('MagazineType');
		$EmployeeNewspaper = D('EmployeeNewspaper');
        
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
		$roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
		$this->assign('roleEname', $roleEname);
        
        $MagazineList      = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
		$MagazineTypeList = $MagazineType->field('id, name')->select();
		if ($roleEname == 'admin')
		{
			$CustomList   = $Custom->field('id, name')->order("name desc")->select();
		}
		else
		{
			$CustomList   = $Custom->where('employeeID = ' . $EmployeeId)->field('id, name')->order("name desc")->select();
		}
        
        $SendGoodsTypeList = $SendGoodsType->field('id, name')->select();

		/* 业务经理 */
        $User        = D('User');
        $ManagerList = $User->getUserByRoleName('businessManager');
        $this->assign('ManagerList', $ManagerList);
		
		$this->assign('MagazineTypeList', $MagazineTypeList);
        $this->assign('MagazineList', $MagazineList);
        $this->assign('CustomList', $CustomList);
        $this->assign('SendGoodsTypeList', $SendGoodsTypeList);

		if ($_REQUEST['beginTime']) {
            $BeginDateTemp = strtotime($_REQUEST['beginTime']);
            $searchStr .= 'beginTime/' . $_REQUEST['beginTime'] . '/';
        }
        
        if ($_REQUEST['endTime']) {
            $EndDateTemp = strtotime($_REQUEST['endTime']);
            $searchStr .= 'endTime/' . $_REQUEST['endTime'] . '/';
        }

        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
		
        foreach ($map as $key => $value) {
			if ($key == 'isChecked')
			{
				if ($_REQUEST['isChecked'])
				{
					$SearchStr .= "$key/".$_REQUEST['isChecked']."/";
				}
				else
				{
					$SearchStr .= "$key/0/";
				}
			}
			else
			{
				$SearchStr .= "$key/$value/";
			}
        }
        
        if ($SearchStr) {
            $SearchStr = substr($SearchStr, 0, strlen($SearchStr) - 1);
        }
        
        $this->assign('SearchStr', $SearchStr);

		if ($roleEname != 'admin')        
		{
			$map['employeeID'] = $EmployeeId;
		}
		
		if ($BeginDateTemp || $EndDateTemp) {
            if ($BeginDateTemp && $EndDateTemp) {
                $map['orderTime'] = array(
                    'between',
                    "$BeginDateTemp, $EndDateTemp"
                );
            } else if ($BeginDateTemp) {
                $map['orderTime'] = array(
                    'egt',
                    $BeginDateTemp
                );
            } else {
                $map['orderTime'] = array(
                    'elt',
                    $EndDateTemp
                );
            }
        }
        
        $model = D('OrderBase');
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        
        $this->display();
        return;
    }
    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {
        $order = 'isTrans asc, insertTime desc';
        
        //取得满足条件的记录数
        $count = $model->where($map)->count('id');

        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = 30;
            }
            $p = new Page($count, $listRows);
            //分页查询数据
            
            $voList = $model->where($map)->order("$order")->limit($p->firstRow . ',' . $p->listRows)->select();

            //分页跳转的时候保证查询条件
            foreach ($map as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $page = $p->show();
            
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign("page", $page);
        }
        $this->assign('totalCount', $count);
        $this->assign('numPerPage', 30);
        $this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
        
        Cookie::set('_currentUrl_', __SELF__);
        return;
    }
    
    function _search($name = '')
    {
        $name  = 'OrderBase';
        $model = D($name);
        $map   = array();
        foreach ($model->getDbFields() as $key => $val) {
            if (isset($_REQUEST[$val]) && $_REQUEST[$val] != '') {
                $map[$val] = $_REQUEST[$val];
            }
        }
		
		/* 客户信息 */
		if (isset($_REQUEST['customID']) && $_REQUEST['customID'] != "")
		{
			$map['customID'] = array('in', $_REQUEST['customID']);
		}
		
		/* 报刊信息 */
		if (isset($_REQUEST['postCode']) && $_REQUEST['postCode'] != "")
		{
			$map['postCode'] = array('in', $_REQUEST['postCode']);
		}
		/* 首次显示未提交的订单 */
		if (isset($_REQUEST['isChecked']) && $_REQUEST['isChecked'] == "3")
		{
			/* 全部显示 */
			$map['isChecked'] = array('in', '0,1,2');
		}
		else if(isset($_REQUEST['isChecked']))
		{
			$map['isChecked'] = $_REQUEST['isChecked'];
		}
		else
		{
			$_REQUEST['isChecked'] = 2;
			$map['isChecked'] = 2;
		}
        return $map;
        
    }
    
    
    function edit()
    {
        if (method_exists($this, '_before_edit')) {
            $this->_before_edit();
        }
        
        $name              = 'OrderBase';
        $model             = M($name);
        $id                = $_REQUEST[$model->getPk()];
        $singleVo          = array();
        $MagazineTerrmList = array();
        $EmployeeId        = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $map['id'] = array('in', $id);        
        $orderList = $model->where($map)->select();
        unset($map);
        
        if ($orderList) {
			$vo = $orderList[0];
            if ($orderList[0]['isSingle']) {
                /* 获取单期信息 */
                $MagazineTerrm = D('MagazineTerrm');
                $singleVo      = $MagazineTerrm->find($orderList[0]['termID']);
                
                $map['postCode']   = $singleVo['postCode'];
                $map['year']       = $singleVo['year'];
                $map['month']      = $singleVo['month'];
                $MagazineTerrmList = $MagazineTerrm->where($map)->field('id, name')->select();
            }
        }
        
        $this->assign('vo', $vo);
        $this->assign('orderList', $orderList);
        $this->assign('singleVo', $singleVo);
        $this->assign('MagazineTerrmList', $MagazineTerrmList);
        $this->display();
    }


	function editNew()
    {
        if (method_exists($this, '_before_edit')) {
            $this->_before_edit();
        }
        
        $name              = 'OrderBase';
        $model             = M($name);
        $id                = $_REQUEST[$model->getPk()];
        $singleVo          = array();
        $MagazineTerrmList = array();
        $EmployeeId        = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
		$EmployeeNewspaper = D('EmployeeNewspaper');

		$MagazineList       = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);

		$map['id'] = array('in', $id);        
        $orderList = $model->where($map)->select();
		unset($map);
		
		/* 判断客户是否是一个客户 */
		$customId = $orderList[0]['customID'];
		foreach ($orderList as $orderListVo)
		{
			if ($orderListVo['customID'] != $customId)
			{
				$this->error("必须是同一个客户!");
			}
		}

		if ($orderList[0]['employeeID'] != $EmployeeId)
		{        
			unset($orderList);
			$this->error("没有权限!");
		}
		else
		{
			$vo = $orderList[0];
		}
        unset($map);
        
        if ($orderList) {
            if ($orderList[0]['isSingle']) {
                /* 获取单期信息 */
                $MagazineTerrm = D('MagazineTerrm');
                $singleVo      = $MagazineTerrm->find($orderList[0]['termID']);
                
                $map['postCode']   = $singleVo['postCode'];
                $map['year']       = $singleVo['year'];
                $map['month']      = $singleVo['month'];
                $MagazineTerrmList = $MagazineTerrm->where($map)->field('id, name')->select();
            }
        }
        $vo['orderTime'] = time();
		$vo['payTime'] = time();
		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$batchMax			= $model->where('employeeID = ' . $EmployeeId ." and contractID like '".$year.'_'.$month.'_'.$day."%'")->max('batch');
		$contractIdMax		= $model->max('contractID');

		if ( strncmp($year.'_'.$month.'_'.$day, $contractIdMax, 10) == 0)
		{
			$currentIndex = substr($contractIdMax, 11, 4);
			$currentIndex = intval($currentIndex) + 1;
			$contractIdCode = $year.'_'.$month.'_'.$day.'_';
			$contractIdCode = sprintf("$contractIdCode%04d", $currentIndex);
		}
		else
		{
			$contractIdCode = $year.'_'.$month.'_'.$day.'_0001';
			$batchMax = 0;
		}
		$vo['contractID'] = $contractIdCode;
		$vo['batch'] = $batchMax + 1;

        $this->assign('vo', $vo);
		$this->assign('contractID', substr($vo['contractID'], 0, 15));
        $this->assign('orderList', $orderList);
        $this->assign('singleVo', $singleVo);
		$this->assign('MagazineList', $MagazineList);
        $this->assign('MagazineTerrmList', $MagazineTerrmList);
        $this->display();
    }

	function addEditNew()
	{
		if ($_POST['idArr'])
		{
			$OrderBase = D('OrderBase');

			foreach ($_POST['postCodeArr'] as $key => $value) {
				$currentRow               = $key + 1;
				$_POST['postCode']        = $value;
				$_POST['orderNum']        = $_POST['orderNumArr'][$key];
				$_POST['orderYear']       = $_POST['orderYearArr'][$key];
				$_POST['beginOrderDate']  = $_POST['beginOrderDateArr'][$key];
				$_POST['endOrderDate']    = $_POST['endOrderDateArr'][$key];
				$_POST['termID']          = $_POST['termIDArr'][$key];
				$_POST['isSingle']        = $_POST['isSingleArr'][$key];
				$_POST['recPeople']       = $_POST['recPeopleArr'][$key];
				$_POST['recTel']          = $_POST['recTelArr'][$key];
				$_POST['recTelphone']     = $_POST['recTelphoneArr'][$key];
				$_POST['recSpareTel']     = $_POST['recSpareTelArr'][$key];
				$_POST['recFax']          = $_POST['recFaxArr'][$key];
				$_POST['packetType']      = $_POST['packetTypeArr'][$key];
				$_POST['provinceID']      = $_POST['provinceIDArr'][$key];
				$_POST['cityName']        = $_POST['cityNameArr'][$key];
				$_POST['sendGoodsSortID'] = $_POST['sendGoodsSortIDArr'][$key];
				$_POST['sendGoodsTypeID'] = $_POST['sendGoodsTypeIDArr'][$key];
				$_POST['sendCyleID']      = $_POST['sendCyleIDArr'][$key];
				$_POST['recAddress']      = $_POST['recAddressArr'][$key];
				$_POST['schoolID']        = $_POST['schoolIDArr'][$key];
				$_POST['isSchool']        = $_POST['isSchoolArr'][$key];
				$_POST['weakCity']        = $_POST['isWeakCityArr'][$key];
				$_POST['class']           = $_POST['classArr'][$key];
				$_POST['tapeNum']         = $_POST['tapeNumArr'][$key];
				$_POST['answerNum']       = $_POST['answerNumArr'][$key];
				$_POST['memo']            = $_POST['memoArr'][$key];
				$_POST['zipCode']         = $_POST['zipCodeArr'][$key];				
				
				if (!isset($_POST['isSingle']) || empty($_POST['isSingle']) || $_POST['isSingle'] == NULL) 
				{
					if ((!empty($_POST['beginOrderDate'])) || (!empty($_POST['endOrderDate']) || ($_POST['endOrderDate'] == 0) || ($_POST['beginOrderDate'] == 0))) {
						if ((($_POST['beginOrderDate'] <= 0) || ($_POST['beginOrderDate'] > 12)) || (($_POST['endOrderDate'] <= 0) || ($_POST['endOrderDate'] > 12))) {
							$this->error('第' . $currentRow . '行，起订月份和截止月份有误!');
						}
						
						if ($_POST['beginOrderDate'] > $_POST['endOrderDate']) {
							$this->error('第' . $currentRow . '行，起订月份大于截止月份，请重新填写！');
						}
					}				
				}					
			}
			
			/* 订单编号 */
			$contractIDIndex = substr($_POST['contractID'], 11, 4);
			$contractIDPreDate = substr($_POST['contractID'], 0, 11); 
			if ($_POST['isChecked'] == 2)
			{
				$_POST['orderStatus'] = '待提交';
				$insertTime = time();
				$commitTime = 0;
			}
			else
			{
				$_POST['orderStatus'] = '待审核';
				$insertTime = time();
				$commitTime = time();
			}

			foreach ($_POST['postCodeArr'] as $key => $value) 
			{
				$_POST['postCode']        = $value;
				$_POST['orderNum']        = $_POST['orderNumArr'][$key];
				$_POST['orderYear']       = $_POST['orderYearArr'][$key];
				$_POST['beginOrderDate']  = $_POST['beginOrderDateArr'][$key];
				$_POST['endOrderDate']    = $_POST['endOrderDateArr'][$key];
				$_POST['termID']          = $_POST['termIDArr'][$key];
				$_POST['isSingle']        = $_POST['isSingleArr'][$key];
				$_POST['recPeople']       = $_POST['recPeopleArr'][$key];
				$_POST['recTel']          = $_POST['recTelArr'][$key];
				$_POST['recTelphone']     = $_POST['recTelphoneArr'][$key];
				$_POST['recSpareTel']     = $_POST['recSpareTelArr'][$key];
				$_POST['recFax']          = $_POST['recFaxArr'][$key];
				$_POST['packetType']      = $_POST['packetTypeArr'][$key];
				$_POST['provinceID']      = $_POST['provinceIDArr'][$key];
				$_POST['cityName']        = $_POST['cityNameArr'][$key];
				$_POST['sendGoodsSortID'] = $_POST['sendGoodsSortIDArr'][$key];
				$_POST['sendGoodsTypeID'] = $_POST['sendGoodsTypeIDArr'][$key];
				$_POST['sendCyleID']      = $_POST['sendCyleIDArr'][$key];
				$_POST['recAddress']      = $_POST['recAddressArr'][$key];
				$_POST['schoolID']        = $_POST['schoolIDArr'][$key];
				$_POST['isSchool']        = $_POST['isSchoolArr'][$key];
				$_POST['weakCity']        = $_POST['isWeakCityArr'][$key];
				$_POST['class']           = $_POST['classArr'][$key];
				$_POST['tapeNum']         = $_POST['tapeNumArr'][$key];
				$_POST['answerNum']       = $_POST['answerNumArr'][$key];
				$_POST['memo']            = $_POST['memoArr'][$key];
				$_POST['zipCode']         = $_POST['zipCodeArr'][$key];
				$id = $_POST['idArr'][$key];
				$contractID = sprintf("$contractIDPreDate%04d", $contractIDIndex);
				$contractIDIndex++; 

				$sqlStr = "insert into tb_order_base(
					 customID,
					 employeeID,
					 contractID,
					 postCode,
					 termID,
					 beginOrderDate,
					 endOrderDate,
					 orderYear,
					 orderNum,
					 orderTime,
					 sendGoodsSortID,
					 sendGoodsTypeID,
					 sendCyleID,
					 recPeople,
					 recTelphone,
					 recTel,
					 recSpareTel,
					 recFax,
					 schoolID,
					 class,
					 recAddress,
					 packetType,
					 provinceID,
					 cityName,
					 zipCode,
					 orderStatus,
					 validateStatus,
					 validateDate,
					 validateID,
					 memo,
					 isSingle,
					 isChecked,
					 isSchool,
					 weakCity,
					 isPay,
					 payTime,
					 payType,
					 insertPerson,
					 insertTime,
					 commitTime,
					 payPerson,
					 magazineOriginNameID,
					 batch,
					 tapeNum,
					 answerNum
					)select 
					 customID,
					 employeeID,
					 '".$contractID."',
					 '".$_POST['postCode']."',
					 '".$_POST['termID']."',
					 '".$_POST['beginOrderDate']."',
					 '".$_POST['endOrderDate']."',
					 '".$_POST['orderYear']."',
					 '".$_POST['orderNum']."',
					 '".strtotime($_POST['orderTime'])."',
					 '".$_POST['sendGoodsSortID']."',
					 '".$_POST['sendGoodsTypeID']."',
					 '".$_POST['sendCyleID']."',
					  '".$_POST['recPeople']."',
					 '".$_POST['recTelphone']."',
					 '".$_POST['recTel']."',
					 '".$_POST['recSpareTel']."',
					 '".$_POST['recFax']."',
					 '".$_POST['schoolID']."',
					 '".$_POST['class']."',
					 '".$_POST['recAddress']."',
					  '".$_POST['packetType']."',
					 '".$_POST['provinceID']."',
					 '".$_POST['cityName']."',
					 '".$_POST['zipCode']."',
					 '".$_POST['orderStatus']."',
					 validateStatus,
					 '0',
					 '0',
					 '".$_POST['memo']."',
					 '".$_POST['isSingle']."',
					 '".$_POST['isChecked']."',
					 '".$_POST['isSchool']."',
					 '".$_POST['weakCity']."',
					 '".$_POST['isPay']."',
					 '".strtotime($_POST['payTime'])."',
					 '".$_POST['payType']."',
					 insertPerson,
					 '".$insertTime."',
					 '".$commitTime."',
					 '".$_POST['payPerson']."',
					 '".$_POST['magazineOriginNameID']."', 
					 '".$_POST['batch']."', 
					 '".$_POST['tapeNum']."',
					 '".$_POST['answerNum']."'
					from tb_order_base where (id = ".$id.")";

				$OrderBase->execute($sqlStr);
			}

			$this->success("保存成功!"); 
		}
		else
		{
			$this->error("没有数据!");
		}        
	}
    
    
    public function export()
    {
		$BeginDateTemp = '';
		$EndDateTemp = '';
        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }

		if ($_REQUEST['beginTime']) {
            $BeginDateTemp = strtotime($_REQUEST['beginTime']);
        }
        
        if ($_REQUEST['endTime']) {
            $EndDateTemp = strtotime($_REQUEST['endTime']);
        }
        if ($BeginDateTemp || $EndDateTemp) {
            if ($BeginDateTemp && $EndDateTemp) {
                $map['orderTime'] = array(
                    'between',
                    "$BeginDateTemp, $EndDateTemp"
                );
            } else if ($BeginDateTemp) {
                $map['orderTime'] = array(
                    'egt',
                    $BeginDateTemp
                );
            } else {
                $map['orderTime'] = array(
                    'elt',
                    $EndDateTemp
                );
            }
        }
        
        $model = D('OrderBase');
        if (!empty($model)) {
            /* 查询自己的信息 */
            $EmployeeId        = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
			$roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
			if ($roleEname != 'admin')
			{
				$map['employeeID'] = $EmployeeId;
			}
            $count             = $model->where($map)->count('id');
            if ($count > 0) {
                import("ORG.Util.Page");
				$order = 'isChecked asc, insertTime desc';		
                $voList = $model->where($map)->order($order)->select();
                $FileName = date('Y-m-d') . "订单数据.xls";
                $FileName = iconv("UTF-8", "GBK", $FileName);
                
                header("Content-Type: application/vnd.ms-execl");
                header("Content-Disposition: attachment; filename= $FileName");
                header("Pragma: no-cache");
                header("Expires: 0");
                
                /*first line*/
                $HeaderStr = "日期" . "\t";
                $HeaderStr .= "客户" . "\t";
				$HeaderStr .= "批次" . "\t";
                $HeaderStr .= "收货人" . "\t";
                $HeaderStr .= "手机" . "\t";
                $HeaderStr .= "地址" . "\t";
                $HeaderStr .= "报刊分类" . "\t";
				$HeaderStr .= "报刊" . "\t";
                $HeaderStr .= "份数" . "\t";
                $HeaderStr .= "起月" . "\t";
                $HeaderStr .= "止月" . "\t";
                $HeaderStr .= "付款方式" . "\t";
				$HeaderStr .= "发货周期" . "\t";
				$HeaderStr .= "发货类型" . "\t";
				$HeaderStr .= "发货方式" . "\t";
				$HeaderStr .= "邮编" . "\t";				
                $HeaderStr .= "省份" . "\t";
				$HeaderStr .= "城市" . "\t";
				$HeaderStr .= "单位" . "\t";
				$HeaderStr .= "班级" . "\t";
				$HeaderStr .= "付款人" . "\t";
				$HeaderStr .= "是否单期" . "\t";
				$HeaderStr .= "期数名称" . "\t";
                $HeaderStr .= "是否审核" . "\t";
				$HeaderStr .= "备注" . "\t\n";
                
                $ContentStr = '';
                
                /*start of second line*/
                foreach ($voList as $vo) {
                    $ContentStr .= date('Y-m-d', $vo['orderTime']) . "\t";
                    $ContentStr .= get_custom_name($vo['customID']) . "\t";
					$ContentStr .= $vo['batch'] . "\t";
                    $ContentStr .= $vo['recPeople'] . "\t";
                    $ContentStr .= $vo['recTelphone'] . "\t";
                    $ContentStr .= $vo['recAddress'] . "\t";
					$ContentStr .= get_magazine_type_name_by_postcode($vo['postCode']) . "\t";
                    $ContentStr .= get_magazine_name($vo['postCode']) . "\t";
                    $ContentStr .= $vo['orderNum'] . "\t";
                    $ContentStr .= $vo['beginOrderDate'] . "\t";
                    $ContentStr .= $vo['endOrderDate'] . "\t";
                    $ContentStr .= get_pay_type_name($vo['payType']) . "\t";

					$ContentStr .= get_pay_type_name($vo['payType']) . "\t";
					$ContentStr .= get_send_order_cyle_name($vo['sendCyleID']) . "\t";
					$ContentStr .= get_send_goods_sort_name($vo['sendGoodsSortID']) . "\t";
					$ContentStr .= get_send_goods_type_name($vo['sendGoodsTypeID']) . "\t";
                    $ContentStr .= $vo['zipCode'] . "\t";					

					$ContentStr .= $vo['cityName'] . "\t";
					$ContentStr .= get_custom_unit_name($vo['schoolID']) . "\t";
					$ContentStr .= $vo['class'] . "\t";
					$ContentStr .= $vo['payPerson'] . "\t";
					if ($vo['isSingle'] == 1)
					{
						$ContentStr .= "是\t";
					}
					else
					{
						$ContentStr .= "否\t";
					}
					if ($vo['isSingle'] == 1)
					{
						$ContentStr .= get_magazine_terrm_name($vo['termID'])."\t";
					}
					else
					{
						$ContentStr .= "\t";
					}					
                    if ($vo['isChecked'] == 2)
					{
						$ContentStr .= "未提交\t";
					}
					else if ($vo['isChecked'] == 1)
					{
						$ContentStr .= "已审核\t";
					}
					else if ($vo['isChecked'] == 0)
					{
						$ContentStr .= "未审核\t";
					}
					$ContentStr .= $vo['memo'] . "\t\n";
                }
                
                $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
                $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
                
                echo $HeaderStr . $ContentStr;
                
                exit();
                
            } else {
                //错误提示
                $this->error('没有数据!');
            }
        }
	}
    
    function update()
    {
        $OrderBase  = D('OrderBase');
        $currentRow = 1;
        
        if ($_POST['isChecked'] == 2)
		{
			$_POST['orderStatus'] = '待提交';
			$insertTime = time();
			$commitTime = 0;
		}
		else
		{
			$_POST['orderStatus'] = '待审核';
			$insertTime = time();
			$commitTime = time();
		}
        
        if (!isset($_POST['isPay']) || empty($_POST['isPay'])) {
            $_POST['isPay'] = 0;
        }
        
        foreach ($_POST['postCodeArr'] as $key => $value) {
            $currentRow               = $key + 1;
            $_POST['postCode']        = $value;
            $_POST['orderNum']        = $_POST['orderNumArr'][$key];
            $_POST['orderYear']       = $_POST['orderYearArr'][$key];
            $_POST['beginOrderDate']  = $_POST['beginOrderDateArr'][$key];
            $_POST['endOrderDate']    = $_POST['endOrderDateArr'][$key];
            $_POST['termID']          = $_POST['termIDArr'][$key];
            $_POST['isSingle']        = $_POST['isSingleArr'][$key];
            $_POST['recPeople']       = $_POST['recPeopleArr'][$key];
            $_POST['recTel']          = $_POST['recTelArr'][$key];
            $_POST['recTelphone']     = $_POST['recTelphoneArr'][$key];
			$_POST['recSpareTel']     = $_POST['recSpareTelArr'][$key];
			$_POST['recFax']          = $_POST['recFaxArr'][$key];
			$_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['provinceID']      = $_POST['provinceIDArr'][$key];
            $_POST['cityName']        = $_POST['cityNameArr'][$key];
            $_POST['sendGoodsSortID'] = $_POST['sendGoodsSortIDArr'][$key];
            $_POST['sendGoodsTypeID'] = $_POST['sendGoodsTypeIDArr'][$key];
            $_POST['sendCyleID']      = $_POST['sendCyleIDArr'][$key];
            $_POST['recAddress']      = $_POST['recAddressArr'][$key];
            $_POST['schoolID']        = $_POST['schoolIDArr'][$key];
            $_POST['isSchool']        = $_POST['isSchoolArr'][$key];
			$_POST['weakCity']        = $_POST['isWeakCityArr'][$key];
            $_POST['class']           = $_POST['classArr'][$key];
			$_POST['tapeNum']         = $_POST['tapeNumArr'][$key];
			$_POST['answerNum']       = $_POST['answerNumArr'][$key];
            $_POST['memo']            = $_POST['memoArr'][$key];
			$_POST['zipCode']         = $_POST['zipCodeArr'][$key];
            
			if (!isset($_POST['isSingle']) || empty($_POST['isSingle']) || $_POST['isSingle'] == NULL) 
			{
				if ((!empty($_POST['beginOrderDate'])) || (!empty($_POST['endOrderDate']) || ($_POST['endOrderDate'] == 0) || ($_POST['beginOrderDate'] == 0))) {
					if ((($_POST['beginOrderDate'] <= 0) || ($_POST['beginOrderDate'] > 12)) || (($_POST['endOrderDate'] <= 0) || ($_POST['endOrderDate'] > 12))) {
						$this->error('第' . $currentRow . '行，起订月份和截止月份有误!');
					}
					
					if ($_POST['beginOrderDate'] > $_POST['endOrderDate']) {
						$this->error('第' . $currentRow . '行，起订月份大于截止月份，请重新填写！');
					}
				}
			}
            
            $_POST['id'] = $_POST['idArr'][$key];
            
            if ($_POST['id']) {
                $isChecked = $OrderBase->where('id = ' . $_POST['id'])->getField('isChecked');
                
                if ($isChecked == 1) {
                    $this->error('第' . $currentRow . '行，订单已经通过审核，不能进行修改!');
                }
            }
        }
        
        foreach ($_POST['postCodeArr'] as $key => $value) {
            $currentRow               = $key + 1;
            $_POST['postCode']        = $value;
            $_POST['orderNum']        = $_POST['orderNumArr'][$key];
            $_POST['orderYear']       = $_POST['orderYearArr'][$key];
            $_POST['beginOrderDate']  = $_POST['beginOrderDateArr'][$key];
            $_POST['endOrderDate']    = $_POST['endOrderDateArr'][$key];
            $_POST['termID']          = $_POST['termIDArr'][$key];
            $_POST['isSingle']        = $_POST['isSingleArr'][$key];
            $_POST['recPeople']       = $_POST['recPeopleArr'][$key];
            $_POST['recTel']          = $_POST['recTelArr'][$key];
            $_POST['recTelphone']     = $_POST['recTelphoneArr'][$key];
			$_POST['recSpareTel']     = $_POST['recSpareTelArr'][$key];
			$_POST['recFax']          = $_POST['recFaxArr'][$key];
			$_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['provinceID']      = $_POST['provinceIDArr'][$key];
            $_POST['cityName']        = $_POST['cityNameArr'][$key];
            $_POST['sendGoodsSortID'] = $_POST['sendGoodsSortIDArr'][$key];
            $_POST['sendGoodsTypeID'] = $_POST['sendGoodsTypeIDArr'][$key];
            $_POST['sendCyleID']      = $_POST['sendCyleIDArr'][$key];
            $_POST['recAddress']      = $_POST['recAddressArr'][$key];
            $_POST['schoolID']        = $_POST['schoolIDArr'][$key];
            $_POST['isSchool']        = $_POST['isSchoolArr'][$key];
			$_POST['weakCity']        = $_POST['isWeakCityArr'][$key];
            $_POST['class']           = $_POST['classArr'][$key];
			$_POST['tapeNum']         = $_POST['tapeNumArr'][$key];
			$_POST['answerNum']       = $_POST['answerNumArr'][$key];
            $_POST['memo']            = $_POST['memoArr'][$key];
			$_POST['zipCode']         = $_POST['zipCodeArr'][$key];
			$_POST['insertTime']      = $insertTime;
			$_POST['commitTime']      = $commitTime;
			$_POST['contractID']      = $_POST['contractIDArr'][$key];

			if ($_POST['isSingle'] == 1) 
			{
				$_POST['beginOrderDate']  = 0;
				$_POST['endOrderDate']    = 0;
			}
            
            $_POST['id'] = $_POST['idArr'][$key];
            
            if (false === $OrderBase->create()) {
                $this->error($OrderBase->getError());
            }
            // 更新数据
            $list = $OrderBase->save();
        }
        
        if (false !== $list) {
            if (method_exists($this, '_after_update')) {
                $this->_after_update($list);
            }
            //成功提示
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            
            $this->success('编辑成功!');
        } else {
            //错误提示
            $this->error('编辑失败!');
        }
    }
    
    public function insert()
    {
        $OrderBase  = D('OrderBase');
        $currentRow = 1;

		if ($_POST['isChecked'] == 2)
		{
			$_POST['orderStatus'] = '待提交';
			$insertTime = time();
			$commitTime = 0;
		}
		else
		{
			$_POST['orderStatus'] = '待审核';
			$insertTime = time();
			$commitTime = time();
		}
        
        if (!isset($_POST['isPay']) || empty($_POST['isPay'])) {
            $_POST['isPay'] = 0;
        }
        
        foreach ($_POST['postCodeArr'] as $key => $value) {
            $currentRow               = $key + 1;
            $_POST['postCode']        = $value;
            $_POST['orderNum']        = $_POST['orderNumArr'][$key];
            $_POST['orderYear']       = $_POST['orderYearArr'][$key];
            $_POST['beginOrderDate']  = $_POST['beginOrderDateArr'][$key];
            $_POST['endOrderDate']    = $_POST['endOrderDateArr'][$key];
            $_POST['termID']          = $_POST['termIDArr'][$key];
            $_POST['isSingle']        = $_POST['isSingleArr'][$key];
            $_POST['recPeople']       = $_POST['recPeopleArr'][$key];
            $_POST['recTel']          = $_POST['recTelArr'][$key];
            $_POST['recTelphone']     = $_POST['recTelphoneArr'][$key];
			$_POST['recSpareTel']     = $_POST['recSpareTelArr'][$key];
			$_POST['recFax']          = $_POST['recFaxArr'][$key];
			$_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['provinceID']      = $_POST['provinceIDArr'][$key];
            $_POST['cityName']        = $_POST['cityNameArr'][$key];
            $_POST['sendGoodsSortID'] = $_POST['sendGoodsSortIDArr'][$key];
            $_POST['sendGoodsTypeID'] = $_POST['sendGoodsTypeIDArr'][$key];
            $_POST['sendCyleID']      = $_POST['sendCyleIDArr'][$key];
            $_POST['recAddress']      = $_POST['recAddressArr'][$key];
            $_POST['schoolID']        = $_POST['schoolIDArr'][$key];
            $_POST['isSchool']        = $_POST['isSchoolArr'][$key];
			$_POST['weakCity']        = $_POST['isWeakCityArr'][$key];
            $_POST['class']           = $_POST['classArr'][$key];
            $_POST['tapeNum']         = $_POST['tapeNumArr'][$key];
			$_POST['answerNum']       = $_POST['answerNumArr'][$key];
			$_POST['memo']            = $_POST['memoArr'][$key];
			$_POST['zipCode']         = $_POST['zipCodeArr'][$key];
            			$_POST['insertTime']      = $insertTime;
			$_POST['commitTime']      = $commitTime;

			if (!isset($_POST['isSingle']) || empty($_POST['isSingle']) || $_POST['isSingle'] == NULL) 
			{
				if ((!empty($_POST['beginOrderDate'])) || (!empty($_POST['endOrderDate']) || ($_POST['endOrderDate'] == 0) || ($_POST['beginOrderDate'] == 0))) {
					if ((($_POST['beginOrderDate'] <= 0) || ($_POST['beginOrderDate'] > 12)) || (($_POST['endOrderDate'] <= 0) || ($_POST['endOrderDate'] > 12))) {
						$this->error('第' . $currentRow . '行，起订月份和截止月份有误!');
					}
					
					if ($_POST['beginOrderDate'] > $_POST['endOrderDate']) {
						$this->error('第' . $currentRow . '行，起订月份大于截止月份，请重新填写！');
					}
				}
			}
        }

		/* 订单编号 */
		$contractIDIndex = substr($_POST['contractID'], 11, 4);
		$contractIDPreDate = substr($_POST['contractID'], 0, 11); 
        
        foreach ($_POST['postCodeArr'] as $key => $value) {
            $currentRow               = $key + 1;
            $_POST['postCode']        = $value;
            $_POST['orderNum']        = $_POST['orderNumArr'][$key];
            $_POST['orderYear']       = $_POST['orderYearArr'][$key];
            $_POST['beginOrderDate']  = $_POST['beginOrderDateArr'][$key];
            $_POST['endOrderDate']    = $_POST['endOrderDateArr'][$key];
            $_POST['termID']          = $_POST['termIDArr'][$key];
            $_POST['isSingle']        = $_POST['isSingleArr'][$key];
            $_POST['recPeople']       = $_POST['recPeopleArr'][$key];
            $_POST['recTel']          = $_POST['recTelArr'][$key];
            $_POST['recTelphone']     = $_POST['recTelphoneArr'][$key];
			$_POST['recSpareTel']     = $_POST['recSpareTelArr'][$key];
			$_POST['recFax']          = $_POST['recFaxArr'][$key];
			$_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['packetType']      = $_POST['packetTypeArr'][$key];
            $_POST['provinceID']      = $_POST['provinceIDArr'][$key];
            $_POST['cityName']        = $_POST['cityNameArr'][$key];
            $_POST['sendGoodsSortID'] = $_POST['sendGoodsSortIDArr'][$key];
            $_POST['sendGoodsTypeID'] = $_POST['sendGoodsTypeIDArr'][$key];
            $_POST['sendCyleID']      = $_POST['sendCyleIDArr'][$key];
            $_POST['recAddress']      = $_POST['recAddressArr'][$key];
            $_POST['schoolID']        = $_POST['schoolIDArr'][$key];
            $_POST['isSchool']        = $_POST['isSchoolArr'][$key];
			$_POST['weakCity']        = $_POST['isWeakCityArr'][$key];
            $_POST['class']           = $_POST['classArr'][$key];
			$_POST['tapeNum']         = $_POST['tapeNumArr'][$key];
			$_POST['answerNum']       = $_POST['answerNumArr'][$key];
            $_POST['memo']            = $_POST['memoArr'][$key];
			$_POST['zipCode']         = $_POST['zipCodeArr'][$key];
			$_POST['insertTime']      = $insertTime;
			$_POST['commitTime']      = $commitTime;

			$contractID = sprintf("$contractIDPreDate%04d", $contractIDIndex);
			$contractIDIndex++;

			$_POST['contractID'] = $contractID;
            
            if (!isset($_POST['isSingle']) || empty($_POST['isSingle'])) {
                $_POST['isSingle'] = 0;
            }
			else
			{
				$_POST['beginOrderDate']  = 0;
				$_POST['endOrderDate']    = 0;
			}
            
            if (!isset($_POST['isSchool']) || empty($_POST['isSchool'])) {
                $_POST['isSchool'] = 0;
            }
            
            if (false === $OrderBase->create()) {
                $this->error($OrderBase->getError());
            }
            
            //保存当前数据对象
            $list = $OrderBase->add();
        }
        
        if ($list !== false) {
            //保存成功
            if (method_exists($this, '_after_insert')) {
                $this->_after_insert($list);
            }
            
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('新增成功!');
        } else {
            //失败提示
            $this->error('新增失败!');
        }
    }
    
    public function foreverdelete()
    {
        //删除指定记录
        $name  = 'OrderBase';
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if (isset($id)) {
                $transFlag = false;
                $condition = array(
                    $pk => array(
                        'in',
                        explode(',', $id)
                    )
                );
                $transList = $model->where($condition)->field('isChecked')->select();
                
                if ($transList) {
                    foreach ($transList as $key => $vo) {
                        if ($vo['isChecked'] == '1') {
                            $transFlag = true;
                            
                        }
                    }
                } else {
                    $this->error('非法操作!');
                }
                
                if ($transFlag) {
                    $this->error('订单已经入库，不能删除!');
                }
                
                if (false !== $model->where($condition)->delete()) {
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


	public function orderSubmit()
    {
        //提交订单
        $name  = 'OrderBase';
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if (isset($id)) {
                $transFlag = false;
                $condition = array(
                    $pk => array(
                        'in',
                        explode(',', $id)
                    )
                );
                $transList = $model->where($condition)->field('isChecked')->select();
                
                if ($transList) {
                    foreach ($transList as $key => $vo) {
                        if ($vo['isChecked'] == '1') {
                            $transFlag = true;
                            
                        }
                    }
                } else {
                    $this->error('非法操作!');
                }
                
                if ($transFlag) {
                    $this->error('订单已经审核，不能重复提交!');
                }
				$data['isChecked'] = 0;
				$data['commitTime'] = time();
                
                if (false !== $model->where($condition)->setField($data)) {
                    $this->successNoClose('提交成功！');
                } else {
                    $this->error('提交失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
        $this->forward();
    }
	
    
    function detail()
    {
        $name  = 'OrderBase';
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
                    $MagazineTerrmList = $MagazineTerrm->where($map)->field('id, name')->order('name desc')->select();
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
            
            case 2:
                /* 客户列表 */ {
                $isOldClient = $_REQUEST['isOldClient'];
                $Custom      = M("Custom");
                $EmployeeId  = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
                $map         = array();
                
                $map['employeeID'] = $EmployeeId;
                if ($isOldClient) {
                    $map['isOldClient'] = 1;
                }
                $CustomList = $Custom->where($map)->field('id, name')->order('name desc')->select();
                $select[]   = array(
                    'id' => '',
                    'title' => '--请选择--'
                );
                foreach ($CustomList as $CustomVo) {
                    $select[] = array(
                        'id' => $CustomVo['id'],
                        'title' => $CustomVo['name']
                    );
                }
                
                echo json_encode($select);
                return;
            }
                break;
            
            case 3:
                /* 客户单位 */ {
                $isSchool   = $_REQUEST['isSchool'];
				$EmployeeId  = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
                $CustomUnit = M("CustomUnit");
                $map        = array();
                
                if ($isSchool) {
                    $map['isSchool'] = 1;
                }
				$map['employeeID'] = $EmployeeId;
                $CustomUnitList = $CustomUnit->where($map)->field('id, name')->order('name desc')->select();
                $select[]       = array(
                    'id' => '',
                    'title' => '--请选择--'
                );
                foreach ($CustomUnitList as $CustomUnitVo) {
                    $select[] = array(
                        'id' => $CustomUnitVo['id'],
                        'title' => $CustomUnitVo['name']
                    );
                }
                
                echo json_encode($select);
                return;
            }
                break;
            
            case 4:
                /* 客户货物信息 */ {
                $customId    = $_REQUEST['customId'];
                $CustomGoods = M("CustomGoods");
                $map         = array();
                
                if ($customId) {
                    $map['custom_goods.customId'] = $customId;
                    
                    $CustomGoodsList = $CustomGoods->table(array( 'tb_custom_goods' => 'custom_goods'))->
						join('tb_province province on province.id =custom_goods.provinceID')->
						where($map)->field('custom_goods.id, custom_goods.recName, custom_goods.address,  custom_goods.className, custom_goods.cityName as cityName, province.name as provinceName')->order('custom_goods.recName desc')->select();
                    
                    $select[] = array(
                        'id' => '',
                        'title' => '--请选择--'
                    );
                    foreach ($CustomGoodsList as $CustomGoodsVo) {
                        $select[] = array(
                            'id' => $CustomGoodsVo['id'],
                            'title' => $CustomGoodsVo['recName'] . ' ' . $CustomGoodsVo['provinceName'] . ' ' . $CustomGoodsVo['cityName'] . ' ' . msubstr($CustomGoodsVo['address'], 0, 10) . ' ' . msubstr($CustomGoodsVo['className'], 0, 10)
                        );
                    }
                    echo json_encode($select);
                    return;
                }
            }
                break;
            
            case 5:
                /* 客户货物详细信息 */ {
                $goodsId     = $_REQUEST['goodsId'];
                $CustomGoods = M("CustomGoods");
                
                if ($goodsId) {
                    $CustomGoodsVo = $CustomGoods->find($goodsId);
                    if ($CustomGoodsVo) {
                        echo json_encode($CustomGoodsVo);
                    }
                    return;
                }
                
                return;
            }
                break;
            
            case 6:
                /* 发货公司信息 */ {
                $sendSortID    = $_REQUEST['sendSortID'];
                $SendGoodsType = M("SendGoodsType");
                
                if ($sendSortID) {
                    $SendGoodsTypeList = $SendGoodsType->where('sendGoodsSortID = ' . $sendSortID)->order('name desc')->select();
                    
                    $select[] = array(
                        'id' => '',
                        'title' => '--请选择--'
                    );
                    foreach ($SendGoodsTypeList as $SendGoodsTypeVo) {
                        $select[] = array(
                            'id' => $SendGoodsTypeVo['id'],
                            'title' => $SendGoodsTypeVo['name']
                        );
                    }
                    
                    echo json_encode($select);
                    return;
                }
            }
                break;
			
			case 7:
                /* 报刊信息 */ {
                $typeID    = $_REQUEST['typeID'];
				$Magazine = M("Magazine");
                
                if ($typeID) {
                    $postCodeList = $Magazine->where('typeID = ' . $typeID)->order('name desc')->select();
                    
                    $select[] = array(
                        'postCode' => '',
                        'title' => '--请选择--'
                    );
                    foreach ($postCodeList as $postCodeVo) {
                        $select[] = array(
                            'postCode' => $postCodeVo['postCode'],
                            'title' => $postCodeVo['name']
                        );
                    }
                    
                    echo json_encode($select);
                    return;
                }
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