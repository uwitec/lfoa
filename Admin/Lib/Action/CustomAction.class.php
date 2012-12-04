<?php
// 客户信息模块
class CustomAction extends CommonAction
{
    private function beforeIndex()
    {
        $Province     = M("Province");
        $ProvinceList = $Province->field('id, name')->select();
        $this->assign('ProvinceList', $ProvinceList);
        
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        $this->assign('roleEname', $roleEname);
    }
    
    
    private function listFilter(&$map)
    {
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);

        /**
         * 系统管理员可以查看客户信息和管理客户信息
         * 业务经理只能看自己的信息和修改自己的客户信息
         * 其他按照可以看自己的信息和修改自己的客户信息
         */
        if ($roleEname == 'businessManager') {
            $map['custom.employeeID'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        } else {
           
        }
        
        if ($roleEname != 'admin') {
            $map['custom.status'] = array(
                'neq',
                -1
            );
        }
    }
    
    
    /* 不同的角色具有不同的权限，用来判断不同的角色是否具有编辑更新权限 */
    private function canUpdate($CustomId)
    {
        if (empty($CustomId)) {
            return false;
        }
        
        $Custom           = D('Custom');
        $customEmployeeID = $Custom->where('id = ' . $CustomId)->getField('employeeID');
        $EmployeeID       = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
        $roleEname        = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        /**
         * 系统管理员可以查看客户信息和管理客户信息
         * 物流质检员可以查看客户信息，只能修改自己的客户信息
         * 业务经理只能看自己的信息和修改自己的客户信息
         * 其他按照可以看自己的信息和修改自己的客户信息
         */
        if (($_SESSION[C('USER_AUTH_KEY')] == 1)) {
            return true;
        } else {
            if ($EmployeeID == $customEmployeeID) {
                return true;
            }
        }
        
        return false;
    }
    
    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {
        //取得满足条件的记录数
        $count = $model->table(array(
            'tb_custom' => 'custom'
        ))->join('tb_custom_unit  custom_unit on custom_unit.id = custom.UnitID')->join('tb_employee employee on employee.id = custom.employeeID')->join('tb_province province on province.id = custom.provinceID')->where($map)->count('custom.id');
        
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
            $orderStr = 'custom.isOldClient desc, custom.insertTime desc';
            $fieldStr = '
				custom.id as id, 
				employee.employeeName as employeeName, 
				custom.name as customName, 
				custom_unit.name as customUnitName,
				custom.telphone1 as telphone1, 
				custom.telphone2 as telphone2, 
				custom.telphone3 as telphone3, 
				custom.fax as fax, 	
				province.name as provinceName, 
				custom.cityName as cityName, 
				custom.isOldClient as isOldClient,
				custom_unit.name as customUnitName,
				custom.address as address,
				custom.postCode as postCode';
            
            $voList = $model->table(array(
                'tb_custom' => 'custom'
            ))->join('tb_custom_unit  custom_unit on custom_unit.id = custom.UnitID')->join('tb_employee employee on employee.id = custom.employeeID')->join('tb_province province on province.id = custom.provinceID')->where($map)->order($orderStr)->limit($p->firstRow . ',' . $p->listRows)->field($fieldStr)->select();
            
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
        if (isset($_REQUEST['name']) && !empty($_REQUEST['name'])) {
            $map['custom.name'] = array(
                'eq',
                $_REQUEST['name']
            );
            $searchStr .= 'name/' . $_REQUEST['name'] . '/';
        }
        
        if (isset($_REQUEST['telphone1']) && !empty($_REQUEST['telphone1'])) {
            $map['custom.telphone1'] = array(
                'eq',
                $_REQUEST['telphone1']
            );
            $searchStr .= 'telphone1/' . $_REQUEST['telphone1'] . '/';
        }
        
        if (isset($_REQUEST['cityName']) && !empty($_REQUEST['cityName'])) {
            $map['custom.cityName'] = array(
                'eq',
                $_REQUEST['cityName']
            );
            $searchStr .= 'cityName/' . $_REQUEST['cityName'] . '/';
        }
        
        if (isset($_REQUEST['provinceID']) && !empty($_REQUEST['provinceID'])) {
            $map['custom.provinceID'] = array(
                'eq',
                $_REQUEST['provinceID']
            );
            $searchStr .= 'provinceID/' . $_REQUEST['provinceID'] . '/';
        }
    }
    
    
    public function index()
    {
        $map       = array();
        $searchStr = '';
        
        if (method_exists($this, 'beforeIndex')) {
            $this->beforeIndex();
        }
        
        if (method_exists($this, 'searchMap')) {
            $this->searchMap($map, $searchStr);
        }
        
        if (method_exists($this, 'listFilter')) {
            $this->listFilter($map);
        }
        
        $Custom = D('Custom');
        if (!empty($Custom)) {
            $this->_list($Custom, $map);
        }
        
        $this->assign('searchStr', $searchStr);
        
        $this->display();
    }
    
    //赋值公司人员和客户单位
    public function _before_add()
    {
        $CustomUnit = M('CustomUnit');
        $Province   = M('Province');

		$EmployeeId     = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);        
        $CustomUnitList = $CustomUnit->where('employeeID = '.$EmployeeId)->field('id, name')->select();
        $ProvinceList   = $Province->field('id, name')->select();
        
        $this->assign('CustomUnitList', $CustomUnitList);
        $this->assign('EmployeeID', $EmployeeId);
        $this->assign('ProvinceList', $ProvinceList);
        $this->assign('InsertPerson', $EmployeeId);
    }
    
    //赋值公司人员和客户单位
    public function _before_edit()
    {
        $CustomUnit = M("CustomUnit");
        $Province   = M("Province");
        $Custom     = D('Custom');
        
		$EmployeeId     = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);        
        $CustomUnitList = $CustomUnit->where('employeeID = '.$EmployeeId)->field('id, name')->select();
        $ProvinceList   = $Province->field('id, name')->select();
        
        
        $this->assign('CustomUnitList', $CustomUnitList);
        $this->assign('EmployeeID', $EmployeeId);
        $this->assign('ProvinceList', $ProvinceList);
    }

	function _after_insert($customInsertId)
	{
		$Custom = D('Custom');
		$CustomGoods   = D('CustomGoods');
		
		$list = $Custom->where('id = '.$customInsertId)->select();

		$data['customID']   = $list[0]['id'];
        $data['recName']    = $list[0]['name'];
		$data['payName']    = $list[0]['name'];
        $data['phone']      = $list[0]['telphone1'];
		$data['tel']        = $list[0]['telphone2'];
		$data['spareTel']   = $list[0]['telphone3'];
		$data['fax']        = $list[0]['fax'];
        $data['address']    = $list[0]['address'];
        $data['cityName']   = $list[0]['cityName'];
        $data['provinceID'] = $list[0]['provinceID'];
		$data['schoolID']   = $list[0]['UnitID'];
		$data['zipCode']    = $list[0]['postCode'];
                    
        $CustomGoods->data($data)->add();
	}
    
    
    function update()
    {
        if ($this->canUpdate($_REQUEST['id'])) {
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
        } else {
            $this->error('没有权限！');
        }
    }
    
    
    protected function exportList($model, $map, $sortBy = '', $asc = false)
    {
        $voList = array();
		$orderStr = 'custom.name desc';
        
        //取得满足条件的记录数
        $count = $model->table(array( 'tb_custom' => 'custom'))->
				join('tb_custom_unit  custom_unit on custom_unit.id = custom.UnitID')->
				join('tb_employee employee on employee.id = custom.employeeID')->
				join('tb_province province on province.id = custom.provinceID')->
				join('tb_custom_goods custom_goods on custom_goods.customID = custom.id')->
				join('tb_send_goods_sort send_goods_sort on send_goods_sort.id = custom_goods.sendSortID')->
				join('tb_send_order_cyle send_order_cyle on send_order_cyle.id = custom_goods.sendOrderCyleID')->
				order($orderStr)->where($map)->count('custom.id');

        if ($count > 0) {
            $fieldStr = '
				custom.id as id, 
				custom.name as customName, 
				custom_unit.name as customUnitName, 
				custom.telphone1 as telphone1, 
				custom.fax as fax, 
				employee.employeeName as employeeName, 
				province.name as provinceName, 
				custom.cityName as cityName, 
				custom.isOldClient as isOldClient,
				custom.address as address, 
				custom_goods.recName as recName,
				custom_goods.phone as phone,
				province.name as provinceName,
				custom_goods.cityName as recCityName,
				custom_goods.address as recAddress,
				custom.postCode as postCode, 
				send_goods_sort.name as sendGoodsSortName,
				send_order_cyle.name as sendOrderCyleName,
				custom_goods.packetType as packetType,
				custom_goods.schoolID as schoolID,
				custom_goods.isSchool as isSchool,
				custom_goods.className as className,
				custom_goods.memo as memo';
            
            $voList = $model->table(array( 'tb_custom' => 'custom'))->
				join('tb_custom_unit  custom_unit on custom_unit.id = custom.UnitID')->
				join('tb_employee employee on employee.id = custom.employeeID')->
				join('tb_province province on province.id = custom.provinceID')->
				join('tb_custom_goods custom_goods on custom_goods.customID = custom.id')->
				join('tb_send_goods_sort send_goods_sort on send_goods_sort.id = custom_goods.sendSortID')->
				join('tb_send_order_cyle send_order_cyle on send_order_cyle.id = custom_goods.sendOrderCyleID')->
				where($map)->order($orderStr)->field($fieldStr)->select();
        }
        
        return $voList;
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
        
        $Custom = D('Custom');
        if (!empty($Custom)) {
            $voList = $this->exportList($Custom, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "客户信息.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /*first line*/
            $HeaderStr = "姓名" . "\t";
            $HeaderStr .= "单位" . "\t";
            $HeaderStr .= "电话号码" . "\t";
			$HeaderStr .= "传真" . "\t";
            $HeaderStr .= "所属业务员" . "\t";
            $HeaderStr .= "省份" . "\t";
            $HeaderStr .= "城市" . "\t";
            $HeaderStr .= "是否老客户" . "\t";
			$HeaderStr .= "地址" . "\t";
            $HeaderStr .= "收货人" . "\t";
			$HeaderStr .= "收货人手机" . "\t";
			$HeaderStr .= "收货人传真" . "\t";
			$HeaderStr .= "收货人省份" . "\t";
			$HeaderStr .= "收货人城市" . "\t";
			$HeaderStr .= "收货人地址" . "\t";
			$HeaderStr .= "邮编" . "\t";
			$HeaderStr .= "发货方式" . "\t";
			$HeaderStr .= "发货周期" . "\t";
			$HeaderStr .= "包装方式" . "\t";
			$HeaderStr .= "收货人单位" . "\t";
			$HeaderStr .= "是否学校" . "\t";
			$HeaderStr .= "班级" . "\t";
			$HeaderStr .= "备注" . "\t\n";
            
            $ContentStr = '';
            
            /* start of second line */
            foreach ($voList as $vo) {
                $ContentStr .= $vo['customName'] . "\t";
                $ContentStr .= $vo['customUnitName'] . "\t";
                $ContentStr .= $vo['telphone1'] . "\t";
				$ContentStr .= $vo['fax'] . "\t";
                $ContentStr .= $vo['employeeName'] . "\t";
                $ContentStr .= $vo['provinceName'] . "\t";
                $ContentStr .= $vo['cityName'] . "\t";
                if ($vo['isOldCustom']) {
                    $ContentStr .= "是\t";
                } else {
                    $ContentStr .= "否\t";
                }
                $ContentStr .= $vo['address'] . "\t";
				$ContentStr .= $vo['recName'] . "\t";
				$ContentStr .= $vo['phone'] . "\t";
				$ContentStr .= $vo['fax'] . "\t";
				$ContentStr .= $vo['provinceName'] . "\t";
				$ContentStr .= $vo['recCityName'] . "\t";
				$ContentStr .= $vo['recAddress'] . "\t";
				$ContentStr .= $vo['postCode'] . "\t";
				$ContentStr .= $vo['sendGoodsSortName'] . "\t";
				$ContentStr .= $vo['sendOrderCyleName'] . "\t";
				$ContentStr .= $vo['packetType'] . "\t";
				$ContentStr .= get_custom_unit_name($vo['schoolID']) . "\t";
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
    
    public function import()
    {
        if (!isset($_FILES['customExcelData'])) {
            uploadFileErrorRespond('请选择数据！');
        }
        
        if (file_exists($_FILES['customExcelData']['tmp_name'])) {
            $Custom        = M('Custom');
            $CustomGoods   = D('CustomGoods');
            $SendGoodsSort = D('SendGoodsSort');
            $Employee      = D('Employee');
            $Province      = D('Province');
			$CustomUnit    = D('CustomUnit');
            
            import("ORG.Excel.PHPExcel");
            import("ORG.Excel.PHPExcel.IOFactory", THINK_PATH . '/Lib/', '.php');
            
            $fileName = $_FILES['customExcelData']['tmp_name'];
            
            $PHPExcel = new PHPExcel();
            
            /** 默认用excel2007读取excel，若格式不对，则用之前的版本进行读取 */
            $PHPReader = new PHPExcel_Reader_Excel2007();
            
            if (!$PHPReader->canRead($fileName)) {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($fileName)) {
                    uploadFileErrorRespond('Excel格式不支持，请转换后重试！');
                    //uploadFileRespond('Excel format not support ,please try again.');
                    return;
                }
            }
            
            $EmployeeInsertId = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
            ob_clean();
            $PHPExcel     = $PHPReader->load($fileName);
            /* 读取excel文件中的第一个工作表 */
            $currentSheet = $PHPExcel->getSheet(0);
            
            /* 取得最大的列号 */
            $allColumn = $currentSheet->getHighestColumn();
            
            /* 取得一共有多少行 */
            $allRow = $currentSheet->getHighestRow();
            
            if (empty($allRow) || $allRow < 2) {
                uploadFileErrorRespond('请检查数据是否在excel的第一个sheet中。');
            }

			$customRowCount = 0;
			$customUnitRowCount = 0;
			$customGoodsRowCount = 0;
            
            /* 从第二行开始输出，因为excel表中第一行为列名 */
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                /* 从第A列开始输出 */
                $managerName  = trim($currentSheet->getCell('A' . $currentRow)->getValue());
                $address      = trim($currentSheet->getCell('B' . $currentRow)->getValue());
                $customName   = trim($currentSheet->getCell('C' . $currentRow)->getValue());
				$customUnitName = trim($currentSheet->getCell('D' . $currentRow)->getValue());
                $className    = trim($currentSheet->getCell('E' . $currentRow)->getValue());
                $tel          = trim($currentSheet->getCell('F' . $currentRow)->getValue());
                $sendSortName = trim($currentSheet->getCell('G' . $currentRow)->getValue());
                $provinceName = trim($currentSheet->getCell('H' . $currentRow)->getValue());
                $payName      = trim($currentSheet->getCell('I' . $currentRow)->getValue());
				$zipCode	  = trim($currentSheet->getCell('J' . $currentRow)->getValue());

				if (empty($managerName) && empty($customName))
				{
					continue;
				}
                
                $map['name'] = $provinceName;
                $provinceId  = $Province->where($map)->getField('id');
                unset($map);
                
                $map['name'] = $sendSortName;
                $sendSortId  = $SendGoodsSort->where($map)->getField('id');
                unset($map);
                
                $map['employeeName'] = $managerName;
                $employeeId          = $Employee->where($map)->getField('id');
                if (empty($employeeId)) {
                    $employeeId = $EmployeeInsertId;
                }
                unset($map);

				$map['name'] = $customUnitName;
				$customUnitId = $CustomUnit->where($map)->getField('id');
				if (empty($customUnitId))
				{
					$data['name'] = $customUnitName;
					$data['telphone1'] = $tel;
					$data['address'] = $address;
					$data['postCode'] = $zipCode;
					$data['employeeID'] = $employeeId;
 					$customUnitId = $CustomUnit->data($data)->add();

					$customUnitRowCount++;			/* 客户单位信息添加一条 */
				}
				unset($map);
				unset($data);
                
				/* 唯一定位一个客户 */
                $map['employeeID'] = $employeeId;
                $map['name']       = $customName;
				$map['address']    = $address;
				$map['provinceID'] = $provinceId;
                $customId          = $Custom->where($map)->getField('id');
				//echo $Custom->getLastSql();
				//exit();
                if (empty($customId)) {
                    $data['insertPerson'] = $EmployeeInsertId;
                    $data['employeeID']   = $employeeId;
                    $data['name']         = $customName;
                    $data['provinceID']   = $provinceId;
                    $data['address']      = $address;
                    $data['telphone1']    = $tel;
					$data['UnitID']		  = $customUnitId;
                    $data['insertTime']   = time();
					$data['postCode']     = $zipCode;
                    
                    $customId = $Custom->data($data)->add();
					$customRowCount++;					/* 客户信息插入了一条 */                    
                }
                unset($map);
                unset($data);
                
				/* 唯一定位一个发货人 */
                $map['customID']  = $customId;
                $map['schoolID'] = $customUnitId;
				$map['className'] = $className;
				$map['tel'] = $tel;
                $customGoodsId    = $CustomGoods->where($map)->getField('id');
                if (empty($customGoodsId)) {
                    $data['customID']   = $customId;
                    $data['recName']    = $customName;
                    $data['phone']      = $tel;
                    $data['address']    = $address;
                    $data['cityName']   = $address;
                    $data['payName']    = $payName;
                    $data['sendSortID'] = $sendSortId;
                    $data['provinceID'] = $provinceId;
                    $data['className']  = $className;
					$data['schoolID']   = $customUnitId;
					$data['zipCode']    = $zipCode;
                    
                    $customGoodsId = $CustomGoods->data($data)->add();

					$customGoodsRowCount++;
                }
                unset($map);
                unset($data);
                
            }

			$customRowLast = $allRow - $customRowCount;
			$customUnitRowLast = $allRow - $customUnitRowCount;
			$customGoodsRowLast = $allRow - $customGoodsRowCount;
			$msg = '导入成功!<br/>共有'.$allRow.'条数据.<br/>客户信息添加'.$customRowCount.'条;跳过'.$customRowLast.'条.<br/>单户单位信息添加'.$customUnitRowCount.'条;跳过'.$customUnitRowLast.'条.<br/>客户收货信息添加'.$customGoodsRowCount.'条;跳过'.$customGoodsRowLast.'条.';            
            uploadFileSuccessRespond($msg, 'Custom');
            
            return;
        }
        
        uploadFileErrorRespond('出现错误，请重试!');
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
    
    
    public function foreverdelete()
    {
		$id = $_REQUEST['id'];
		$idArray = explode(',', $id);
        if ($this->canUpdate($idArray[0])) {
            //删除指定记录
            $name  = $this->getActionName();
            $model = D($name);
            if (!empty($model)) {
                $pk = $model->getPk();
                if (isset($id)) {
                    $condition = array(
                        $pk => array(
                            'in',
                            explode(',', $id)
                        )
                    );
                    if (false !== $model->where($condition)->delete()) {
						unset($condition);
						/* 删除客户地址信息 */
						$condition = array(
							'customID' => array(
								'in',
								explode(',', $id)
							)
						);
						$CustomGoods = D('CustomGoods');
						$CustomGoods->where($condition)->delete();
                        $this->successNoClose('删除成功！');
                    } else {
                        $this->error('删除失败！');
                    }
                } else {
                    $this->error('非法操作');
                }
            }
            $this->forward();
        } else {
            $this->error('没有权限!');
        }
    }
	
	public function delete()
    {
		$id = $_REQUEST['id'];
        $idArray = explode(',', $id);
        if ($this->canUpdate($idArray[0])) {
			$name  = $this->getActionName();
			$model = M($name);
			if (!empty($model)) {
				$pk = $model->getPk();
				if (isset($id)) {
					$condition = array(
						$pk => array(
							'in',
							explode(',', $id)
						)
					);
					$list      = $model->where($condition)->setField('status', -1);
					if ($list !== false) {
						$this->successNoClose('删除成功！');
					} else {
						$this->error('删除失败！');
					}
				} else {
					$this->error('非法操作');
				}
			}
		}
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