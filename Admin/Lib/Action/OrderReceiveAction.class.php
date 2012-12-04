<?php
// 订单接收
class OrderReceiveAction extends CommonAction
{
    public function _before_add()
    {
    }
    
    public function _before_edit()
    {
    }
    
    private function _filter(&$map)
    {
		$map['isSend'] = 1;
        if ($_REQUEST['isReceive']) {
            $map['isReceive'] = 1;
        } else {
            $map['isReceive'] = 0;
        }
    }
    
    public function index()
    {
        $SearchStr     = '';
        $BeginDateTemp = '';
        $EndDateTemp   = '';
        
        $EmployeeNewspaper = D('EmployeeNewspaper');
        $Custom            = M("Custom");
        $User              = D('User');
        
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $MagazineList = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
        $CustomList   = $Custom->field('id, name')->select();
        $managerList  = $User->getUserByDutyName('业务经理');
		/* 报刊来源 */
        $MagazineOrigin     = M('MagazineOrigin');
        $MagazineOriginList = $MagazineOrigin->field('id, name')->select();
        $this->assign('MagazineOriginList', $MagazineOriginList);
		/* 发货类型 */
		$SendGoodsSort = M('SendGoodsSort');
		$SendGoodsSortList = $SendGoodsSort->field('id, name')->select();
		$this->assign('SendGoodsSortList', $SendGoodsSortList);
        
        
        $this->assign('MagazineList', $MagazineList);
        $this->assign('CustomList', $CustomList);
        $this->assign('managerList', $managerList);
        
        if ($_REQUEST['beginTime']) {
            $BeginDateTemp = strtotime($_REQUEST['beginTime']);
        }
        
        if ($_REQUEST['endTime']) {
            $EndDateTemp = strtotime($_REQUEST['endTime']);
        }
        
        
        $map = $this->_search();

        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        
        foreach ($map as $key => $value) {
            $SearchStr .= "$key/$value/";
        }
        
        $this->assign('SearchStr', $SearchStr);
        
        $MagazinePostCodes;
        foreach ($MagazineList as $vo) {
             $MagazinePostCodes .= $vo['postCode'] . ',';
        }
        $MagazinePostCodes = substr($MagazinePostCodes, 0, strlen($MagazinePostCodes) - 1);
        
        if (!$map['postCode']) {
			if ($MagazinePostCodes)
			{
				$map['postCode'] = array(
					'in',
					$MagazinePostCodes
				);
			}
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
        $order = 'insertTime desc, employeeID desc, recPeople desc, postCode desc ';
        
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
        $this->assign('numPerPage', C('PAGE_LISTROWS'));
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
        return $map;
        
    }
    
    
    function edit()
    {
    }
    
    
    function export()
    {
		$BeginDateTemp = '';
        $EndDateTemp   = '';

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
        
        $EmployeeNewspaper = D('EmployeeNewspaper');
        
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $MagazineList = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
        
        $MagazinePostCodes;
        foreach ($MagazineList as $vo) {
             $MagazinePostCodes .= $vo['postCode'] . ',';
        }
        $MagazinePostCodes = substr($MagazinePostCodes, 0, strlen($MagazinePostCodes) - 1);
        
		if ($MagazinePostCodes)
		{
			$map['postCode'] = array(
				'in',
				$MagazinePostCodes
			);
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
                
                $order = 'insertTime desc, employeeID desc, recPeople desc, postCode desc ';
                
                $voList = $model->where($map)->order($order)->limit($p->firstRow . ',' . $p->listRows)->select();
                
                $FileName = date('Y-m-d') . "订单接收数据.xls";
                $FileName = iconv("UTF-8", "GBK", $FileName);
                
                header("Content-Type: application/vnd.ms-execl");
                header("Content-Disposition: attachment; filename= $FileName");
                header("Pragma: no-cache");
                header("Expires: 0");
                
                /*first line*/
                $HeaderStr = "日期" . "\t";
				$HeaderStr .= "批次" . "\t";
                $HeaderStr .= "业务经理" . "\t";
				$HeaderStr .= "客户名称" . "\t";
                $HeaderStr .= "收货人" . "\t";
                $HeaderStr .= "省份" . "\t";
                $HeaderStr .= "城市" . "\t";
                $HeaderStr .= "单位" . "\t";
                $HeaderStr .= "班级" . "\t";
                $HeaderStr .= "手机" . "\t";
                $HeaderStr .= "地址" . "\t";
                $HeaderStr .= "报刊" . "\t";
                $HeaderStr .= "份数" . "\t";
                $HeaderStr .= "起月" . "\t";
                $HeaderStr .= "止月" . "\t";
                $HeaderStr .= "发货类型" . "\t";
				$HeaderStr .= "发货方式" . "\t";
				$HeaderStr .= "发货周期" . "\t";
                $HeaderStr .= "付款人" . "\t";
                $HeaderStr .= "是否接收" . "\t\n";
                
                $ContentStr = '';
                
                /*start of second line*/
                foreach ($voList as $vo) {
                    $ContentStr .= date('Y-m-d', $vo['orderTime']) . "\t";
					$ContentStr .= $vo['batch'] . "\t";
                    $ContentStr .= get_employee_name($vo['employeeID']) . "\t";
					$ContentStr .= get_custom_name($vo['customID']) . "\t";
					$ContentStr .= $vo['recPeople'] . "\t";
                    $ContentStr .= get_province_name($vo['provinceID']) . "\t";
                    $ContentStr .= $vo['cityName'] . "\t";
                    $ContentStr .= get_custom_unit_name($vo['schoolID']) . "\t";
                    $ContentStr .= $vo['class'] . "\t";
                    $ContentStr .= $vo['recTelphone'] . "\t";
                    $ContentStr .= $vo['recAddress'] . "\t";
                    $ContentStr .= get_magazine_name($vo['postCode']) . "\t";
                    $ContentStr .= $vo['orderNum'] . "\t";
                    $ContentStr .= $vo['beginOrderDate'] . "\t";
                    $ContentStr .= $vo['endOrderDate'] . "\t";
                    $ContentStr .= get_send_goods_sort_name($vo['sendGoodsSortID']) . "\t";
					$ContentStr .= get_send_goods_type_name($vo['sendGoodsTypeID']) . "\t";
					$ContentStr .= get_send_order_cyle_name($vo['sendCyleID']) . "\t";
                    $ContentStr .= $vo['payPerson'] . "\t";
                    
                    if ($vo['isReceive']) {
                        $ContentStr .= "是\t\n";
                    } else {
                        $ContentStr .= "否\t\n";
                    }
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

    
    public function doReceive()
    {
        $id = $_REQUEST['id'];
        if ($id) {
            $OrderBase                        = D('OrderBase');
            $map['id']   = array(
                'in',
                $id
            );
            $map['isRecevice'] = array(
                'eq',
                '1'
            );
            
            $fieldStr = 'id, isRecevice ';            
            $voList = $OrderBase->where($map)->field($fieldStr)->select();
            
            $isReceiveFlag = 0;            
            foreach ($voList as $key => $vo) {
                if ($vo['isRecevice']) {
                    $isReceiveFlag = 1;
                }
            }            
            if ($isReceiveFlag) {
                $this->error('包含有已接收的订单，请重新选择!');
            }
            
            unset($map);
            $map['id']         = array(
                'in',
                $id
            );
			$data['orderStatus'] = '待转换';
            $data['isReceive'] = '1';
			$data['receiveTime'] = time();
            $OrderBase->where($map)->data($data)->save();
            unset($map);
            unset($data);
            
            $this->successNoClose('接收成功！');
            
        }
        $this->error('没有数据！');
    }
    
    public function cancelReceive()
    {
        $id = $_REQUEST['id'];
        if ($id) {
            $OrderBase                        = D('OrderBase');
            $map['id']   = array(
                'in',
                $id
            );
            $map['isSend'] = array(
                'eq',
                '1'
            );
            
            $fieldStr = 'id, isTrans, isReceive';
            
            $voList = $OrderBase->where($map)->field($fieldStr)->select();
              
            $isReceiveFlag = 1;
			$isTransFlag = 0;
            foreach ($voList as $key => $vo) {
                if (!$vo['isReceive']) {
                    $isReceiveFlag = 0;
                }
                
                if ($vo['isTrans']) {
                    $isTransFlag = 1;
                }
            }
            
            if (!$isReceiveFlag) {
                $this->error('包含未接收的订单！');
            }
            
            if ($isTransFlag) {
                $this->error('取消的订单有已经转换的，请先删除转换后再操作！');
            }
			unset($map);

			$data['orderStatus'] = '待接收';            
            $data['isReceive'] = '0';
			$data['receiveTime'] = 0;
			$map['id']     = array(
                'in',
                $id
            );
            $map['isReceive'] = array(
                'eq',
                '1'
            );
            $OrderBase->where($map)->data($data)->save();
            unset($map);
            unset($data);
        
            $this->successNoClose('操作成功！');
            
        }
        $this->error('没有数据！');
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
}