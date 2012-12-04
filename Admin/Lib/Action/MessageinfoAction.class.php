<?php
// 城市信息
class MessageinfoAction extends CommonAction
{
    /* 组织index页面的搜索语句 */
    private function indexSearch(&$map, &$searchStr)
    {
        $BeginDateTemp = '';
        $EndDateTemp   = '';
        
        
        if ($BeginDateTemp || $EndDateTemp) {
            if ($BeginDateTemp && $EndDateTemp) {
                $map['sendDate'] = array(
                    'between',
                    "$BeginDateTemp, $EndDateTemp"
                );
            } else if ($BeginDateTemp) {
                $map['sendDate'] = array(
                    'egt',
                    $BeginDateTemp
                );
            } else {
                $map['sendDate'] = array(
                    'elt',
                    $EndDateTemp
                );
            }
        }       
        
    }
    public function index()
    {
        $map       = array();
        $searchStr = '';
        $this->indexSearch($map, $searchStr);

        
        $Messageinfo = D('Messageinfo');
        if (!empty($Messageinfo)) {
            $this->_list($Messageinfo, $map);
        }        
        $roleEname        = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        $this->assign('roleEname', $roleEname);
        $this->display();
    }
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {
        //取得满足条件的记录数
        $EmployeeID       = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $roleEname        = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        if ($roleEname == 'customCenterWorker') {
            $map['sendNameID'] = $EmployeeID ;
            $map['receiveNameID'] = $EmployeeID ;
            $map['isValidate'] = '1' ;            
            $map['_logic'] = 'or';
            //$map['isValidate'] = array(
           //     'eq',
          //      '1'
          //  );
        } else {
            $map['sendNameID'] = $EmployeeID ;
            $map['_string'] = '(isValidate=1 and validateStatus=1  and receiveNameID="'.$EmployeeID.'" ) or (isValidate=0 and receiveNameID="'.$EmployeeID. '" ) ';                       
            $map['_logic'] = 'or';            
        }
        
        $count = $model->table(array(
            'tb_messageinfo' => 'message'
        ))->where($map)->count('message.id');
        
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
            
            $fieldStr = 'id, sendNameID, receiveNameID, sendDate, sendContent, isValidate, validateDate, validateStatus, validateNameID, replayDate, replayStatus, replayContent';
            $orderStr = 'sendDate desc';
            
            $voList = $model->table(array(
                'tb_messageinfo' => 'message'
            ))->where($map)->order($orderStr)->limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
            
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
    public function insert()
    {
    	  $EmployeeID       = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $StockInput = D('Messageinfo');
        $data['sendNameID'] = $EmployeeID;
        $data['receiveNameID'] = $_REQUEST['employeeID'];
		    $data['sendDate']   = date("Y-m-d   H:i:s");   
        $data['sendContent'] = $_REQUEST['sendContent'];	
        if (!isset($_REQUEST['isValidate']) || empty($_REQUEST['isValidate'])) {
            $data['isValidate'] = 0;
        }	
        else{
        $data['isValidate'] = $_REQUEST['isValidate'];		
        }
        
			  $StockInput->add($data);
		
        
        $this->successNoClose('添加成功!');
    }
    public function messageSubmit()
    {
    	//提交
    	$EmployeeID       = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
    	$Messageinfo = D('Messageinfo');
        
        $data['validateStatus']       = '1';
        $data['validateNameID'] = $EmployeeID;
        $data['validateDate'] =  date("Y-m-d   H:i:s");  
        $map['id'] =  $_REQUEST['id'];
        $map['isValidate'] =  '1';      
        
        $model = M('Messageinfo');
        $vo    = $model->getById($_REQUEST['id']);
        if ($vo) { 
        	if($vo['isValidate'] == '0' )
        	{
        		$this->error('不需要审核!');
        	}
        	else if ($vo['validateStatus'] == '1' )
        	{
        		  $this->error('已经审核!');
          }  	
        	else
        	{
        		$Messageinfo->where($map)->data($data)->save();        
            $this->successNoClose('审核完毕!');
            $this->display();
          }
         }	          
    	  
    }
    public function _before_edit()
    {
    	$name  = $this->getActionName();
    	$model = M($name);
        $id    = $_REQUEST[$model->getPk()];
        $vo    = $model->getById($id);
        $this->assign('vo', $vo);

    }
    public function update()
    {
    	//提交
    	$EmployeeID       = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
    	$Messageinfo = D('Messageinfo');
        
        $data['replayStatus']       = '1';
        $data['replayContent'] = $_REQUEST['replayContent'];
        $data['replayDate'] =  date("Y-m-d   H:i:s");  
        $map['id'] =  $_REQUEST['id'];
        $map['receiveNameID'] = $EmployeeID   ;
        
        $model = M('Messageinfo');
        $vo    = $model->getById($_REQUEST['id']);
        if ($vo) { 
        	if($vo['receiveNameID'] <> $EmployeeID )
        	{
        		$this->error('不是接收人!');
        	}
        	else
        	{
             $Messageinfo->where($map)->data($data)->save();
             $this->successNoClose('回复完毕!');
          }
        }
        
        
    	
    }
    public function foreverdelete()
    {
        //删除指定记录
        $EmployeeID       = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
    	  $Messageinfo = D('Messageinfo');
    	  $map['id'] =  $_REQUEST['id'];
        $map['sendNameID'] = $EmployeeID   ;
        $map['replayStatus'] = '0';             
        
        $model = M('Messageinfo');
        $vo    = $model->getById($_REQUEST['id']);
        if ($vo) { 
        	
        	if ($vo['validateStatus'] == '1')
        	{
        	   $this->error('已审核，不能删除!');
        	   
          }
          else if ($vo['replayStatus'] == '1'){
          	//$Messageinfo->where($map)->delete();
          	$this->error('已回复不能删除，不能删除!');          	
          }
          else {
          	if (false !== $Messageinfo->where($map)->delete()) {
                    $this->successNoClose('删除成功！');
                } else {
                    $this->error('删除失败！');
                }         	
          }       
          
        }
        
        //
        
        
    }
}