<?php
//后台首页模块
class IndexAction extends CommonAction
{
    // 框架首页
    public function index()
    {
        if (isset($_SESSION[C('USER_AUTH_KEY')])) {
            //显示菜单项
            $menu = array();
            
            //读取数据库模块列表生成菜单项
            $node            = M("Node");
            $Group           = D("Group");
            $id              = $node->getField("id");
            $where['level']  = 2;
            $where['status'] = 1;
            $where['isMenu'] = 1;
            $where['pid']    = $id;
            $list            = $node->where($where)->field('id,name,group_id,title')->order('sort asc')->select();            
            
            $accessList = $_SESSION['_ACCESS_LIST'];
            $groupKey   = array();
            foreach ($list as $key => $module) {
                if (isset($accessList[strtoupper(APP_NAME)][strtoupper($module['name'])]) || $_SESSION['administrator']) {
                    //设置模块访问权限
                    $module['access']              = 1;
                    $menu[$key]                    = $module;
                    $groupKey[$module['group_id']] = $module['group_id'];
                }
            }

            $grouplist = array();
            $groupTemp = array();
			$keyStr;
            foreach ($groupKey as $key => $groupRow) {
                if ($key) {
					$keyStr .= $key . ',';
                }
            }
			if ($keyStr)
			{
				$keyStr = substr($keyStr, 0, strlen($keyStr) - 1);
				$groupTemp       = $Group->where("id in ($keyStr) and status = 1")->order('sort asc')->field('id, name,title')->select();
				foreach ($groupTemp as $groupRow)
				{
					$grouplist[$groupRow['id']] = $groupRow;
				}
			}			

            if (!empty($_GET['tag'])) {
                $this->assign('menuTag', $_GET['tag']);
            }
			
			if ($_SESSION[C('USER_AUTH_KEY')] == 1)											/* admin */
			{
				$departmentName = '系统管理';
			}
			else
			{
				$map["employee.id"] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
				$Employee = M('Employee');
				$departmentName = $Employee->table(array('tb_employee' => 'employee'))->join('tb_department department on department.id = employee.deptID' )->where('employee.id = '.get_employeeid($_SESSION[C('USER_AUTH_KEY')]))->getField('department.name');
			}
            
            $this->assign('menu', $menu);
            $this->assign('grouplist', $grouplist);
			$this->assign('departmentName', $departmentName);
            
            $this->roleMainShow();

			C('SHOW_RUN_TIME', false); // 运行时间显示
			C('SHOW_PAGE_TRACE', false);
			$this->display();
        }
		else
		{
			$this->redirect('Public/login');
		}
       
    }
    
    private function roleMainShow()
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
		$employeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $this->assign('roleEname', $roleEname);
        
        if ($roleEname == "customCenterWorker") {
            $OrderBase = D('OrderBase');
            $List             = array();
            
            $postCodeNameList = $OrderBase->table(array('tb_order_base' => 'order_base'))->
			join('tb_magazine magazine on magazine.postCode = order_base.postCode')->join('tb_employee_newspaper employee_newspaper on employee_newspaper.postCode = order_base.postCode')->Distinct('order_base.postCode')->field('magazine.postCode, magazine.name')->where("employee_newspaper.personID = '".get_employeeid($_SESSION[C('USER_AUTH_KEY')])."'")->order('employee_newspaper.operatingFrequency desc')->select();
            foreach ($postCodeNameList as $key => $vo) {
                $List[$key]['postCodeName'] = $vo['name'];

				$map['postCode']     = $vo['postCode'];
                
                $List[$key]['orderNum'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
				unset($map);
                
				$map['postCode']     = $vo['postCode'];
                $map['isChecked']        = array(
                    'eq',
                    '0'
                );
                $List[$key]['sumUnChecked'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);
                
                $map['postCode']     = $vo['postCode'];
                $map['isChecked']         = array(
                    'eq',
                    '1'
                );
                $List[$key]['sumChecked'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);

                $map['postCode']     = $vo['postCode'];
                $map['isSend']           = array(
                    'eq',
                    '0'
                );
                $List[$key]['sumUnSend'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);
                
                $map['postCode']     = $vo['postCode'];
                $map['isSend']         = array(
                    'eq',
                    '1'
                );
                $List[$key]['sumSend'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);
            }
            
            $this->assign('List', $List);
        } 
		else if ($roleEname == 'logisticsAssigner')					/* 物流部分配员 */
		{
			$OrderBase = D('OrderBase');
            $List             = array();
            
            $postCodeNameList = $OrderBase->table(array('tb_order_base' => 'order_base'))->
			join('tb_magazine magazine on magazine.postCode = order_base.postCode')->join('tb_employee_newspaper employee_newspaper on employee_newspaper.postCode = order_base.postCode')->Distinct('order_base.postCode')->field('magazine.postCode, magazine.name')->where("employee_newspaper.personID = '".get_employeeid($_SESSION[C('USER_AUTH_KEY')])."'")->order('employee_newspaper.operatingFrequency desc')->select();
            foreach ($postCodeNameList as $key => $vo) {
                $List[$key]['postCodeName'] = $vo['name'];               
                
				$map['postCode']     = $vo['postCode'];
                $map['isReceive']        = array(
                    'eq',
                    '1'
                );
                $List[$key]['sumReceive'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);
                
                $map['postCode']     = $vo['postCode'];
                $map['isReceive']         = array(
                    'eq',
                    '0'
                );
                $List[$key]['sumUnReceive'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);

                $map['postCode']     = $vo['postCode'];
                $map['isTrans']           = array(
                    'eq',
                    '1'
                );
                $List[$key]['sumTrans'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);
                
                $map['postCode']     = $vo['postCode'];
                $map['isTrans']         = array(
                    'eq',
                    '0'
                );
                $List[$key]['sumUnTrans'] = $OrderBase->where($map)->Distinct('contractID')->count('contractID'); 
                unset($map);

				$PostGoods = D("PostGoods");
				$map['order_base.postCode']     = $vo['postCode'];
                $map['post_goods.isCheckOut']           = array(
                    'eq',
                    '1'
                );
               
				$List[$key]['sumCheckOut'] = $PostGoods->table(array('tb_post_goods' => 'post_goods'))->
				join('tb_order_flow_details order_flow_details on order_flow_details.id = post_goods.orderFlowID')->
				join('tb_order_base order_base on order_base.id = order_flow_details.orderID')->where($map)->count('post_goods.id'); unset($map);
					
                $PostGoods = D("PostGoods");
				$map['order_base.postCode']     = $vo['postCode'];
                $map['post_goods.isCheckOut']           = array(
                    'eq',
                    '0'
                );
               
				$List[$key]['sumUnCheckOut'] =  $PostGoods->table(array('tb_post_goods' => 'post_goods'))->
				join('tb_order_flow_details order_flow_details on order_flow_details.id = post_goods.orderFlowID')->
				join('tb_order_base order_base on order_base.id = order_flow_details.orderID')->where($map)->count('post_goods.id');  unset($map); unset($map);
            }
            
            $this->assign('List', $List);
		}		
		else if ($roleEname == 'businessManager') {

			import("LibChart");
            $Magazine         = D('Magazine');
            $OrderBase		  = D('OrderBase');
            $List             = array();
            $year             = date('Y');
            $month            = date('m');
            $map              = array();
            
            $MagazineList = $Magazine->getMagazineListByEmId($employeeId);

			$MagazineCount = 0;  
			$monthAdd = $month + 1;
            if ($MagazineList) {
                foreach ($MagazineList as $key => $MagazineVo) {
					/* 单期订单的数量 */
					$orderSingleNum = 0;
					
					/* 多期订单的数量 */
					$orderMonthNum = 0;
					
					/**
					 * 算法：通过查找判断单期中的订单是否在 本月内 统计其数量 某一报刊 某一个人
					 */
					$map['postCode'] = $MagazineVo['postCode'];
					$map['employeeID'] = $employeeId;
					$map['isSingle'] = 1;
					$map['orderYear'] = $year;
					$map['orderTime'] = array('between', strtotime("$year-$month-1 00:00:00").','.strtotime("$year-$monthAdd-1 00:00:00"));
					$orderSingleNum = $OrderBase->where($map)->sum('orderNum');

					unset($map);
					if (empty($orderSingleNum))
					{
						$orderSingleNum = 0;
					}

					$map['orderYear'] = $year;
					$map['orderTime'] = array('between', strtotime("$year-$month-1 00:00:00").','.strtotime("$year-$monthAdd-1 00:00:00"));
					$map['postCode'] = $MagazineVo['postCode'];
					$map['employeeID'] = $employeeId;
					$orderNumList = $OrderBase->where($map)->field('beginOrderDate, endOrderDate, orderNum')->select();
					unset($map);
					
					if ($orderNumList)
					{
						foreach($orderNumList as $orderNumVo)
						{
							$intervalMonths = $orderNumVo['endOrderDate'] - $orderNumVo['beginOrderDate'] + 1;
							$orderMonthNum += $orderNumVo['orderNum'] * $intervalMonths;
						}
					}

                    $MagazineNum = $orderMonthNum + $orderSingleNum; 
					$MagazineName = $MagazineVo['name'];

                    if (empty($MagazineNum) || ($MagazineNum == 0)) {
                        continue;
                    }
					else
					{
						$List[$MagazineCount]['MagazineName'] = $MagazineName;
						$List[$MagazineCount]['MagazineNum'] = $MagazineNum;
						$MagazineCount++;
					}
                }
                
                $chart = new VerticalBarChart(600, 300);
                
                $dataSet    = new XYDataSet();
                $index      = 1;
                $picUrlPath = array();
                
                foreach ($List as $key => $vo) {
                    /* 每行显示10个报刊，多余10个报刊，显示下一个图片 */
                    if (($key % 10 == 0) && ($key != 0)) {
                        $chart->setDataSet($dataSet);
                        
                        $chart->setTitle("当月报刊的销售情况：");
                        $chart->render("./Public/Generated/" . $roleEname . "_" . $EmployeeId . "_" . $index . ".png");
                        $picUrlPath[] = "__PUBLIC__/Generated/" . $roleEname . "_" . $EmployeeId . "_" . $index . ".png";
                        $dataSet      = new XYDataSet();
                        $index++;
                    }
                    $dataSet->addPoint(new Point($vo['MagazineName'], $vo['MagazineNum']));
                }				
                
                $chart->setDataSet($dataSet);
                
                $chart->setTitle("当月报刊的销售情况：");
                $chart->render("./Public/Generated/" . $roleEname . "_" . $EmployeeId . "_" . $index . ".png");
                $picUrlPath[] = "__PUBLIC__/Generated/" . $roleEname . "_" . $EmployeeId . "_" . $index . ".png";
                
                $this->assign('picUrlPath', $picUrlPath);
				

				/* 只保存没提交的订单的数量 */
				unset($map);
				$map['isChecked'] = 2;
				$map['employeeID'] = $employeeId;
				$orderSaveNotCommitNum = $OrderBase->where($map)->sum('orderNum');
				$this->assign('orderSaveNotCommitNum', $orderSaveNotCommitNum);
            }
        }
    }
}