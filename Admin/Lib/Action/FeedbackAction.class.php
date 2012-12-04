<?php
// 订单反馈
class FeedbackAction extends CommonAction {

	public function _before_add()
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
        
        $User           = D('User');
        $PostPeopleList = $User->getUserByDutyName('物流部质检员');
        $this->assign('MagazineList', $MagazineList);
        $this->assign('PostPeopleList', $PostPeopleList);
       /* 客户信息 
        $Custom = M('Custom');
        $CustomList = $Custom->field('id, name')->order('name desc')->select();
        $this->assign('CustomList', $CustomList);*/
        unset($map);
	}

	public function _before_edit()
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
        $User           = D('User');
        $PostPeopleList = $User->getUserByDutyName('物流部质检员');
        $this->assign('MagazineList', $MagazineList);
        $this->assign('PostPeopleList', $PostPeopleList);
        
        /* 客户信息*/
        $Custom = M('Custom');
        $CustomList = $Custom->field('id, name')->order('name desc')->select();
        $this->assign('CustomList', $CustomList); 
        unset($map);
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
            $map['feedback.postCode'] = array(
                'eq',
                $_REQUEST['postCode']
            );
            $searchStr .= 'postCode/' + $_REQUEST['postCode'] + '/';
        }
        
        if (isset($_REQUEST['customID']) && !empty($_REQUEST['customID'])) {
            $map['feedback.customID'] = array(
                'eq',
                $_REQUEST['customID']
            );
            $searchStr .= 'customID/' + $_REQUEST['customID'] + '/';
        }

		if (isset($_REQUEST['year']) && !empty($_REQUEST['year'])) {
            $map['feedback.year'] = array(
                'eq',
                $_REQUEST['year']
            );
            $searchStr .= 'year/' + $_REQUEST['year'] + '/';
        }

		if (isset($_REQUEST['month']) && !empty($_REQUEST['month'])) {
            $map['feedback.month'] = array(
                'eq',
                $_REQUEST['month']
            );
            $searchStr .= 'month/' + $_REQUEST['month'] + '/';
        }
        
        if (isset($_REQUEST['beginTime']) && !empty($_REQUEST['beginTime'])) {
            $BeginDateTemp = strtotime($_REQUEST['beginTime']);
            $searchStr .= 'beginTime/' . $_REQUEST['beginTime'] . '/';
        }
        
        if (isset($_REQUEST['endTime']) && !empty($_REQUEST['endTime'])) {
            $EndDateTemp = strtotime($_REQUEST['endTime']);
            $searchStr .= 'endTime/' . $_REQUEST['endTime'] . '/';
        }
        
        if ($BeginDateTemp || $EndDateTemp) {
            if ($BeginDateTemp && $EndDateTemp) {
                $map['feedback.insertDate'] = array(
                    'between',
                    "$BeginDateTemp, $EndDateTemp"
                );
            } else if ($BeginDateTemp) {
                $map['feedback.insertDate'] = array(
                    'egt',
                    $BeginDateTemp
                );
            } else {
                $map['feedback.insertDate'] = array(
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
					if (!$map['feedback.postCode']) {
						$map['feedback.postCode'] = array(
							'in',
							$MagazinePostCodes
						);
					}
				}
            }
        }
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
        
        $Feedback = D('Feedback');
        if (!empty($Feedback)) {
            $this->_list($Feedback, $map);
        }
        
        $this->assign('searchStr', $searchStr);
        
        $this->display();
    }


	private function getListFieldStr($colList, &$searchStr)
    {
        $fieldStr = '
			feedback.id as id , 
			custom.name as customName , 
			feedback.contactPerson as contactPerson, 
			feedback.tel as tel, 
			magazine.name as magazineName, 
			feedback.year as year, 
			feedback.month as month, 
			feedback.term as term, 
			feedback.content as content, 
			feedback.result as result, 
			feedback.resultPersonID as resultPersonID, 
			feedback.reslutTime as reslutTime, 
			feedback.employeeID as employeeID,
			feedback.insertDate as insertDate';
        
        return $fieldStr;
    }

	private function getConditionModel(&$model)
	{
		$model->table(array('tb_feedback' => 'feedback'))->
			join('tb_custom custom on custom.id = feedback.customID')->
			join('tb_magazine magazine on magazine.postCode = feedback.postCode');
	}
    
    private function getListSortStr()
    {
        $order = 'feedback.year desc, feedback.month, feedback.insertDate desc';
        
        return $order;
    }

	protected function _list($model, $map)
    {
        $voList = array();
        
        $fieldStr .= $this->getListFieldStr($colList, $searchStr);
        $orderStr = $this->getListSortStr();
		$this->getConditionModel(&$model);
        
        //取得满足条件的记录数
        $count = $model->where($map)->count('feedback.id');
        
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
        $count = $model->where($map)->count('feedback.id');
        
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
        
        $Feedback = D('Feedback');
        if (!empty($Feedback)) {
            $voList = $this->exportList($Feedback, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "客户反馈信息.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "客户姓名" . "\t";
            $HeaderStr .= "联系人" . "\t";
            $HeaderStr .= "电话" . "\t";
            $HeaderStr .= "报刊名称" . "\t";
            $HeaderStr .= "年度" . "\t";
            $HeaderStr .= "月份" . "\t";
            $HeaderStr .= "期数" . "\t";
            $HeaderStr .= "反馈内容" . "\t";
            $HeaderStr .= "处理结果" . "\t";
            $HeaderStr .= "处理人" . "\t";
            $HeaderStr .= "处理时间" . "\t";
			$HeaderStr .= "录入人" . "\t";
			$HeaderStr .= "录入时间" . "\t\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                $ContentStr .= $vo['customName'] . "\t";
                $ContentStr .= $vo['contactPerson'] . "\t";
                $ContentStr .= $vo['tel'] . "\t";
                $ContentStr .= $vo['magazineName'] . "\t";
                $ContentStr .= $vo['year'] . "\t";
                $ContentStr .= $vo['month'] . "\t";
                $ContentStr .= $vo['term'] . "\t";
                $ContentStr .= $vo['content'] . "\t";
                $ContentStr .= $vo['result'] . "\t";
                $ContentStr .= get_employee_name($vo['resultPersonID']) . "\t";
                $ContentStr .= date('Y-m-d',$vo['resultTime'])."\t";
				$ContentStr .= get_employee_name($vo['employeeID']) . "\t";
				$ContentStr .= date('Y-m-d',$vo['insertDate']) . "\t\n";
            }
            
            $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
            $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
            
            echo $HeaderStr . $ContentStr;
            
            exit();
        } else {
            $this->error('没有数据!');
        }
    }

	function update()
    {
		//if (isset($_POST['result']) && !empty($_POST['result']))
		{
	//		$_POST['resultPersonID  '] = $resultPersonID;
			$_POST['reslutTime'] = strtotime($reslutTime);
		}

        $name  = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 更新数据
        $list = $model->save();
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

	function insert()
    {
		$EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);

		$_POST['employeeID'] = $EmployeeId;
		$_POST['insertDate'] = time();

	//	if (isset($_POST['result']) && !empty($_POST['result']))
		{
			//$_POST['resultPersonID'] = $resultPersonID;
			$_POST['reslutTime'] = strtotime($reslutTime);
		}

        $name  = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        //保存当前数据对象
        $list = $model->add();
        if ($list !== false) { //保存成功
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

	function detail()
    {
        $name  = $this->getActionName();
        $model = M($name);
        $id    = $_REQUEST[$model->getPk()];
        $vo    = $model->getById($id);
        $this->assign('vo', $vo);
        $this->display();
    }
}