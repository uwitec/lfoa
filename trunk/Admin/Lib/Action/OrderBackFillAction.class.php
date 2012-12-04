<?php
// 单号回填
class OrderBackFillAction extends CommonAction
{
    /* index页面的搜索列表赋值 */
    private function indexSearchList()
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $roleEname  = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        /* 业务经理报刊信息列表 */
        $Magazine = D("Magazine");
        if ($roleEname == 'admin') {
            $MagazineList = $Magazine->field('postCode, name')->order('name desc')->select();
        } else {
            $MagazineList = $Magazine->getMagazineListByEmId($EmployeeId);
        }
        $this->assign('MagazineList', $MagazineList);
        
        /* 客户信息 */
        $Custom = M('Custom');
        $CustomList = $Custom->field('id, name')->order('name desc')->select();
        $this->assign('CustomList', $CustomList);
        unset($map);
    }
    
    
    /* 组织index页面的搜索语句 */
    private function indexSearch(&$map, &$searchStr)
    {
        $BeginDateTemp = '';
        $EndDateTemp   = '';
        
        if (isset($_REQUEST['postCode']) && !empty($_REQUEST['postCode'])) {
            $map['order_base.postCode'] = array(
                'eq',
                $_REQUEST['postCode']
            );
            $searchStr .= 'postCode/' + $_REQUEST['postCode'] + '/';
        }
        
        if (isset($_REQUEST['month']) && !empty($_REQUEST['month'])) {
            $MagazineTerrm = D('MagazineTerrm');
            if ($_REQUEST['postCode']) {
                $MagazineMap['postCode'] = $_REQUEST['postCode'];
            }
            $MagazineMap['month'] = $_REQUEST['month'];
            $MagazineTermList         = $MagazineTerrm->where($MagazineMap)->field('id')->select();
            foreach ($MagazineTermList as $MagazineVo) {
                $MagazineIds .= $MagazineVo['id'] . ',';
            }
            $MagazineIds                        = substr($MagazineIds, 0, strlen($MagazineIds) - 1);
            $map['order_flow_details.termID'] = array(
                'in',
                $MagazineIds
            );
            $searchStr .= 'month/' . $_REQUEST['month'] . '/';
        }
        
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
        
        if (isset($_REQUEST['customID']) && !empty($_REQUEST['customID'])) {
            $map['order_flow_details.customID'] = array(
                'eq',
                $_REQUEST['customID']
            );
            $searchStr .= 'customID/' + $_REQUEST['customID'] + '/';
        }
        
        if (isset($_REQUEST['beginTime']) && !empty($_REQUEST['beginTime'])) {
            $BeginDateTemp = strtotime($_REQUEST['beginTime']);
            $searchStr .= 'beginTime/' . $_REQUEST['beginTime'] . '/';
        }
        
        if (isset($_REQUEST['endTime']) && !empty($_REQUEST['endTime'])) {
            $EndDateTemp = strtotime($_REQUEST['endTime']);
            $searchStr .= 'endTime/' . $_REQUEST['endTime'] . '/';
        }
        if ($_REQUEST['isSendGoodsID']) {
            $map['post_goods.isSendGoodsID'] = $_REQUEST['isSendGoodsID'];
            $SearchSql .= 'isSendGoodsID/' . $_REQUEST['isSendGoodsID'] . '/';
        }  
		    else
		    {
			      $map['post_goods.isSendGoodsID'] = 0;
            $SearchSql .= 'isSendGoodsID/0/';
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
    }
    
    private function listFilter(&$map)
    {
        /* 获取用户的部门ID */
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $roleEname  = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if ($roleEname == 'admin') {
            /* */
        } else {
            /* 获取用户负责的报刊列表 */
            $EmployeeNewspaper = D('EmployeeNewspaper');
            $MagazineList      = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
            
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
        }
        
        $map['post_goods.isPrintCheckOut'] = 1;
		$map['post_goods.isPrintSendGoods'] = 1;
		$map['post_goods.isPrintSendLabel'] = 1;
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
        if ($_REQUEST['recPeople']) {
            $map['order_base.recPeople'] = $_REQUEST['recPeople'];
            $SearchSql .= 'recPeople/' . $_REQUEST['recPeople'] . '/';
        }
        if ($_REQUEST['postCode']) {
            $map['order_base.postCode'] = array('in', $_REQUEST['postCode']);
            $SearchSql .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
        if ($_REQUEST['postPeople']) {
            $map['post_goods.postPeople'] = $_REQUEST['postPeople'];
            $SearchSql .= 'postPeople/' . $_REQUEST['postPeople'] . '/';
        }
		    if ($_REQUEST['magazineOriginNameID']) {
            $map['order_base.magazineOriginNameID'] = $_REQUEST['magazineOriginNameID'];
            $SearchSql .= 'magazineOriginNameID/' . $_REQUEST['magazineOriginNameID'] . '/';
        }

		    if ($_REQUEST['isBackFill']) {
            $map['post_goods.isBackFill'] = $_REQUEST['isBackFill'];
            $SearchSql .= 'isBackFill/' . $_REQUEST['isBackFill'] . '/';
        }  
		    else{
			      $map['post_goods.isBackFill'] = 0;
            $SearchSql .= 'isBackFill/0/';
		    }         
        if (isset($_REQUEST['sendGoodsID']) && !empty($_REQUEST['sendGoodsID'])) {
            $map['post_goods.sendGoodsID'] = array(
                'eq',
                $_REQUEST['sendGoodsID']
            );
            $searchStr .= 'sendGoodsID/' + $_REQUEST['sendGoodsID'] + '/';
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
        $this->assign('EmployeeId', $EmployeeId);
        $this->display();
        return;
    }
    
    private function getListFieldStr($colList, &$searchStr)
    {
        $fieldStr = '
			post_goods.id as id , 
			order_flow_details.termID as termID , 
			order_base.recPeople as recPeople, 
			order_base.recTelphone as recTelphone, 
			order_base.recAddress as recAddress, 
			order_base.postCode as postCode, 
			order_flow_details.quantity as quantity, 
			order_flow_details.beginTermID as beginTermID, 
			order_flow_details.endTermID as endTermID, 
			post_goods.postPeople as postPeople, 
			post_goods.sendGoodsTypeID as sendGoodsTypeID, 
			post_goods.checkID as checkID, 
			post_goods.sendGoodsDate as sendGoodsDate, 
			post_goods.sendGoodsID as sendGoodsID ';
        
        return $fieldStr;
    }

	private function getConditionModel(&$model)
	{
		$model->table(array('tb_order_flow_details' => 'order_flow_details'))->
			join('tb_order_base order_base on order_base.id = order_flow_details.orderID')->
			join('tb_post_goods post_goods on post_goods.orderFlowID = order_flow_details.id');
	}
    
    private function getListSortStr()
    {
        $order = 'post_goods.isCheckOut asc, post_goods.insertTime desc';
        
        return $order;
    }
    
    protected function _list($model, $map)
    {
        $voList = array();
        
        $fieldStr .= $this->getListFieldStr($colList, $searchStr);
        $orderStr = $this->getListSortStr();
		$this->getConditionModel(&$model);
        
        //取得满足条件的记录数
        $count = $model->where($map)->count('post_goods.id');
        
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
            
			$this->getConditionModel(&$model);
            $voList = $model->where($map)->order($orderStr)->limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
            
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
        $voList = array();
        
        $fieldStr .= $this->getListFieldStr($colList, $searchStr);
        $orderStr = $this->getListSortStr();
		$this->getConditionModel(&$model);
        
        //取得满足条件的记录数
        $count = $model->where($map)->count('post_goods.id');
        
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
            
			$this->getConditionModel(&$model);
            $voList = $model->where($map)->order($orderStr)->limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
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
        
        $OrderBase = D('OrderBase');
        if (!empty($OrderBase)) {
            $voList = $this->exportList($OrderBase, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "单号回填.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "期数" . "\t";
            $HeaderStr .= "收货人" . "\t";
            $HeaderStr .= "电话" . "\t";
            $HeaderStr .= "地址" . "\t";
            $HeaderStr .= "报刊名称" . "\t";
            $HeaderStr .= "份数" . "\t";
            $HeaderStr .= "起期" . "\t";
            $HeaderStr .= "止期" . "\t";
            $HeaderStr .= "发行员" . "\t";
            $HeaderStr .= "发货方式" . "\t";
            $HeaderStr .= "单号" . "\t\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                $ContentStr .= get_magazine_terrm_name($vo['termID']) . "\t";
                $ContentStr .= $vo['recPeople'] . "\t";
                $ContentStr .= $vo['recTelphone'] . "\t";
                $ContentStr .= $vo['recAddress'] . "\t";
                $ContentStr .= get_magazine_name($vo['postCode']) . "\t";
                $ContentStr .= $vo['quantity'] . "\t";
                $ContentStr .= get_magazine_terrm_name($vo['beginTermID']) . "\t";
                $ContentStr .= get_magazine_terrm_name($vo['endTermID']) . "\t";
                $ContentStr .= get_employee_name($vo['postPeople']) . "\t";
                $ContentStr .= get_send_goods_type_name($vo['sendGoodsTypeID']) . "\t";
                $ContentStr .= $vo['sendGoodsID']. "\t\n";
            }
            
            $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
            $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
            
            echo $HeaderStr . $ContentStr;
            
            exit();
        } else {
            $this->error('没有数据!');
        }
    }

	public function backFill()
	{
		$id = $_REQUEST['id'];

		if ($id)
		{
			$map       = array();
			
			$map['post_goods.id'] = array('in', $id);			
			$OrderBase = D('OrderBase');
			if (!empty($OrderBase)) {
				$this->_list($OrderBase, $map);
			}
			$User           = D('User');
      $PostPeopleList = $User->getUserByDutyName('物流部质检员');
       
      $this->assign('PostPeopleList', $PostPeopleList);
			$this->display();
		}
	}

	public function doBackFill()
	{
		$id = $_REQUEST['id'];

		if ($id)
		{
			$EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);

			$map['id'] = array('in', $id);
			$data['sendGoodsID'] = $_REQUEST['sendGoodsID'];
			$data['sendGoodsDate'] = $_REQUEST['sendGoodsDate'];
			$data['checkDate'] = time();
			$data['checkID'] = $_REQUEST['checkID'];
			$data['isBackFill'] = 1;

			$PostGoods = D('PostGoods');
			$PostGoods->where($map)->data($data)->save();

			$this->success('保存成功!');
		}
		else
		{
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