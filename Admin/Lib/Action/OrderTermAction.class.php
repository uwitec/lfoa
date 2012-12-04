<?php
// 期数模块
class OrderTermAction extends CommonAction
{
    public function _before_add()
    {
    }
    
    public function _before_edit()
    {
    }
    
    private function _filter(&$map)
    {
		$map['isReceive'] = 1;
        if ($_REQUEST['isTrans']) {
            $map['isTrans'] = 1;
        } else {
            $map['isTrans'] = 0;
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
            $SearchStr .= "$key/$value";
        }
        
        if ($SearchStr) {
            $SearchStr .= '/';
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
        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
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
                
                $voList = $model->where($map)->order($order)->select();
                
                $FileName = date('Y-m-d') . "订单期数数据.xls";
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
                $HeaderStr .= "付款人" . "\t";
                $HeaderStr .= "是否转换" . "\t\n";
                
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
                    $ContentStr .= $vo['payPerson'] . "\t";
                    
                    if ($vo['isTrans']) {
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
    
    public function convert()
    {
        $name  = 'OrderBase';
        $model = M($name);
        $id    = $_REQUEST[$model->getPk()];
        if ($id) {
            $vo = $model->find($id);
            
            $MagazineTerrm = M('MagazineTerrm');
            
            if ($vo['isSingle']) {
                $TermList = $MagazineTerrm->where('id = ' . $vo['termID'])->select();
            } else {
                $map['year'] = $vo['orderYear'];
                if ($vo['beginOrderDate'] && $vo['endOrderDate']) {
                    $map['month'] = array(
                        'between',
                        array(
                            $vo['beginOrderDate'],
                            $vo['endOrderDate']
                        )
                    );
                } else if ($vo['beginOrderDate']) {
                    $map['month'] = $vo['beginOrderDate'];
                } else if ($vo['endOrderDate']) {
                    $map['month'] = $vo['endOrderDate'];
                } else {
                }
                $map['postCode'] = $vo['postCode'];
                $TermList        = $MagazineTerrm->where($map)->field('id, name, month, year')->order('month desc, name desc')->select();
                
                if ($TermList) {
                    foreach ($TermList as $TermVo) {
                        $TermId[$TermVo['id']] = $TermVo['name'];
                    }
                    
                    asort($TermId);
                    
                    $BeginTerm;
                    $EndTerm;
                    $i = 1;
                    foreach ($TermId as $key => $value) {
                        if ($i == 1) {
                            $BeginTerm = $value;
                        }
                        $i++;
                    }
                    $EndTerm = $value;
                    
                    $vo['beginTerm'] = $BeginTerm;
                    $vo['endTerm']   = $EndTerm;
                    
                }
            }
            
            if ($TermList) {
                foreach ($TermList as $key => $Termvo) {
                    $Termvo['orderNum'] = $vo['orderNum'];
                    $TermList[$key]     = $Termvo;
                }
            }
            
            $this->assign('vo', $vo);
            $this->assign('TermList', $TermList);
            $this->display();
        }
    }


	private function PostGoodsAdd($orderId)
	{
		$BaseOrder                        = D('BaseOrder');
        $map['order_flow_details.orderID']   = array(
		   'in',
			$orderId
		);		
		$fieldStr = 'order_flow_details.id as id, order_flow_details.termID as termID , order_base.recPeople as recPeople, order_base.recTelphone as recTelphone, order_base.sendGoodsSortID as sendGoodsSortID, order_base.sendGoodsTypeID as sendGoodsTypeID,  order_base.recAddress as recAddress, order_base.postCode as postCode, order_flow_details.quantity as quantity, order_flow_details.beginTermID as beginTermID, order_flow_details.endTermID as endTermID, order_base.employeeID as employeeID, order_flow_details.sendSort as sendSort';
		$voList = $BaseOrder->table(array(
                'tb_order_flow_details' => 'order_flow_details'
            ))->join('`tb_order_base` order_base on order_base.id = order_flow_details.orderID')->where($map)->order("$order")->field($fieldStr)->select();
		
		$PostGoods  = D('PostGoods');
		$EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
		foreach ($voList as $vo) {
			$data['orderFlowID']     = $vo['id'];
			$data['sendGoodsSortID'] = $vo['sendGoodsSortID'];
			$data['sendGoodsTypeID'] = $vo['sendGoodsTypeID'];
			$data['sendNum']         = $vo['quantity'];
			$data['recOrderDate']    = time();
			$data['insertPerson']    = $EmployeeId;
			$data['insertTime']      = time();
			
			$PostGoods->add($data);
		}

		return true;
	}

	private function PostGoodsDel($orderId)
	{
		$OrderFlowDetails                 = D('OrderFlowDetails');
		$map['order_flow_details.orderID']     = array(
			'in',
			$orderId
		);
		
		$voList = $OrderFlowDetails->table(array(
			'tb_order_flow_details' => 'order_flow_details'
		))->where($map)->join('tb_post_goods post_goods on order_flow_details.id = post_goods.orderFlowID')->field('order_flow_details.id as orderFlowDetailsId, post_goods.isCheckOut as isCheckOut')->select();
		
		$isCheckOutFlag = 0;
		foreach ($voList as $key => $vo) {
			if ($vo['isCheckOut']) {
				$isCheckOutFlag = 1;
			}
		}
		
		if ($isCheckOutFlag) {
			return false;
		}
		unset($map);
		
		$PostGoods          = D('PostGoods');
		foreach ($voList as $key => $vo)
		{
			$map['orderFlowID'] = array(
                'eq',
                $vo['orderFlowDetailsId']
            );
			$PostGoods->where($map)->delete();
			unset($map);
		}

		return true;
	}
    
    public function doConvert()
    {
        $OrderBase = M('OrderBase');
        $id        = $_REQUEST[$OrderBase->getPk()];
        if ($id) {
            $vo = $OrderBase->find($id);
            
            if ($vo['isTrans']) {
                $this->error('该订单已经转换过了，不能重复转换!');
            }
            
            $MagazineTerrm    = M('MagazineTerrm');
            $OrderFlowDetails = M('OrderFlowDetails');
            
            $Data['orderID']      = $vo['id'];
            $Data['insertPerson'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
            $Data['insertTime'] = time();
            $Data['quantity']   = $vo['orderNum'];
            if ($vo['sendGoodsSortID']) {
                $Data['sendSort'] = get_send_goods_sort_name($vo['sendGoodsSortID']);
            }
            
            if ($vo['isSingle']) {
                $TermList = $MagazineTerrm->where('id = ' . $vo['termID'])->select();
                
                if ($TermList) {
                    $Data['termID'] = $TermList[0]['id'];
                }
                
            } else {
                $map['year'] = $vo['orderYear'];
                if ($vo['beginOrderDate'] && $vo['endOrderDate']) {
                    $map['month'] = array(
                        'between',
                        array(
                            $vo['beginOrderDate'],
                            $vo['endOrderDate']
                        )
                    );
                } else if ($vo['beginOrderDate']) {
                    $map['month'] = $vo['beginOrderDate'];
                } else if ($vo['endOrderDate']) {
                    $map['month'] = $vo['endOrderDate'];
                } else {
                    $this->error('订单中没有开始月份或结束月份，请设置!');
                }
                
                $map['postCode'] = $vo['postCode'];
                $TermList        = $MagazineTerrm->where($map)->field('id, name')->select();
                
                if ($TermList) {
                    foreach ($TermList as $TermVo) {
                        $TermId[$TermVo['id']] = $TermVo['name'];
                    }
                    
                    asort($TermId);
                    
                    $BeginTermId;
                    $EndTermId;
                    $i = 1;
                    foreach ($TermId as $key => $value) {
                        if ($i == 1) {
                            $BeginTermId = $key;
                        }
                        $i++;
                    }
                    
                    $EndTermId           = $key;
                    $Data['beginTermID'] = $BeginTermId;
                    $Data['endTermID']   = $EndTermId;
                }
            }
            
            if ($TermList) {
                foreach ($TermList as $termVo) {
                    $Data['termID'] = $termVo['id'];
                    $OrderFlowDetails->add($Data);
                }
                unset($Data);
                
                $Data['id']          = $id;
                $Data['isTrans']     = '1';
                $Data['orderStatus'] = '待分配';
                $OrderBase->data($Data)->save();
				
				/* 添加发货信息 */
				$this->PostGoodsAdd($vo['id']);
                
                $this->success('转换成功!');
            } else {
                $this->error('没有数据!');
            }
        } else {
            $this->error('转换失败!');
        }
    }
    
    public function cancelConvert()
    {
        $OrderBase = M('OrderBase');
        $id        = $_REQUEST[$OrderBase->getPk()];
        if ($id) {
			 $map['id'] = array(
                'in',
                $id
            );

            $list = $OrderBase->where($map)->select();

			foreach($list as $vo)      
			{
				if ($vo['isTrans']) {						
					if (!$this->PostGoodsDel($vo['id'])) {
						$this->error('取消的订单，有已经分配发货的，请先取消分配！');
					}  
				} else {
					$this->error('所选订单包含还未进行期数转换!');
				}
			}

			$OrderFlowDetails = M('OrderFlowDetails');
			foreach ($list as $vo)
			{				
				$OrderFlowDetails->where('orderID = ' . $id)->delete();
				/* 按订单号删除订单流转信息表 */
							
				$Data['id']          = $id;
				$Data['isTrans']     = '0';
				$Data['orderStatus'] = '待转换';
				$OrderBase->data($Data)->save();
				$OrderBase->where('id = ' . $id)->setField('isTrans', '0');
				/* 设置订单为未转换 */							
			}
			$this->successNoClose('操作成功！');
        }
    }
    
    function update()
    {
    }
    
    public function insert()
    {
    }
    
    public function foreverdelete()
    {
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