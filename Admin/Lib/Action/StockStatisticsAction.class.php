<?php
// 库存统计
class StockStatisticsAction extends CommonAction
{
    public function index()
    {
        $SearchSql     = '';       
        $Magazine          = D('Magazine');
        
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        
        $MagazineList      = $Magazine->field('postCode, name')->order("name desc")->select();
        $this->assign('MagazineList', $MagazineList);
	     
        /*if ($_REQUEST['postCode']) {
            $map['magazine_terrm.postCode'] = $_REQUEST['postCode'];
            $SearchSql .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }*/
        if (isset($_REQUEST['postCode']) && $_REQUEST['postCode'] != "")
		{
			$map['magazine_terrm.postCode'] = array('in', $_REQUEST['postCode']);
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
            $map['magazine_terrm.id'] = array(
                'in',
                $MagazineIds
            );
            $SearchSql .= 'month/' . $_REQUEST['month'] . '/';
        }
        
        $this->assign('SearchSql', $SearchSql);
        /*
        if ($MagazineList) {
            foreach ($MagazineList as $vo) {
                 $MagazinePostCodes .= $vo['postCode'] . ',';
            }
            $MagazinePostCodes = substr($MagazinePostCodes, 0, strlen($MagazinePostCodes) - 1);
            
						if ($MagazinePostCodes)
						{
								if (!$map['postCode']) {
										$map['postCode'] = array(
										'in',
										$MagazinePostCodes);
								}
						}
        }*/
        
        $model                               = D('MagazineTerrm');
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        
        $this->display();
        return;
    }

	private function getFieldStr()
	{
		$fieldStr = '
			magazine_terrm.id as termID , 
			magazine_terrm.name as termName, 
			magazine_terrm.postCode as postCode,
			magazine_terrm.longname as termLongName, 
			magazine_terrm.month as termMonth,
			(select sum(stockNum) from tb_stock_input j where j.postCode=magazine_terrm.postCode and j.termID=magazine_terrm.id) as inputNum,
			(select sum(outputNum) from tb_stock_output t where t.postCode=magazine_terrm.postCode and t.termID=magazine_terrm.id) as outputNum,
			(select sum(stockNum) from tb_stock_input j where j.postCode=magazine_terrm.postCode and j.termID=magazine_terrm.id) - (select sum(outputNum) from tb_stock_output t where t.postCode=magazine_terrm.postCode and t.termID=magazine_terrm.id) as nowStockNum,
			magazine.name as magazineName';
			//magazine.name as magazineName';
      //(select sum(stockNum) from tb_stock_input j where j.postCode=magazine_terrm.postCode and j.termID=magazine_terrm.id) as inputNum,
			//(select sum(outputNum) from tb_stock_output t where t.postCode=magazine_terrm.postCode and t.termID=magazine_terrm.id) as outputNum,
		return $fieldStr;
	}

	private function getOrderStr()
	{
		$order = 'magazine_terrm.postCode, magazine_terrm.year asc, magazine_terrm.month asc, magazine_terrm.name asc';
    //$order = 'magazine_terrm.postCode desc';
		return $order;
	}

	private function getConditionModel(&$model)
	{
		$EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
		//$model->table(array('tb_magazine' => 'magazine'))->
		//join('tb_magazine_terrm magazine_terrm on magazine_terrm.postCode =  magazine.postCode')->
		//join('tb_employee_newspaper employee_newspaper on magazine.postCode=employee_newspaper.postCode and employee_newspaper.personID="'.$EmployeeId.'"');
		$model->table(array('tb_employee_newspaper' => 'employee_newspaper'))->		
		join('tb_magazine magazine on magazine.postCode=employee_newspaper.postCode and employee_newspaper.personID="'.$EmployeeId.'"')->
		join('tb_magazine_terrm magazine_terrm on magazine_terrm.postCode =  magazine.postCode');
		
		//->join('tb_magazine magazine on magazine.postCode =  magazine_terrm.postCode')
	//join('tb_stock_input stock_input on stock_input.termID = magazine_terrm.id')->
	//join('tb_stock_output stock_output on stock_output.termID = magazine_terrm.id')	
	}
    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {       
        $this->getConditionModel($model);
		$fieldStr = $this->getFieldStr();
        //取得满足条件的记录数
        //$count = count($model->where($map)->field($fieldStr)->group('magazine_terrm.postCode, magazine_terrm.year, magazine_terrm.month')->select());
        $count = count($model->where($map)->field($fieldStr)->group('magazine_terrm.postCode, magazine_terrm.year, magazine_terrm.month, magazine_terrm.name')->select());
        //$count = count($model->where($map)->field($fieldStr)->group('magazine_terrm.postCode')->select());
        
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
				    limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->group('magazine_terrm.postCode, magazine_terrm.year, magazine_terrm.month, magazine_terrm.name')->select();
            
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
        $count = count($model->where($map)->group('magazine_terrm.contractID, order_flow_details.termID')->select());
        
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

            $voList   = $model->where($map)->order("$order")->
				limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->group('magazine_terrm.contractID, order_flow_details.termID')->select();
        }
        
        return $voList;
    }
    
    protected function searchMap()
    {
      
        $EmployeeNewspaper = D('EmployeeNewspaper');        
        $EmployeeId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);        
        $MagazineList = $EmployeeNewspaper->getEmployeeNespapers($EmployeeId);
        
        if ($_REQUEST['postCode']) {
            $map['magazine_terrm.postCode'] = $_REQUEST['postCode'];
            $SearchSql .= 'postCode/' . $_REQUEST['postCode'] . '/';
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
					$map['magazine_terrm.postCode'] = array(
						'in',
						$MagazinePostCodes
					);
				}
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
            $FileName = date('Y-m-d') . "出库单打印数据.xls";
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
            $HeaderStr .= "是否打印出库单" . "\t";
			$HeaderStr .= "出库单号" . "\t\n";
            
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
                if ($vo['isPrintCheckOut']) {
                    $ContentStr .= "是\t";
                } else {
                    $ContentStr .= "否\t";
                }
				$ContentStr .= $vo['checkOutNum'] ."\t\n";

            }
            
            $HeaderStr  = iconv("UTF-8", "GBK", $HeaderStr);
            $ContentStr = iconv("UTF-8", "GBK", $ContentStr);
            
            echo $HeaderStr . $ContentStr;
            
            exit();
        } else {
            $this->error('没有数据!');
        }
    }


}