<?php
// 发货标签打印
class SendLabelPrintAction extends CommonAction
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

		if ($_REQUEST['isPrintSendLabel']) {
            $map['post_goods.isPrintSendLabel'] = $_REQUEST['isPrintSendLabel'];
            $SearchSql .= 'isPrintSendLabel/' . $_REQUEST['isPrintSendLabel'] . '/';
        }  
		else
		{
			$map['post_goods.isPrintSendLabel'] = 0;
            $SearchSql .= 'isPrintSendLabel/0/';
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
        $map['post_goods.isCheckOut'] = array(
            'eq',
            '1'
        );

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
			magazine_terrm.name as termName, 
			magazine_terrm.month as termMonth,
			order_base.recPeople as recPeople,
			order_base.provinceID as provinceID, 
			order_base.cityName as cityName, 
			order_base.schoolID as schoolID, 
			order_base.class as class, 
			order_base.recTelphone as recTelphone, 
			order_base.recAddress as recAddress, 
			magazine.name as magazineName, 
			order_flow_details.quantity as quantity, 
			order_flow_details.beginTermID as beginTermID, 
			order_flow_details.endTermID as endTermID, 
			post_goods.postPeople as postPeople, 
			post_goods.sendGoodsTypeID as sendGoodsTypeID, 
			post_goods.isPrintCheckOut as isPrintCheckOut ,
			post_goods.checkOutNum as checkOutNum,
			post_goods.isPrintSendGoods as isPrintSendGoods ,
			post_goods.sendGoodsNum as sendGoodsNum,
			post_goods.isPrintSendLabel as isPrintSendLabel ,
			post_goods.sendLabelNum as sendLabelNum,
			sum(post_goods.sendNum) as sendNum';

		return $fieldStr;
	}

	private function getOrderStr()
	{
		/* 先显示的尾未打印的数据，然后是某一期的报刊，再接着为某一个人的报刊，报刊按*/
		$order = 'post_goods.isPrintSendGoods asc, magazine_terrm.name desc, order_base.provinceID desc, order_base.recPeople desc, magazine.name desc';

		return $order;
	}

	private function getConditionModel(&$model)
	{
		$model->table(array('tb_order_flow_details' => 'order_flow_details'))->
			join('tb_order_base order_base on order_base.id = order_flow_details.orderID')->
			join('tb_post_goods post_goods on post_goods.orderFlowID = order_flow_details.id')->
			join('tb_magazine_terrm magazine_terrm on magazine_terrm.id =  order_flow_details.termID')->
			join('tb_magazine_origin magazine_origin on magazine_origin.id =  order_base.magazineOriginNameID')->
			join('tb_magazine magazine on magazine.postCode =  order_base.postCode');
	}
    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {       
       $this->getConditionModel($model);
        $fieldStr = $this->getFieldStr();
        //取得满足条件的记录数
        $count = count($model->where($map)->field($fieldStr)->group('order_base.contractID, order_flow_details.termID')->select());
        
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '100';
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
        $this->assign('numPerPage', 100);
        $this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
        
        Cookie::set('_currentUrl_', __SELF__);
        return;
    }
    
    
    protected function exportList($model, $map, $sortBy = '', $asc = false)
    {        
        $this->getConditionModel($model);
        //取得满足条件的记录数
        $count = $model->where($map)->count('post_goods.id');
        
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '100';
            }
            $p = new Page($count, $listRows);
            //分页查询数据
            
			$order = $this->getOrderStr();
            $fieldStr = $this->getFieldStr();
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
        
        if ($_REQUEST['postCode']) {
            $map['order_base.postCode'] = array('in', $_REQUEST['postCode']);
            $SearchSql .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
        
        if ($_REQUEST['isCheckOut']) {
            $map['post_goods.isCheckOut'] = $_REQUEST['isCheckOut'];
            $SearchSql .= 'isCheckOut/' . $_REQUEST['isCheckOut'] . '/';
        }

		if ($_REQUEST['magazineOriginNameID']) {
            $map['order_base.magazineOriginNameID'] = $_REQUEST['magazineOriginNameID'];
            $SearchSql .= 'magazineOriginNameID/' . $_REQUEST['magazineOriginNameID'] . '/';
        }

		if ($_REQUEST['isPrintSendLabel']) {
            $map['post_goods.isPrintSendLabel'] = $_REQUEST['isPrintSendLabel'];
            $SearchSql .= 'isPrintSendLabel/' . $_REQUEST['isPrintSendLabel'] . '/';
        }  
		else
		{
			$map['post_goods.isPrintSendLabel'] = 0;
            $SearchSql .= 'isPrintSendLabel/0/';
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
        
         $map['post_goods.isCheckOut'] = array(
            'eq',
            '1'
        );
        $model                               = D('OrderBase');
        if (!empty($model)) {
            $voList = $this->exportList($model, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "发货标签数据.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "期数" . "\t";
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
            $HeaderStr .= "发行人" . "\t";
            $HeaderStr .= "发货方式" . "\t";
            $HeaderStr .= "是否打印发货标签" . "\t";
			$HeaderStr .= "标签编号" . "\t\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                $ContentStr .= $vo['termName'] . "\t";
				$ContentStr .= $vo['termMonth'] . "\t";
                $ContentStr .= $vo['recPeople'] . "\t";
				$ContentStr .= get_province_name($vo['provinceID']) . "\t";
				$ContentStr .= $vo['cityName'] . "\t";
				$ContentStr .= get_custom_unit_name($vo['schoolID']) . "\t";
				$ContentStr .= $vo['class'] . "\t";
                $ContentStr .= $vo['recTelphone'] . "\t";
                $ContentStr .= $vo['recAddress'] . "\t";
                $ContentStr .= $vo['magazineName'] . "\t";
                $ContentStr .= $vo['sendNum'] . "\t";            
                $ContentStr .= get_employee_name($vo['postPeople']) . "\t";
                $ContentStr .= get_send_goods_type_name($vo['sendGoodsTypeID']) . "\t";
                if ($vo['isPrintSendLabel']) {
                    $ContentStr .= "是\t";
                } else {
                    $ContentStr .= "否\t";
                }
				$ContentStr .= $vo['sendLabelNum'] ."\t\n";

            }
            
            $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
            $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
            
            echo $HeaderStr . $ContentStr;
            
            exit();
        } else {
            $this->error('没有数据!');
        }
    }

	function getFieldExternFieldStr()
	{
		$externFieldStr .= '
			magazine_terrm.year as year,
			magazine_origin.name as magazineOriginName,
			order_base.zipCode as zipCode,
			order_flow_details.memo as memo,
			order_flow_details.termID as termID, 
			order_base.contractID as contractID
			';
		return $externFieldStr;
	}

	public function prePrint()
	{
		$id = $_REQUEST['id'];
		$list = array();
		
		if ($id)
		{
			$PostGoods = D('PostGoods');
            
            $map['post_goods.id'] = array(
                'in',
                $id
            );

			$isPrintSendLabelFlag = 0;
            $voList    = $PostGoods->field('id ,isPrintSendLabel')->where("id in ($id)")->select();
            if ($voList) {
                foreach ($voList as $key => $vo) {
                    if ($vo['isPrintSendLabel']) {
                        $isPrintSendLabelFlag = 1;
                    }
                }
                
                if ($isPrintSendLabelFlag) {
                    $this->error('包含有已打印的发货标签!');
                }
			}
			else
			{
				$this->error('没有数据！');
			}

			$order = $this->getOrderStr();
            $fieldStr = $this->getFieldStr();
			$fieldStr = $fieldStr.','.$this->getFieldExternFieldStr();
			$this->getConditionModel($PostGoods);

            $list   = $PostGoods->where($map)->order("$order")->field($fieldStr)->group('order_base.contractID, order_flow_details.termID')->select();

			foreach ($list as $key => $vo)
			{
				$voList = $PostGoods->query("select sum(order_flow_details.quantity) as quantity from tb_order_flow_details order_flow_details left join tb_order_base order_base on order_base.id = order_flow_details.orderID where (order_flow_details.termID =".$vo['termID']." and order_base.contractID = '".$vo['contractID']."' )");
				$vo['sendNum'] = $voList[0]['quantity'];

				$list[$key] = $vo;
			}

			$printList = array();
			foreach ($list as $key => $vo)
			{
				$printList[$key] = $vo;
			}

			$this->assign('todayDate', date('Y-m-d'));
			$this->assign('list', $list);
			$this->assign('id', $id);
			$this->assign('printList', $printList);
		}
		$this->display();
	}

	public function doPrint()
    {
        $id = $_REQUEST['id'];
        
        if ($id) {
            $PostGoods = D('PostGoods');
            
            $map['id'] = array(
                'in',
                $id
            );

			$isPrintSendLabelFlag = 0;
            $voList    = $PostGoods->field('id ,isPrintSendLabel')->where($map)->select();
            if ($voList) {
                foreach ($voList as $key => $vo) {
                    if ($vo['isPrintSendLabel']) {
                        $isPrintSendLabelFlag = 1;
                    }
                }
                
                if ($isPrintSendLabelFlag) {
					$result['msg'] = '包含有已打印的发货标签!';
					$result['status'] = 0;
                }

				$ParametersSave = D('ParametersSave');

				$sendLabelNum = $ParametersSave->where('type = 3')->getField('value');

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
					$sqlStr = "UPDATE tb_post_goods SET isPrintSendLabel='1',sendLabelNum='".$sendLabelNum."' WHERE id in (".$ids.")";
					$PostGoods->execute($sqlStr);
				}

				$sendLabelNum = $sendLabelNum + 1;
				$ParametersSave->where('type = 3')->setField('value', $sendLabelNum);               
                
                $result['msg'] = '设置成功!';
                $result['status'] = 1;
            } else {
                $result['msg'] = '没有数据！';
				$result['status'] = 0;
            }
            
        }
        else
		{
			$result['msg'] = '没有数据！';
			$result['status'] = 0;
		}
		
		$result['navTabId'] = $_REQUEST['navTabId'];

		echo json_encode($result);
		return ;
    }

    public function cancelPrint()
    {
        $id = $_REQUEST['id'];
        if ($id) {
            $isCheckOutFlag = 1;
            $PostGoods      = D('PostGoods');
            
            $map['id'] = array(
                'in',
                $id
            );
            
            $data['isPrintSendLabel'] = '0';
			$data['sendLabelNum'] = '0';
            $PostGoods->where($map)->data($data)->save();
            
            $this->success('操作成功！');
            
        }
        $this->error('没有数据！');
    }
}