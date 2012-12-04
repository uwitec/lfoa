<?php
// 客户收货信息模块
class CustomGoodsAction extends CommonAction
{
    private function beforeIndex()
    {
        $this->assign('customId', $_REQUEST['customId']);
    }
    
    
    private function listFilter(&$map)
    {
        $map['custom_goods.customID'] = $_REQUEST['customId'];
    }
    
	private function getFieldStr()
	{
		$fieldStr = '
				custom_goods.id as id, 
				custom.name as customName,
				custom_goods.recName as recName, 
				custom_goods.phone as phone,
				custom_goods.tel as tel, 
				custom_goods.spareTel as spareTel,
				custom_goods.fax as fax, 
				custom_goods.address as address, 
				custom_goods.zipCode as zipCode, 
				custom_goods.payName as payName, 
				province.name as provinceName,
				custom_goods.cityName as cityName, 
				province.name as provinceName, 
				send_goods_type.name as sendTypeName,
				send_goods_sort.name as sendSortName,
				send_order_cyle.name as sendCyleName,
				custom_unit.name as customUnitName,
				custom_goods.isSchool as isSchool,
				custom_goods.className as className,
				custom_goods.memo as memo';
		return $fieldStr;
	}

	private function getOrderStr()
	{
		$orderStr = 'custom_goods.id desc';

		return $orderStr;
	}

	private function getConditionModel(&$model)
	{
		$model->table(array('tb_custom_goods' => 'custom_goods'))->
			join('tb_custom custom on custom.id = custom_goods.customID')->
			join('tb_province province on province.id = custom_goods.provinceID')->
			join('tb_custom_unit custom_unit on custom_unit.id = custom_goods.schoolID')->
			join('tb_send_goods_type send_goods_type on send_goods_type.id = custom_goods.sendTypeID')->
			join('tb_send_goods_sort send_goods_sort on send_goods_sort.id = custom_goods.sendSortID')->
			join('tb_send_order_cyle send_order_cyle on send_order_cyle.id = custom_goods.sendOrderCyleID');
	}


    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {
		$this->getConditionModel($model);
        //取得满足条件的记录数
        $count = $model->where($map)->count('custom_goods.id');
        
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '';
            }
            $p        = new Page($count, $listRows);
            //分页查询数据
            $orderStr = $this->getOrderStr();
            $fieldStr = $this->getFieldStr();
            
			$this->getConditionModel($model);
            $voList = $model->where($map)->order($orderStr)->
				limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
            
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
    
    private function searchMap(&$map, &$searchStr)
    {
        /* void */
    }
    
    
    public function index()
    {
        $map       = array();
        $searchStr = '';
        
        $CustomID = $_REQUEST['customId'];
        
        if ($CustomID) {
            if (method_exists($this, 'beforeIndex')) {
                $this->beforeIndex();
            }
            
            if (method_exists($this, 'searchMap')) {
                $this->searchMap($map, $searchStr);
            }
            
            if (method_exists($this, 'listFilter')) {
                $this->listFilter($map);
            }
            $CustomGoods = D('CustomGoods');
            if (!empty($CustomGoods)) {
                $this->_list($CustomGoods, $map);
            }
        }

		$this->assign('customId', $CustomID);
        
        $this->display();
    }
     
    
    //赋值公司人员和客户单位
    public function _before_add()
    {
        $SendGoodsSort = D('SendGoodsSort');
		$SendGoodsType  = M('SendGoodsType');
        $CustomUnit		= M('CustomUnit');
        $Province      = M('Province');
        $SendOrderCyle = M('SendOrderCyle');

		$EmployeeId     = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);  
		$CustomUnitList = $CustomUnit->where('employeeID = '.$EmployeeId)->field('id, name')->select();
        
		$SendGoodsSortList  = $SendGoodsSort->field('id, name')->order("name desc")->select();
        $SendGoodsTypeList  = $SendGoodsType->field('id, name')->order("name desc")->select();
        $ProvinceList      = $Province->field('id, name')->select();
        $SendOrderCyleList = $SendOrderCyle->field('id, name')->select();
        
        $this->assign('SendGoodsSortList', $SendGoodsSortList);
		$this->assign('SendGoodsTypeList', $SendGoodsTypeList);
        $this->assign('SchoolList', $CustomUnitList);
        $this->assign('ProvinceList', $ProvinceList);
        $this->assign('SendOrderCyleList', $SendOrderCyleList);
        $this->assign('customId', $_REQUEST['customId']);
    }
    
    //赋值公司人员和客户单位
    public function _before_edit()
    {
        $SendGoodsSort = D('SendGoodsSort');
		$SendGoodsType  = M('SendGoodsType');
        $CustomUnit    = M('CustomUnit');
        $Province      = M('Province');
        $SendOrderCyle = M('SendOrderCyle');

		$EmployeeId     = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);  
		$CustomUnitList = $CustomUnit->where('employeeID = '.$EmployeeId)->field('id, name')->select();
        
        $SendGoodsSortList  = $SendGoodsSort->field('id, name')->order("name desc")->select();
        $SendGoodsTypeList  = $SendGoodsType->field('id, name')->order("name desc")->select();
        $ProvinceList      = $Province->field('id, name')->select();
        $SendOrderCyleList = $SendOrderCyle->field('id, name')->select();
        
        $this->assign('SendGoodsSortList', $SendGoodsSortList);
		$this->assign('SendGoodsTypeList', $SendGoodsTypeList);
        $this->assign('SchoolList', $CustomUnitList);
        $this->assign('ProvinceList', $ProvinceList);
        $this->assign('SendOrderCyleList', $SendOrderCyleList);
		$this->assign('customId', $_REQUEST['customId']);
    }
    
    protected function exportList($model, $map, $sortBy = '', $asc = false)
    {
        $voList = array();
        
		$this->getConditionModel($model);
        //取得满足条件的记录数
        $count = $model->where($map)->count('custom_goods.id');
        
        if ($count > 0) {
            import("ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '';
            }
            $p        = new Page($count, $listRows);
            //分页查询数据
            $orderStr = $this->getOrderStr();
            $fieldStr = $this->getFieldStr();
            
			$this->getConditionModel($model);
            $voList = $model->where($map)->order($orderStr)->field($fieldStr)->select();
        }
        
        return $voList;
    }

	function insert()
    {
        //B('FilterString');
        $name  = $this->getActionName();
        $model = D($name);

        $className = $_POST['className'];
		$classNameArr = explode('#', $className);
		foreach ($classNameArr as $className)
		{
			$_POST['className'] = $className;
			if (false === $model->create()) {
				$this->error($model->getError());
			}
			
			$list = $model->add();
		}
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


	function update()
    {
        //B('FilterString');
        $name  = $this->getActionName();
        $model = D($name);
		$firstUpdate = false;

		$className = $_POST['className'];
		$classNameArr = explode('#', $className);
		foreach ($classNameArr as $className)
		{
			$_POST['className'] = $className;
			if (false === $model->create()) {
				$this->error($model->getError());
			}
			if ($firstUpdate == false)
			{
				$firstUpdate = true;
				unset($_POST['id']);
				$list = $model->save();
			}
			else
			{
				$list = $model->add();
			}
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
    
    public function export()
    {
        $map       = array();
        $searchStr = '';
        $voList    = array();
        
        if (method_exists($this, 'searchMap')) {
            $this->searchMap($map, $searchStr);
        }
        
        if (method_exists($this, 'listFilter')) {
            $this->listFilter($map);
        }
        
        $CustomGoods = D('CustomGoods');
        if (!empty($CustomGoods)) {
            $voList = $this->exportList($CustomGoods, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "客户发货地址信息.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "客户名字" . "\t";
            $HeaderStr .= "收货人" . "\t";
			$HeaderStr .= "手机" . "\t";
            $HeaderStr .= "电话" . "\t";
            $HeaderStr .= "地址" . "\t";
			$HeaderStr .= "邮编" . "\t";
            $HeaderStr .= "付款人" . "\t";
			$HeaderStr .= "省份" . "\t";
            $HeaderStr .= "所属城市" . "\t";
             $HeaderStr .= "发货类型" . "\t";
            $HeaderStr .= "发货方式" . "\t";
			$HeaderStr .= "发货周期" . "\t";
			$HeaderStr .= "单位" . "\t";
			$HeaderStr .= "是否学校" . "\t";
			$HeaderStr .= "班级" . "\t";
			$HeaderStr .= "备注" . "\t\n";
            
            $ContentStr = '';
            
            /*start of second line*/
            foreach ($voList as $vo) {
                $ContentStr .= $vo['customName'] . "\t";
                $ContentStr .= $vo['recName'] . "\t";
				$ContentStr .= $vo['phone'] . "\t";
                $ContentStr .= $vo['tel'] . "\t";
                $ContentStr .= $vo['address'] . "\t";
				$ContentStr .= $vo['zipCode'] . "\t";
                $ContentStr .= $vo['payName'] . "\t";
				$ContentStr .= $vo['provinceName'] . "\t";
                $ContentStr .= $vo['cityName'] . "\t";
                $ContentStr .= $vo['sendTypeName'] . "\t";
                $ContentStr .= $vo['sendSortName'] . "\t";
				$ContentStr .= $vo['sendCyleName'] . "\t";
				$ContentStr .= $vo['customUnitName'] . "\t";
                if ($vo['isSchool']) {
                    $ContentStr .= "是\t";
                } else {
                    $ContentStr .= "否\t";
                }
				$ContentStr .= $vo['className'] . "\t";
				$ContentStr .= $vo['memo'] . "\t\n";
            }
            
            $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
            $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
            
            echo $HeaderStr . $ContentStr;
            
            exit();
        } else {
            $this->error('没有数据!');
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
    
    public function getSelect()
    {
        $type = $_REQUEST['type'];
        
        switch ($type) {
            case '1': {
                $provinceID = $_REQUEST['provinceID'];
                $City       = M("City");
                if ($provinceID) {
                    $CityList = $City->where('provinceID = ' . $provinceID)->field('id, name')->select();
                } else {
                    break;
                }
                $select[] = array(
                    'id' => '',
                    'title' => '--请选择--'
                );
                foreach ($CityList as $CityVo) {
                    $select[] = array(
                        'id' => $CityVo['id'],
                        'title' => $CityVo['name']
                    );
                }
                
                echo json_encode($select);
                return;
            }
                break;

			case 2:
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