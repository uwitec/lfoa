<?php
// 报刊期数信息
class MagazineTerrmAction extends CommonAction
{
    /* index页面的搜索列表赋值 */
    private function indexSearchList()
    {
        $_REQUEST['_order'] = 'name';
        $Magazine           = M("Magazine");
        $MagazineList       = $Magazine->field('postCode, name')->order("name desc")->select();
        
        $this->assign('MagazineList', $MagazineList);
    }
    
    
    /* 组织index页面的搜索语句 */
    private function indexSearch(&$map, &$searchStr)
    {
        if (isset($_REQUEST['postCode']) && !empty($_REQUEST['postCode'])) {
            $map['postCode'] = array(
                'eq',
                $_REQUEST['postCode']
            );
            $searchStr .= 'postCode/' . $_REQUEST['postCode'] . '/';
        }
        
        if (isset($_REQUEST['month']) && !empty($_REQUEST['month'])) {
            $map['month'] = array(
                'eq',
                $_REQUEST['month']
            );
            $searchStr .= 'month/' . $_REQUEST['month'] . '/';
        }
        
        if (isset($_REQUEST['year']) && !empty($_REQUEST['year'])) {
            $map['year'] = array(
                'eq',
                $_REQUEST['year']
            );
            $searchStr .= 'month/' . $_REQUEST['year'] . '/';
        }
    }
    
    
    protected function _list($model, $map, $sortBy = '', $asc = false)
    {
        $order = 'postCode desc, name asc';
        
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
    
    
    private function listFilter(&$map)
    {
        /* void */
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
        
        $MagazineTerrm = D('MagazineTerrm');
        if (!empty($MagazineTerrm)) {
            $this->_list($MagazineTerrm, $map);
        }
        
        $this->assign('searchStr', $searchStr);
        $this->display();
    }
    
    
    private function exportList($model, $map, $sortBy = '', $asc = false)
    {
        $voList = array();
        
         $order = 'postCode desc, name asc';
        
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
            
            $voList = $model->where($map)->order("$order")->select();
            
            return $voList;
        }
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
        
        $MagazineTerrm = D('MagazineTerrm');
        if (!empty($MagazineTerrm)) {
            $voList = $this->exportList($MagazineTerrm, $map);
        }
        
        if ($voList) {
            $FileName = date('Y-m-d') . "报刊期数信息.xls";
            $FileName = iconv("UTF-8", "GBK", $FileName);
            
            header("Content-Type: application/vnd.ms-execl");
            header("Content-Disposition: attachment; filename= $FileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            /* first line */
            $HeaderStr = "期数名称" . "\t";
            $HeaderStr .= "期数全称" . "\t";
            $HeaderStr .= "期数序号" . "\t";
            $HeaderStr .= "所属期刊" . "\t";
            $HeaderStr .= "月份" . "\t";
            $HeaderStr .= "年度" . "\t\n";
            
            $ContentStr = '';
            
            /* start of second line */
            foreach ($voList as $vo) {
                $ContentStr .= $vo['name'] . "\t";
                $ContentStr .= $vo['longname'] . "\t";
                $ContentStr .= $vo['termList'] . "\t";
                $ContentStr .= get_magazine_name($vo['postCode']) . "\t";
                $ContentStr .= $vo['month'] . "\t";
                $ContentStr .= $vo['year'] . "\t\n";
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
        if (!isset($_FILES['termExcelData'])) {
            uploadFileRespond('请选择数据！');
        }
        
        if (file_exists($_FILES['termExcelData']['tmp_name'])) {
            $MagazineTerrm = M('MagazineTerrm');
            $Magazine      = D('Magazine');
            
            import("ORG.Excel.PHPExcel");
            import("ORG.Excel.PHPExcel.IOFactory", THINK_PATH . '/Lib/', '.php');
            
            $fileName = $_FILES['termExcelData']['tmp_name'];
            
            $PHPExcel = new PHPExcel();
            
            /* 默认用excel2007读取excel，若格式不对，则用之前的版本进行读取 */
            $PHPReader = new PHPExcel_Reader_Excel2007();
            
            if (!$PHPReader->canRead($fileName)) {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($fileName)) {
                    uploadFileRespond('Excel格式不支持，请转换后重试！');
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
                uploadFileRespond('请检查数据是否在excel的第一个sheet中。');
            }
            
            /* 从第二行开始输出，因为excel表中第一行为列名*/
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                /**从第A列开始输出*/
                $termName     = trim($currentSheet->getCell('A' . $currentRow)->getValue());
                $termLongName = trim($currentSheet->getCell('B' . $currentRow)->getValue());
                $termList     = trim($currentSheet->getCell('C' . $currentRow)->getValue());
                $magazineName = trim($currentSheet->getCell('D' . $currentRow)->getValue());
                $month        = trim($currentSheet->getCell('E' . $currentRow)->getValue());
                $year         = trim($currentSheet->getCell('F' . $currentRow)->getValue());
                
                $map['name'] = $magazineName;
                $postCode    = $Magazine->where($map)->getField('postCode');
                unset($map);
                
                
                $map['postCode'] = $postCode;
                $map['year']     = $year;
                $map['month']    = $month;
                $map['name']     = $termName;
                $termId          = $MagazineTerrm->where($map)->getField('id');
                if (empty($termId)) {
                    $data['name']         = $termName;
                    $data['termList']     = $termList;
                    $data['longname']     = $termLongName;
                    $data['postCode']     = $postCode;
                    $data['month']        = $month;
                    $data['year']         = $year;
                    $data['insertTime']   = time();
                    $data['insertPerson'] = $EmployeeInsertId;
                    
                    $termId = $MagazineTerrm->data($data)->add();
                }
                unset($map);
                unset($data);
                
            }
            
            uploadFileRespond('导入成功!');
            
            return;
        }
        
        uploadFileRespond('出现错误，请重试!');
    }
    
    
    public function _before_add()
    {
        $Magazine     = M("Magazine");
        $MagazineList = $Magazine->field('postCode, name')->order("name desc")->select();
        
        $this->assign('MagazineList', $MagazineList);
        $this->assign('InsertPerson', get_employeeid($_SESSION[C('USER_AUTH_KEY')]));
        
        $this->assign('currentYear', date('Y'));
    }
    
    
    public function _before_edit()
    {
        $Magazine     = M("Magazine");
        $MagazineList = $Magazine->field('postCode, name')->order("name desc")->select();
        
        $this->assign('MagazineList', $MagazineList);
        $this->assign('InsertPerson', get_employeeid($_SESSION[C('USER_AUTH_KEY')]));
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
    
    public function monthCopy()
    {
        $Magazine     = M("Magazine");
        $MagazineList = $Magazine->field('postCode, name')->order("name desc")->select();
        
        $this->assign('MagazineList', $MagazineList);
        $this->assign('currentYear', date('Y'));
        $this->display();
    }
    
    public function yearCopy()
    {
        $Magazine     = M("Magazine");
        $MagazineList = $Magazine->field('postCode, name')->order("name desc")->select();
        
        $this->assign('MagazineList', $MagazineList);
        $this->assign('currentYear', date('Y'));
        $this->display();
    }
    
    public function saveMonthCopy()
    {
        $result;
        
        if (!$_REQUEST['sourcePostCode']) {
            $this->error('请选择来源报刊!');
        }
        
        if (!$_REQUEST['destPostCode']) {
            $this->error('请选择目的报刊');
        }
        
        if (!$_REQUEST['month']) {
            $this->error('请选择月份!');
        }
        
        if (!$_REQUEST['year']) {
            $this->error('请选择年!');
        }
        
        $MagazineTerrm = D('MagazineTerrm');
        
        $result = $MagazineTerrm->copyMagazineTerrmByMonth($_REQUEST['sourcePostCode'], $_REQUEST['destPostCode'], $_REQUEST['year'], $_REQUEST['month']);
        
        if ($result == -1) {
            $this->error('输入参数错误，请重试!');
        } else {
            $this->success('保存成功！');
        }
        
    }
    
    public function saveYearCopy()
    {
        $result;
        
        if (!$_REQUEST['sourcePostCode']) {
            $this->error('请选择来源报刊!');
        }
        
        if (!$_REQUEST['destPostCode']) {
            $this->error('请选择目的报刊');
        }
        
        if (!$_REQUEST['year']) {
            $this->error('请选择年!');
        }
        
        $MagazineTerrm = D('MagazineTerrm');
        
        $result = $MagazineTerrm->copyMagazineTerrmByYear($_REQUEST['sourcePostCode'], $_REQUEST['destPostCode'], $_REQUEST['year']);
        
        if ($result == -1) {
            $this->error('输入参数错误，请重试!');
        } else {
            $this->success('保存成功！');
        }
    }

	public function weekAdd()
    {
        $Magazine     = M("Magazine");
        $MagazineList = $Magazine->field('postCode, name')->order("name desc")->select();
        
        $this->assign('MagazineList', $MagazineList);
        $this->assign('currentYear', date('Y'));
        $this->display();
    }
    
    public function monthAdd()
    {
        $Magazine     = M("Magazine");
        $MagazineList = $Magazine->field('postCode, name')->order("name desc")->select();
        
        $this->assign('MagazineList', $MagazineList);
        $this->assign('currentYear', date('Y'));
        $this->display();
    }

	private function setBeginAndEndMonth(&$beginMonth, &$endMonth)
	{
		switch ($_REQUEST['cycleTerm'])
		{
			case 1:
			{
				$beginMonth = 1;
				$endMonth = 12;
			}
			break;

			case 2:
			{
				$beginMonth = 1;
				$endMonth = 6;
			}
			break;

			case 3:
			{
				$beginMonth = 7;
				$endMonth = 12;
			}
			break;

			default:
			{
				$beginMonth = 1;
				$endMonth = 12;
			}
		}
	}

	var $weekEnglishNames = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

	private function getWeekDays($year, $beginMonth, $endMonth, $weekDay)
	{
		$weekDayArray = array();

		$startDate = $year.'-'.$beginMonth.'-1';
		if ($endMonth == 6)
		{
			$endDate = $year.'-'.$endMonth.'-30';
			$lastMonthDayCount = 30;
		}
		else
		{
			$endDate = $year.'-'.$endMonth.'-31';
			$lastMonthDayCount = 31;
		}
		$startDay = strtotime($startDate);

		$startWeekDay = $weekDay[0];
		$weekDayLen = count($weekDay);
		$endWeekDay = $weekDay[$weekDayLen - 1];
		
		if (intval(date('w', $startDay)) != $startWeekDay)
		{
			$weekStr = "next ".$this->weekEnglishNames[$startWeekDay];
			/* 获取第一个周几的日期 */
			$startDay = strtotime("next ".$this->weekEnglishNames[$startWeekDay], strtotime($startDate));
		}
		/* 获取第一周几的日期 */
		$startWeekDate = date('Y-m-d', $startDay);

		$endDay = strtotime($endDate);	
		$endDay = strtotime("last ".$this->weekEnglishNames[$endWeekDay], strtotime($endDate));	
		$weekNum = intval(date('W', $endDay));

		if (intval(date('w', strtotime($endDate))) == $endWeekDay )
		{
			/* 最后一天和所求的星期一样，要加1 ，这样每年有52个或53个同一星期几 */
			$weekNum++;
		}
		if (($endMonth == 12) && ($beginMonth == 7))
		{
			$weekNum = $weekNum - 26;
		}

		$lastDay;
		for ($i = 0; $i < $weekNum; $i++)
		{
			$firstFlag = true;
			for ($j = 0; $j < $weekDayLen; $j++)
			{
				if ($firstFlag)
				{
					$firstFlag = false;
					$weekDate = date('Y年m月d日', strtotime("$startWeekDate $i week"));
					/* 循环使用 */
					$weekBaseDate = date('Y-m-d', strtotime("$startWeekDate $i week"));
					$month = date("m", strtotime("$startWeekDate $i week"));

					$weekDateArray1[$j] = array('week' => $weekDate, 'month' => $month);
				}
				else
				{
					$lastDay = $weekDay[$j] - $weekDay[0];

					$weekDate = date('Y年m月d日', strtotime("$weekBaseDate +$lastDay day"));
					$month = date('m', strtotime("$weekBaseDate +$lastDay day"));

					$weekDateArray1[$j] = array('week' => $weekDate, 'month' => $month);
				}
			}

			/* 转存 $weekDateArray1 */
			for ($j = 0; $j < $weekDayLen; $j++)
			{
				$weekDateArray[$i * $weekDayLen + $j] = $weekDateArray1[$j];
			}
			unset($weekDateArray1);
		}

		
		/* 求出最后一天的日期 */
		if (($endMonth == 6) && ($beginMonth == 1))
		{
			if (empty($lastDay))
			{
				$lastWeekDay = $weekBaseDate;
			}
			else
			{
				$lastWeekDay = date('Y-m-d', strtotime("$weekBaseDate + $lastDay day"));
			}

			$tempDayArray = explode('-', $lastWeekDay);
			$monthLastDay = $tempDayArray[2];

			/* 循环判断以后的日期是否包含需要的星期 */

			$lastDayStr;
			for ($monthLastDay++; $monthLastDay <= $lastMonthDayCount; $monthLastDay++)
			{
				$lastDayStr = strtotime($tempDayArray[0].'-'.$tempDayArray[1].'-'.$monthLastDay);
				$lastWeek = intval(date('w', $lastDayStr));

				if (in_array($lastWeek, $weekDay))
				{
					$weekDateArray[] = array('week' => $tempDayArray[0].'年'.$tempDayArray[1].'月'.$monthLastDay.'日', 'month' => $endMonth);
				}
			}
		}

		return $weekDateArray;
	}

	public function saveWeekAdd()
	{
		if (!$_REQUEST['postCode']) {
            $this->error('请选择报刊!');
        } 
		
        if (!$_REQUEST['termName']) {
            $this->error('请填写开始期数,本周期开始的期数');
        }

		if (!$_REQUEST['intervalTerm']) {
            $this->error('请填写间隔期数');
        }
        
        if (!$_REQUEST['weekday']) {
            $this->error('请选择星期，每周第几个天发行');
        }

		if (!$_REQUEST['cycleTerm']) {
            $this->error('请选择周期，本次期数录入，是按一年、上半年还是下半年为周期的');
        }

		if (!$_REQUEST['year']) {
            $this->error('请填写要录入的年份');
        }

		$beginMonth = 1;
		$endMonth = 12;
		$this->setBeginAndEndMonth($beginMonth, $endMonth);

		/* 删除原来的数据 */
		$MagazineTerrm = D('MagazineTerrm');
		$map['month'] = array('between', "$beginMonth, $endMonth");
		$map['year'] = $_REQUEST['year'];
		$map['postCode'] = $_REQUEST['postCode'];
		$MagazineTerrm->where($map)->delete();
		unset($map);

		/* 获取所求区间的星期几的个数，及对应的日期 */
		$weekDateArray = $this->getWeekDays($_REQUEST['year'], $beginMonth, $endMonth, $_REQUEST['weekday']);

		if (empty($weekDateArray))
		{
			$this->error('数据错误，请重试!');
		}

		$data['insertPerson'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
		$data['insertTime'] = time();
		$data['year'] = $_REQUEST['year'];
		$data['postCode'] = $_REQUEST['postCode'];
		
		foreach ($weekDateArray as $key => $weekVo )
		{
			$data['name'] = $_REQUEST['termName'] + $key * $_REQUEST['intervalTerm'];
			$data['month'] = $weekVo['month'];
			$data['longname'] = $weekVo['week'];
			$data['termList'] = $key + 1;

			$MagazineTerrm->data($data)->add();
		}

		$this->success("添加成功!");
	}

	public function saveMonthAdd()
	{
		if (!$_REQUEST['postCode']) {
            $this->error('请选择报刊!');
        }
        
        if (!$_REQUEST['termName']) {
            $this->error('请填写开始期数,本周期开始的期数');
        }

		if (!$_REQUEST['intervalTerm']) {
            $this->error('请填写间隔期数');
        }
        
        if (!$_REQUEST['monthDay']) {
            $this->error('请填写每月的几号，报刊发行');
        }
		else
		{
			if (($_REQUEST['monthDay'] < 1 ) || ($_REQUEST['monthDay'] > 28))
			{
				$this->error('请填写每月的几号，范围为1 ~ 28');
			}
		}

		if (!$_REQUEST['cycleTerm']) {
            $this->error('请选择周期，本次期数录入，是按一年、上半年还是下半年为周期的');
        }

		if (!$_REQUEST['year']) {
            $this->error('请填写要录入的年份');
        }

		$beginMonth = 1;
		$endMonth = 12;
		$this->setBeginAndEndMonth($beginMonth, $endMonth);
		
		/* 删除原来的数据 */
		$MagazineTerrm = D('MagazineTerrm');
		$map['month'] = array('between', "$beginMonth, $endMonth");
		$map['year'] = $_REQUEST['year'];
		$map['postCode'] = $_REQUEST['postCode'];
		$MagazineTerrm->where($map)->delete();
		unset($map);

		$data['insertPerson'] = get_employeeid($_SESSION[C('USER_AUTH_KEY')]);
		$data['insertTime'] = time();
		$data['year'] = $_REQUEST['year'];
		$data['postCode'] = $_REQUEST['postCode'];

		for ($month = $beginMonth, $i = 0; $month <= $endMonth; $month++, $i++)
		{
			$data['name'] = $_REQUEST['termName'] + $i * $_REQUEST['intervalTerm'];
			$data['month'] = $month;
			$data['longname'] = $_REQUEST['year'].'年'.$month.'月'.$_REQUEST['monthDay'].'日';
			$data['termList'] = $i;

			$MagazineTerrm->data($data)->add();
		}

		$this->success("添加成功!");
	}
}