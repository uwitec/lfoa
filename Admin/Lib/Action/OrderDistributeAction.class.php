<?php
// 订单分配
class OrderDistributeAction extends CommonAction
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
        $Custom            = M("Custom");
        $MagazineOrigin    = M('MagazineOrigin');

        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $MagazineList = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
        $CustomList   = $Custom->field('id, name')->order('name desc')->select();
		$MagazineOriginList = $MagazineOrigin->field('id, name')->order('name desc')->select();
        
        $SendGoodsType     = D('SendGoodsType');
        $SendGoodsTypeList = $SendGoodsType->field('id, name')->order('name desc')->select();
        
        $User           = D('User');
        $PostPeopleList = $User->getUserByDutyName('物流部发行员');
        
        $this->assign('PostPeopleList', $PostPeopleList);
        $this->assign('SendGoodsTypeList', $SendGoodsTypeList);
        $this->assign('MagazineList', $MagazineList);
		$this->assign('MagazineOriginList', $MagazineOriginList);
        $this->assign('CustomList', $CustomList);
        
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
        
        if ($_REQUEST['postCode']) {
            $map['order_base.postCode'] = array('in', $_REQUEST['postCode']);
            $SearchSql .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }

		if ($_REQUEST['magazineOriginNameID']) {
            $map['order_base.magazineOriginNameID'] = $_REQUEST['magazineOriginNameID'];
            $SearchSql .= 'magazineOriginNameID/' . $_REQUEST['magazineOriginNameID'] . '/';
        }
        
        if ($_REQUEST['isCheckOut']) {
            $map['post_goods.isCheckOut'] = $_REQUEST['isCheckOut'];
            $SearchSql .= 'isCheckOut/' . $_REQUEST['isCheckOut'] . '/';
        }
		else
		{
			$map['post_goods.isCheckOut'] = 0;
            $SearchSql .= 'isCheckOut/0/';
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
        
        $this->assign('SearchSql', $SearchSql);
        
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
                $map['post_goods.insertTime'] = array(
                    'between',
                    "$BeginDateTemp, $EndDateTemp"
                );
            } else if ($BeginDateTemp) {
                $map['post_goods.insertTime'] = array(
                    'egt',
                    $BeginDateTemp
                );
            } else {
                $map['post_goods.insertTime'] = array(
                    'elt',
                    $EndDateTemp
                );
            }
        }
        $model                               = D('OrderBase');
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        
        $this->display();
        return;
    }

	private function getFieldStr()
	{
		$fieldStr = '
			post_goods.id as id , 
			order_flow_details.termID as termID , 
			magazine_terrm.month as termMonth,
			order_base.batch as batch,
			order_base.recPeople as recPeople,
			order_base.provinceID as provinceID, 
			order_base.cityName as cityName, 
			order_base.schoolID as schoolID, 
			order_base.class as class, 
			order_base.recTelphone as recTelphone, 
			order_base.recAddress as recAddress, 
			order_base.postCode as postCode, 
			sum(order_flow_details.quantity) as quantity, 
			order_flow_details.beginTermID as beginTermID, 
			order_flow_details.endTermID as endTermID, 
			post_goods.postPeople as postPeople, 
			post_goods.sendGoodsTypeID as sendGoodsTypeID, 
			post_goods.isCheckOut as isCheckOut ';

		return $fieldStr;
	}

	private function getOrderStr()
	{
		$order = 'order_base.postCode asc, magazine_terrm.month desc';

		return $order;
	}

	private function getConditionModel(&$model)
	{
		$model->table(array('tb_order_flow_details' => 'order_flow_details'))->
			join('tb_order_base order_base on order_base.id = order_flow_details.orderID')->
			join('tb_post_goods post_goods on post_goods.orderFlowID = order_flow_details.id')->
			join('tb_magazine_terrm magazine_terrm on magazine_terrm.id =  order_flow_details.termID');
	}

	private function getExpandFieldStr()
	{
		$fieldStr = 'order_base.contractID as contractID,
					order_flow_details.termID as termID,';
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
    
    protected function searchMap()
    {
        $BeginDateTemp = '';
        $EndDateTemp   = '';
        
        $EmployeeNewspaper = D('EmployeeNewspaper');
        
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $MagazineList = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
        
        if ($_REQUEST['beginTime']) {
            $BeginDateTemp = strtotime($_REQUEST['beginTime']);
        }
        
        if ($_REQUEST['endTime']) {
            $EndDateTemp = strtotime($_REQUEST['endTime']);
        }
        
        if ($_REQUEST['customID']) {
            $map['order_base.customID'] = array('in', $_REQUEST['customID']);
            $SearchSql .= 'customID/' . $_REQUEST['customID'] . '/';
        }

		if ($_REQUEST['batch']) {
            $map['order_base.batch'] = $_REQUEST['batch'];
            $SearchSql .= 'batch/' . $_REQUEST['batch'] . '/';
        }
        
        if ($_REQUEST['postCode']) {
            $map['order_base.postCode'] = array('in', $_REQUEST['postCode']);
            $SearchSql .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
        
        if ($_REQUEST['isCheckOut']) {
            $map['post_goods.isCheckOut'] = $_REQUEST['isCheckOut'];
            $SearchSql .= 'isCheckOut/' . $_REQUEST['isCheckOut'] . '/';
        }
		else
		{
			$map['post_goods.isCheckOut'] = 0;
            $SearchSql .= 'isCheckOut/0/';
		}

		if ($_REQUEST['magazineOriginNameID']) {
            $map['order_base.magazineOriginNameID'] = $_REQUEST['magazineOriginNameID'];
            $SearchSql .= 'magazineOriginNameID/' . $_REQUEST['magazineOriginNameID'] . '/';
        }

		if ($_REQUEST['postPeople']) {
            $map['post_goods.postPeople'] = $_REQUEST['postPeople'];
            $SearchSql .= 'postPeople/' . $_REQUEST['postPeople'] . '/';
        }
        
        if ($_REQUEST['month']) {
            $MagazineTerrm = D('MagazineTerrm');
            if ($_REQUEST['postCode']) {
                $MagazineMap['postCode'] = $_REQUEST['postCode'];
            }
            $MagazineMap['month'] = $_REQUEST['month'];
            $MagazineList         = $MagazineTerrm->where($MagazineMap)->field('id')->select();
            foreach ($MagazineList as $MagazineVo) {
                $MagazineIds .= $MagazineVo['id'] . ',';
            }
            $MagazineIds                        = substr($MagazineIds, 0, strlen($MagazineIds) - 1);
            $map['order_flow_details.termID'] = array(
                'in',
                $MagazineIds
            );
        }
        
        if ($MagazineList) {
            $MagazinePostCodes = '';
            foreach ($MagazineList as $vo) {
                 $MagazinePostCodes .= $vo['postCode'] . ',';
            }
            $MagazinePostCodes = substr($MagazinePostCodes, 0, strlen($MagazinePostCodes) - 1);
            if ($MagazinePostCodes)
			{
				if (!$map['postCode']) {
					$map['order_base.postCode'] = array(
						'in',
						$MagazinePostCodes
					);
				}
			}
        }
        
        if ($BeginDateTemp || $EndDateTemp) {
            if ($BeginDateTemp && $EndDateTemp) {
                $map['post_goods.insertTime'] = array(
                    'between',
                    "$BeginDateTemp, $EndDateTemp"
                );
            } else if ($BeginDateTemp) {
                $map['post_goods.insertTime'] = array(
                    'egt',
                    $BeginDateTemp
                );
            } else {
                $map['post_goods.insertTime'] = array(
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
        
        $map = $this->searchMap();
        
        $model                               = D('OrderBase');
        if (!empty($model)) {
            $voList = $this->exportList($model, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "订单分配.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "期数" . "\t";
			$HeaderStr .= "批次" . "\t";
			$HeaderStr .= "月份" . "\t";
            $HeaderStr .= "收货人" . "\t";
			$HeaderStr .= "省份" . "\t";
			$HeaderStr .= "城市" . "\t";
			$HeaderStr .= "单位" . "\t";
			$HeaderStr .= "班级" . "\t";
            $HeaderStr .= "电话" . "\t";
            $HeaderStr .= "地址" . "\t";
            $HeaderStr .= "报刊名称" . "\t";
            $HeaderStr .= "份数" . "\t";
            $HeaderStr .= "起期" . "\t";
            $HeaderStr .= "止期" . "\t";
            $HeaderStr .= "发行人" . "\t";
            $HeaderStr .= "发货方式" . "\t";
            $HeaderStr .= "是否分配" . "\t\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                $ContentStr .= get_magazine_terrm_name($vo['termID']) . "\t";
				$ContentStr .= $vo['batch'] . "\t";
				$ContentStr .= $vo['termMonth'] . "\t";
                $ContentStr .= $vo['recPeople'] . "\t";
				$ContentStr .= get_province_name($vo['provinceID']) . "\t";
				$ContentStr .= $vo['cityName'] . "\t";
				$ContentStr .= get_custom_unit_name($vo['schoolID']) . "\t";
				$ContentStr .= $vo['class'] . "\t";
                $ContentStr .= $vo['recTelphone'] . "\t";
                $ContentStr .= $vo['recAddress'] . "\t";
                $ContentStr .= get_magazine_name($vo['postCode']) . "\t";
                $ContentStr .= $vo['quantity'] . "\t";
                $ContentStr .= get_magazine_terrm_name($vo['beginTermID']) . "\t";
                $ContentStr .= get_magazine_terrm_name($vo['endTermID']) . "\t";
                $ContentStr .= get_employee_name($vo['postPeople']) . "\t";
                $ContentStr .= get_send_goods_type_name($vo['sendGoodsTypeID']) . "\t";
                if ($vo['isCheckOut']) {
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
            $this->error('没有数据!');
        }
    }
    
    public function doDistribute()
    {
        $id = $_REQUEST['id'];
        
        if ($id) {
            $postPeople      = $_REQUEST['postPeople'];
            $sendGoodsTypeID = $_REQUEST['sendGoodsTypeID'];
            $isCheckOutFlag  = 0;
            
            if (!$sendGoodsTypeID) {
                $this->error('请选择发货方式!');
            }
            
            if (!$postPeople) {
                $this->error('请选择发行员!');
            }
            
            $PostGoods = D('PostGoods');
            
            $map['id'] = array(
                'in',
                $id
            );
            $voList    = $PostGoods->field('id, isCheckOut')->where($map)->select();
            if ($voList) {
                foreach ($voList as $key => $vo) {
                    if ($vo['isCheckOut']) {
                        $isCheckOutFlag = 1;
                    }
                }
                
                if ($isCheckOutFlag) {
                    $this->error('包含有已分配的期数，请重新选择!');
                }

				foreach ($voList as $key => $vo)
				{
					$id = array();

					/* 根据post_goods的id得到期数和合同号 */
					$sqlStr = 'select order_flow_details.termID AS termID, order_base.contractID AS contractID from tb_post_goods AS post_goods left join tb_order_flow_details order_flow_details on order_flow_details.id = post_goods.orderFlowID left join tb_order_base order_base on order_base.id = order_flow_details.orderID where (post_goods.id = '.$vo['id'].')';

					$listTemp1 = $PostGoods->query($sqlStr);

					$termID = $listTemp1[0]['termID'];
					$contractID = $listTemp1[0]['contractID'];
					
					/* 根据期数和合同号得到相关的发货信息 */
					$sqlStr = "select post_goods.id as id from tb_post_goods AS post_goods left join tb_order_flow_details order_flow_details on order_flow_details.id = post_goods.orderFlowID left join tb_order_base order_base on order_base.id = order_flow_details.orderID where (order_flow_details.termID = '".$termID."' and order_base.contractID = '".$contractID."')";
					$listTemp2 = $PostGoods->query($sqlStr);
					foreach ($listTemp2 as $listTempVo)
					{
						$id[] = $listTempVo['id']; 
					}
					$ids = implode(',', $id);
					$sqlStr = "UPDATE tb_post_goods SET isCheckOut='1',postPeople='".$postPeople."' ,sendGoodsTypeID='".$sendGoodsTypeID."', checkOutTime = '".time()."' WHERE id in (".$ids.")";
					$PostGoods->execute($sqlStr);
				}
                
                $this->successNoClose('分配成功！');
            } else {
                $this->error('没有数据！');
            }
            
        }
        $this->error('没有数据！');
    }
    
    
    public function cancelDistribute()
    {
        $id = $_REQUEST['id'];
        if ($id) {
            $isCheckOutFlag = 1;
            $PostGoods      = D('PostGoods');
            
            $map['id'] = array(
                'in',
                $id
            );
            $voList    = $PostGoods->field('id, isCheckOut')->where($map)->select();
            foreach ($voList as $key => $vo) {
                if (!$vo['isCheckOut']) {
                    $isCheckOutFlag = 0;
                }
            }
            
            if (!$isCheckOutFlag) {
                $this->error('包含有未分配的期数，请重新选择!');
            }

			foreach ($voList as $key => $vo)
			{
				$id = array();

				/* 根据post_goods的id得到期数和合同号 */
				$sqlStr = 'select order_flow_details.termID AS termID, order_base.contractID AS contractID from tb_post_goods AS post_goods left join tb_order_flow_details order_flow_details on order_flow_details.id = post_goods.orderFlowID left join tb_order_base order_base on order_base.id = order_flow_details.orderID where (post_goods.id = '.$vo['id'].')';

				$listTemp1 = $PostGoods->query($sqlStr);

				$termID = $listTemp1[0]['termID'];
				$contractID = $listTemp1[0]['contractID'];
				
				/* 根据期数和合同号得到相关的发货信息 */
				$sqlStr = "select post_goods.id as id from tb_post_goods AS post_goods left join tb_order_flow_details order_flow_details on order_flow_details.id = post_goods.orderFlowID left join tb_order_base order_base on order_base.id = order_flow_details.orderID where (order_flow_details.termID = '".$termID."' and order_base.contractID = '".$contractID."')";
				$listTemp2 = $PostGoods->query($sqlStr);
				foreach ($listTemp2 as $listTempVo)
				{
					$id[] = $listTempVo['id']; 
				}
				$ids = implode(',', $id);
				$sqlStr = "UPDATE tb_post_goods SET isCheckOut='0', postPeople='', sendGoodsTypeID='', checkOutTime = 0 WHERE id in (".$ids.")";
				$PostGoods->execute($sqlStr);
			}
			
            $this->successNoClose('操作成功！');
            
        }
        $this->error('没有数据！');
    }
}