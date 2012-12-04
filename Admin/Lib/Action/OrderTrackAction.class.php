<?php
// 订单跟踪
class OrderTrackAction extends CommonAction
{
    public function _before_add()
    {
    }
    
    public function _before_edit()
    {
    }
    
    
    public function index()
    {
        $SearchSql     = '';
        $BeginDateTemp = '';
        $EndDateTemp   = '';
        
        $EmployeeNewspaper = D('EmployeeNewspaper');
		$Magazine          = D('Magazine');

		$EmployeeId       = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
		$roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);

		if ($roleEname != 'admin')
		{
			$MagazineList = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
		}
		else 
		{
			$MagazineList = $Magazine->field('postCode, name')->select();
		}    
		
        
        $this->assign('MagazineList', $MagazineList);

        $this->searchMap($map, $SearchSql);
        $this->assign('SearchSql', $SearchSql);
		
		if ($map)
		{
			if ($roleEname == "businessManager")
			{
				$map['order_base.employeeID'] = $EmployeeId;
			}
		}
        
        $model                               = D('OrderBase');
        if (!empty($model) && (!empty($map))) {
            $this->_list($model, $map);
        }
        
        $this->display();
        return;
    }

	private function getFieldStr()
	{
		$fieldStr = '
			post_goods.id as id ,  
			order_base.contractID as contractID,
			order_base.batch as batch,
			magazine.name as magazineName,
			magazine_terrm.name as termName ,
			order_base.isChecked as isChecked,
			order_base.recPeople as recPeople,
			order_base.recTelphone as recTelphone,
			order_base.checkTime as checkTime,
			order_base.isSend as isSend,
			order_base.sendTime as sendTime,
			order_base.isReceive as isReceive,
			order_base.receiveTime as receiveTime,
			post_goods.isCheckOut as isCheckOut,
			post_goods.checkOutTime as checkOutTime,
			post_goods.isPrintCheckOut as isPrintCheckOut,
			post_goods.printCheckOutTime as printCheckOutTime,
			post_goods.checkID as checkID,
			post_goods.checkDate as checkDate,
			post_goods.sendGoodsID as sendGoodsID,
			send_goods_type.name as sendGoodsTypeName';

		return $fieldStr;
	}

	private function getOrderStr()
	{
		$order = 'post_goods.isCheckOut asc, post_goods.insertTime desc';

		return $order;
	}

	private function getConditionModel(&$model)
	{
		$model->table(array('tb_order_base' => 'order_base'))->
			join('tb_order_flow_details order_flow_details on order_flow_details.orderID  = order_base.id')->
			join('tb_post_goods post_goods on post_goods.orderFlowID = order_flow_details.id')->
			join('tb_magazine magazine on magazine.postCode  =  order_base.postCode')->
			join('tb_magazine_terrm magazine_terrm on order_flow_details.termID  =  magazine_terrm.id')->
			join('tb_send_goods_type send_goods_type on send_goods_type.id = post_goods.sendGoodsTypeID');
	}

	private function getExpandFieldStr()
	{
		
	}
    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {       
        $this->getConditionModel($model);
		$fieldStr = $this->getExpandFieldStr().$this->getFieldStr();
        //取得满足条件的记录数
        $count = count($model->where($map)->field($fieldStr)->group('order_base.contractID, order_flow_details.termID')->select());
        
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
            
			$order = $this->getOrderStr();            
			$this->getConditionModel($model);

            $voList   = $model->where($map)->order("$order")->
				limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->group('order_base.contractID, order_flow_details.termID')->select();
            
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
    
    
    protected function exportList($model, $map, $sortBy = '', $asc = false)
    {        
        $this->getConditionModel($model);
		$fieldStr = $this->getExpandFieldStr().$this->getFieldStr();
        //取得满足条件的记录数
        $count = count($model->where($map)->field($fieldStr)->group('order_base.contractID, order_flow_details.termID')->select());
        
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
            
			$order = $this->getOrderStr();
			$this->getConditionModel($model);

            $voList   = $model->where($map)->order("$order")->field($fieldStr)->group('order_base.contractID, order_flow_details.termID')->select();
        }
        
        return $voList;
    }
    
    protected function searchMap(&$map, &$SearchSql)
    {
        $BeginDateTemp = '';
        $EndDateTemp   = '';

        if ($_REQUEST['beginTime']) {
            $BeginDateTemp = strtotime($_REQUEST['beginTime']);
            $SearchSql .= 'beginTime/' . $_REQUEST['beginTime'] . '/';
        }
        
        if ($_REQUEST['endTime']) {
            $EndDateTemp = strtotime($_REQUEST['endTime']);
            $SearchSql .= 'endTime/' . $_REQUEST['endTime'] . '/';
        }
        
        if ($_REQUEST['customID']) {
            $map['order_base.customID'] = array('in', $_REQUEST['customID']);
            $SearchSql .= 'customID/' . $_REQUEST['customID'] . '/';
        }

		if ($_REQUEST['orderID'])
		{
			$map['order_base.contractID'] = array('like',"%".$_REQUEST['orderID']."%");
			$SearchSql .= 'orderID/' . $_REQUEST['orderID'] . '/';
		}

		if ($_REQUEST['batch'])
		{
			$map['order_base.batch'] = $_REQUEST['batch'];
            $SearchSql .= 'batch/' . $_REQUEST['batch'] . '/';
		}
        
        if ($_REQUEST['postCode']) {
            $map['order_base.postCode'] = array('in', $_REQUEST['postCode']);
            $SearchSql .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
		
		if ($_REQUEST['Term'])
		{

		}
       
        if ($_REQUEST['month']) {
            $MagazineTerrm = D('MagazineTerrm');
            if ($_REQUEST['postCode']) {
                $MagazineMap['postCode'] = $_REQUEST['postCode'];
            }
            $MagazineMap['month'] = $_REQUEST['month'];
            $MagazineList         = $MagazineTerrm->where($MagazineMap)->field('id, postCode')->select();
            foreach ($MagazineList as $MagazineVo) {
                $MagazineIds .= $MagazineVo['id'] . ',';
            }
            $MagazineIds                        = substr($MagazineIds, 0, strlen($MagazineIds) - 1);
            $map['order_flow_details.termID'] = array(
                'in',
                $MagazineIds
            );
            $SearchSql .= 'month/' . $_REQUEST['month'] . '/';
        }
        
        if ($MagazineList) {
            foreach ($MagazineList as $vo) {
                 $MagazinePostCodes .= $vo['postCode'] . ',';
            }
            $MagazinePostCodes = substr($MagazinePostCodes, 0, strlen($MagazinePostCodes) - 1);
            
			if ($MagazinePostCodes)
			{
				if (!$map['order_base.postCode']) {
					$map['order_base.postCode'] = array(
						'in',
						$MagazinePostCodes
					);
				}
			}
        }
        
        if ($BeginDateTemp || $EndDateTemp) {
            if ($BeginDateTemp && $EndDateTemp) {
                $map['order_base.insertTime'] = array(
                    'between',
                    "$BeginDateTemp, $EndDateTemp"
                );
            } else if ($BeginDateTemp) {
                $map['order_base.insertTime'] = array(
                    'egt',
                    $BeginDateTemp
                );
            } else {
                $map['order_base.insertTime'] = array(
                    'elt',
                    $EndDateTemp
                );
            }
        }
        
        return $map;
    }
    
    public function export()
    {
        $map    = array();
        $voList = array();
        
        $this->searchMap($map, $SearchSql);
		
		if ($map)
		{
			if ($roleEname == "businessManager")
			{
				$map['order_base.employeeID'] = $EmployeeId;
			}
		}
        
        $model                               = D('OrderBase');
        if (!empty($model)) {
            $voList = $this->exportList($model, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "订单跟踪.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "订单编号" . "\t";
			$HeaderStr .= "批次" . "\t";
			$HeaderStr .= "报刊名称" . "\t";
            $HeaderStr .= "期数" . "\t";
			$HeaderStr .= "收货人" . "\t";
			$HeaderStr .= "收货人电话" . "\t";
			$HeaderStr .= "审核状态" . "\t";
			$HeaderStr .= "审核时间" . "\t";
			$HeaderStr .= "派送状态" . "\t";
			$HeaderStr .= "派送时间" . "\t";
            $HeaderStr .= "接收状态" . "\t";
            $HeaderStr .= "接收时间" . "\t";
            $HeaderStr .= "分配状态" . "\t";
            $HeaderStr .= "分配时间" . "\t";
            $HeaderStr .= "打印状态" . "\t";
            $HeaderStr .= "打印时间" . "\t";
            $HeaderStr .= "质检员" . "\t";
            $HeaderStr .= "质检时间" . "\t";
			 $HeaderStr .= "票号" . "\t";
            $HeaderStr .= "发货方式" . "\t\n";
            
            $ContentStr = '';

            /* start of second line */
            foreach ($voList as $vo) {
				$ContentStr .= $vo['contractID'] . "\t";
				$ContentStr .= $vo['batch'] . "\t";
				$ContentStr .= $vo['magazineName'] . "\t";					
				$ContentStr .= $vo['termName'] . "\t";
				$ContentStr .= $vo['recPeople'] . "\t";	
				$ContentStr .= $vo['recTelphone'] . "\t";
				$ContentStr .= $vo['orderStatus'] . "\t";
				if ($vo['isChecked']) {
                    $ContentStr .= "已审\t";
                } else {
                    $ContentStr .= "未审\t";
                }
				if ($vo['checkTime'] != 0)
				{
					$ContentStr .= date('Y-m-d H:s', $vo['checkTime']) . "\t";
				}
				else
				{
					$ContentStr .= "\t";
				}
				if ($vo['isSend']) {
                    $ContentStr .= "已派送\t";
                } else {
                    $ContentStr .= "未派送\t";
                }
				if ($vo['sendTime'] != 0)
				{
					$ContentStr .= date('Y-m-d H:s', $vo['sendTime']) . "\t";
				}
				else
				{
					$ContentStr .= "\t";
				}
				if ($vo['isReceive']) {
                    $ContentStr .= "已接收\t";
                } else {
                    $ContentStr .= "未接收\t";
                }
				if ($vo['receiveTime'] != 0)
				{
					$ContentStr .= date('Y-m-d H:s', $vo['receiveTime']) . "\t";
				}
				else
				{
					$ContentStr .= "\t";
				}
				if ($vo['isCheckOut']) {
                    $ContentStr .= "已分配\t";
                } else {
                    $ContentStr .= "未分配\t";
                }
				if ($vo['checkOutTime'] != 0)
				{
					$ContentStr .= date('Y-m-d H:s', $vo['checkOutTime']) . "\t";
				}
				else
				{
					$ContentStr .= "\t";
				}
				if ($vo['isPrintCheckOut']) {
                    $ContentStr .= "已打印\t";
                } else {
                    $ContentStr .= "未打印\t";
                }
				if ($vo['printCheckOutTime'] != 0)
				{
					$ContentStr .= date('Y-m-d H:s', $vo['printCheckOutTime']) . "\t";
				}
				else
				{
					$ContentStr .= "\t";
				}
				if ($vo['checkID'])
				{
					$ContentStr .= get_employee_name($vo['checkID']) . "\t";
				}
				else
				{
					$ContentStr .= "\t";
				}
				if ($vo['checkDate'] != 0)
				{
					$ContentStr .= date( 'Y-m-d H:s', $vo['checkDate']) . "\t";
				}
				else
				{
					$ContentStr .= "\t";
				}
				$ContentStr .= $vo["sendGoodsID"] . "\t";
				$ContentStr .= $vo["sendGoodsTypeName"] . "\t\n";
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
            case '1': {
                $postCode   = $_REQUEST['postCode'];
				$year   = $_REQUEST['year'];
				$month   = $_REQUEST['month'];
                $MagazineTerrm = M("MagazineTerrm");
                if ($postCode) {
					if ($year)
					{
						$map['year'] = $year;
					}
					if ($month)
					{
						$map['month'] = $month;
					}
					if ($postCode)
					{
						$map['postCode'] = $postCode;
					}
                    $MagazineTerrmList = $MagazineTerrm->where($map)->field('name')->select();
                } else {
                    break;
                }
                $select[] = array(
                    'id' => '',
                    'title' => '--请选择--'
                );
                foreach ($MagazineTerrmList as $MagazineTerrmVo) {
                    $select[] = array(
                        'id' => $MagazineTerrmVo['name'],
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
            'termID' => '',
            'title' => '--请选择--'
        );
        echo json_encode($select);
        return;
    }
}