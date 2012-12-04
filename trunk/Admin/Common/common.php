<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2007 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

//公共函数
function toDate($time, $format = 'Y-m-d H:i:s') {
	if (empty ( $time )) {
		return '';
	}
	$format = str_replace ( '#', ':', $format );
	return date ($format, $time );
}

function Add_S(&$array){
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $array[$key] = addslashes(trim($value));
            } else {
                Add_S($array[$key]);
            }
        }
    }
}


function uploadFileSuccessRespond($Msg = '操作成功！', $navTabId)
{
	header("Content-type: text/html; charset=utf-8"); 
	$respondStr = '
	<script type="text/javascript"> 
		var statusCode = 200;
		var message = "'.$Msg.'";
		var navTabId = "'.$navTabId.'";
		var forwardUrl = "";
		var callbackType = "closeCurrent"
			
		var response = {
			statusCode:statusCode,
			message:message,
			navTabId:navTabId,
			forwardUrl:forwardUrl,
			callbackType:callbackType
		};
		if (window.parent.donecallback) window.parent.donecallback(response);
		window.parent.navTab.reload(null, {}, navTabId);
	</script>';

	echo $respondStr;
	exit();
}


function uploadFileErrorRespond($Msg = '操作失败！')
{
	header("Content-type: text/html; charset=utf-8"); 
	$respondStr = '
	<script type="text/javascript"> 
		var statusCode = 200;
		var message = "'.$Msg.'";
		var navTabId = "";
		var forwardUrl = "";
		var callbackType = "closeCurrent"
			
		var response = {
			statusCode:statusCode,
			message:message,
			navTabId:navTabId,
			forwardUrl:forwardUrl,
			callbackType:callbackType
		};
		if (window.parent.donecallback) window.parent.donecallback(response);
	</script>';

	echo $respondStr;
	exit();
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

// 缓存文件
function cmssavecache($name = '', $fields = '') {
	$Model = D ( $name );
	$list = $Model->select ();
	$data = array ();
	foreach ( $list as $key => $val ) {
		if (empty ( $fields )) {
			$data [$val [$Model->getPk ()]] = $val;
		} else {
			// 获取需要的字段
			if (is_string ( $fields )) {
				$fields = explode ( ',', $fields );
			}
			if (count ( $fields ) == 1) {
				$data [$val [$Model->getPk ()]] = $val [$fields [0]];
			} else {
				foreach ( $fields as $field ) {
					$data [$val [$Model->getPk ()]] [] = $val [$field];
				}
			}
		}
	}
	$savefile = cmsgetcache ( $name );
	// 所有参数统一为大写
	$content = "<?php\nreturn " . var_export ( array_change_key_case ( $data, CASE_UPPER ), true ) . ";\n?>";
	file_put_contents ( $savefile, $content );
}

function cmsgetcache($name = '') {
	return DATA_PATH . '~' . strtolower ( $name ) . '.php';
}
function get_status($status, $imageShow = true) {
	switch ($status) {
		case 0 :
			$showText = '禁用';
			$showImg = '<IMG SRC="' . __PUBLIC__ . '/Images/error.png" WIDTH="20" HEIGHT="17" BORDER="0" ALT="禁用">';
			break;
		case 2 :
			$showText = '待审';
			$showImg = '<IMG SRC="' . __PUBLIC__ . '/Images/locked.png" WIDTH="20" HEIGHT="17" BORDER="0" ALT="待审">';
			break;
		case - 1 :
			$showText = '删除';
			$showImg = '<IMG SRC="' . __PUBLIC__ . '/Images/del.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="删除">';
			break;
		case 1 :
		default :
			$showText = '正常';
			$showImg = '<IMG SRC="' . __PUBLIC__ . '/Images/ok.png" WIDTH="20" HEIGHT="17" BORDER="0" ALT="正常">';

	}
	return ($imageShow === true) ?  $showImg  : $showText;

}
function getDefaultStyle($style) {
	if (empty ( $style )) {
		return 'blue';
	} else {
		return $style;
	}

}
function IP($ip = '', $file = 'UTFWry.dat') {
	$_ip = array ();
	if (isset ( $_ip [$ip] )) {
		return $_ip [$ip];
	} else {
		import ( "ORG.Net.IpLocation" );
		$iplocation = new IpLocation ( $file );
		$location = $iplocation->getlocation ( $ip );
		$_ip [$ip] = $location ['country'] . $location ['area'];
	}
	return $_ip [$ip];
}

function get_node_name($id) {
	if (Session::is_set ( 'nodeNameList' )) {
		$name = Session::get ( 'nodeNameList' );
		return $name [$id];
	}
	$Group = D ( "Node" );
	$list = $Group->getField ( 'id,name' );
	$name = $list [$id];
	Session::set ( 'nodeNameList', $list );
	return $name;
}

function get_role_name($id = '')
{
	if (!empty($id))
	{
		$RoleUser = D ("Role_user");
		$Role = D ("Role");
		$RoleId = $RoleUser->where("user_id = ".$id)->getField('role_id');
		$RoleName = $Role->where("id = ".$RoleId)->getField('name');

		return $RoleName;
	}
}


function get_role_ename($id = '')
{
	if (!empty($id))
	{
		$RoleUser = D ("Role_user");
		$Role = D ("Role");
		$RoleId = $RoleUser->where("user_id = ".$id)->getField('role_id');
		if ($RoleId)
		{
			$RoleName = $Role->where("id = ".$RoleId)->getField('ename');
			return $RoleName;
		}		
	}
}

/* custom */
function get_custom_unit_name($UnitId = '')
{
	if (!empty($UnitId))
	{
		$CustomUnit = D ("CustomUnit");
		$CustomUnitName = $CustomUnit->where("id = ".$UnitId)->getField('name');

		return $CustomUnitName;
	}
}

/* employee */
function get_duty_name($DutyId = '')
{
	if (!empty($DutyId))
	{
		$Duty = D ("Duty");
		$DutyName = $Duty->where("id = ".$DutyId)->getField('name');

		return $DutyName;
	}
}

function get_department_name($DepartmentId = '')
{
	if (!empty($DepartmentId))
	{
		$Department = D ("Department");
		$DepartmentName = $Department->where("id = ".$DepartmentId)->getField('name');

		return $DepartmentName;
	}
}

function get_department_name_by_employeeid($employeeID = '')
{
	if (!empty($empoyeeID))
	{
		$Department = D ("Department");

		$Department->table(array('tb_employee' => 'employee'))->
		join('tb_department department on department.id = employee.deptID');
		$DepartmentName = $Department->where("employee.id = ".$employeeID)->getField('department.name');

		return $DepartmentName;
	}
}

/* magazine */
function get_magazine_type_name($TypeId = '')
{
	if (!empty($TypeId))
	{
		$MagazineType = D("MagazineType");
		$MagazineTypeName = $MagazineType->where("id = ".$TypeId)->getField("name");

		return $MagazineTypeName;
	}
}

function get_magazine_name($postCode = '')
{
	if (!empty($postCode))
	{
		$Magazine = D("Magazine");
		$MagazineName = $Magazine->where("postCode = '".$postCode."'")->getField('name');

		return $MagazineName;
	}
}

function get_magazine_type_name_by_postcode($postCode = '')
{
	if (!empty($postCode))
	{
		$Magazine = D("Magazine");
		$MagazineTypeID = $Magazine->where("postCode = '".$postCode."'")->getField('typeID');

		return get_magazine_type_name($MagazineTypeID);
	}
}

/* send_goods_type */
function get_send_goods_sort_name($SendGoodsSortId = '')
{
	if (!empty($SendGoodsSortId))
	{
		$SendGoodsSort = D("SendGoodsSort");
		$SendGoodsSortName = $SendGoodsSort->where("id = ".$SendGoodsSortId)->getField('name');

		return $SendGoodsSortName;
	}
}

function get_send_order_cyle_name($SendOrderCyleId = '')
{
	if (!empty($SendOrderCyleId))
	{
		$SendOrderCyle = D('SendOrderCyle');

		$SendOrderCyleName = $SendOrderCyle->where("id = ".$SendOrderCyleId)->getField('name');

		return $SendOrderCyleName;
	}
}

function get_send_goods_type_name($SendGoodsTypeId)
{
	if ($SendGoodsTypeId)
	{
		$SendGoodsType = D('SendGoodsType');
		$SendGoodsTypeName = $SendGoodsType->where("id = ".$SendGoodsTypeId)->getField('name');

		return $SendGoodsTypeName;
	}

}

function get_magazine_terrm_name($MagazineTerrmId = '')
{
	if (!empty($MagazineTerrmId))
	{
		$MagazineTerrm = D('MagazineTerrm');
		$MagazineTerrmName = $MagazineTerrm->where('id = '.$MagazineTerrmId)->getField('name');

		return $MagazineTerrmName;
	}
}

function get_pay_type_name($PayTypeId = '')
{
	if (!empty($PayTypeId))
	{
		$PayType = D('PayType');

		$PayTypeName = $PayType->where('id = '.$PayTypeId)->getField('name');

		return $PayTypeName;
	}
}

function get_employeeid($UserId = '')
{
	if (!empty($UserId))
	{
		$User = D('User');
		$EmployeeId = $User->where('id = '.$UserId)->getField('employeeID');

		return $EmployeeId;
	}
}

function get_employee_name($EmployeeId = '')
{
	if (!empty($EmployeeId))
	{
		$Employee = D ("Employee");
		$EmployeeName = $Employee->where("id = ".$EmployeeId)->getField('employeeName');

		return $EmployeeName;
	}
}

/* province */
function get_area_name($AreaId = '')
{
	if (!empty($AreaId))
	{
		$Area = D("Area");
		$AreaName = $Area->where("id = ".$AreaId)->getField('name');

		return $AreaName;
	}
}

function get_province_name($ProvinceId = '')
{
	if (!empty($ProvinceId))
	{
		$Province = D("Province");
		$ProvinceName = $Province->where("id = ".$ProvinceId)->getField('name');

		return $ProvinceName;
	}
}

function get_city_name($CityId = '')
{
	if (!empty($CityId))
	{
		$City = D("City");
		$CityName = $City->where("id = ".$CityId)->getField('name');

		return $CityName;
	}
}

function get_custom_name($CustomId = '')
{
	if (!empty($CustomId))
	{
		$Custom = D('Custom');
		$CustomName = $Custom->where('id = '.$CustomId)->getField('name');

		return $CustomName;
	}
}

function get_pawn($pawn) {
	if ($pawn == 0)
		return "<span style='color:green'>没有</span>";
	else
		return "<span style='color:red'>有</span>";
}
function get_patent($patent) {
	if ($patent == 0)
		return "<span style='color:green'>没有</span>";
	else
		return "<span style='color:red'>有</span>";
}


function getNodeGroupName($id) {
	if (empty ( $id )) {
		return '未分组';
	}
	if (isset ( $_SESSION ['nodeGroupList'] )) {
		return $_SESSION ['nodeGroupList'] [$id];
	}
	$Group = D ( "Group" );
	$list = $Group->getField ( 'id,title' );
	$_SESSION ['nodeGroupList'] = $list;
	$name = $list [$id];
	return $name;
}

function getCardStatus($status) {
	switch ($status) {
		case 0 :
			$show = '未启用';
			break;
		case 1 :
			$show = '已启用';
			break;
		case 2 :
			$show = '使用中';
			break;
		case 3 :
			$show = '已禁用';
			break;
		case 4 :
			$show = '已作废';
			break;
	}
	return $show;

}

function show_status($status, $id, $module_name) {

	switch ($status) {
		case 0 :
			$info = '<a href="__URL__/resume/id/' . $id . '/navTabId/'.$module_name.'" target="navTabTodo">恢复</a>';
			break;
		case 2 :
			$info = '<a href="__URL__/pass/id/' . $id . '/navTabId/'.$module_name.'" target="navTabTodo">批准</a>';
			break;
		case 1 :
			$info = '<a href="__URL__/forbid/id/' . $id . '/navTabId/'.$module_name.'" target="navTabTodo">禁用</a>';
			break;
		case - 1 :
			$info = '<a href="__URL__/recycle/id/' . $id . '/navTabId/'.$module_name.'" target="navTabTodo">还原</a>';
			break;
	}
	return $info;
}


function show_view($status, $id, $module_name) {
	switch ($status) {
		case 0 :
			$info = '<a href="__URL__/show/id/' . $id . '/navTabId/'.$module_name.'" target="navTabTodo">显示</a>';
			break;
		case 1 :
			$info = '<a href="__URL__/hide/id/' . $id . '/navTabId/'.$module_name.'" target="navTabTodo">隐藏</a>';
			break;
	}
	return $info;
}

/**
 +----------------------------------------------------------
 * 获取登录验证码 默认为4位数字
 +----------------------------------------------------------
 * @param string $fmode 文件名
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function build_verify($length = 4, $mode = 1) {
	return rand_string ( $length, $mode );
}


function getGroupName($id) {
	if ($id == 0) {
		return '无上级组';
	}
	if ($list = F ( 'groupName' )) {
		return $list [$id];
	}
	$dao = D ( "Role" );
	$list = $dao->findAll ( array ('field' => 'id,name' ) );
	foreach ( $list as $vo ) {
		$nameList [$vo ['id']] = $vo ['name'];
	}
	$name = $nameList [$id];
	F ( 'groupName', $nameList );
	return $name;
}
function sort_by($array, $keyname = null, $sortby = 'asc') {
	$myarray = $inarray = array ();
	# First store the keyvalues in a seperate array
	foreach ( $array as $i => $befree ) {
		$myarray [$i] = $array [$i] [$keyname];
	}
	# Sort the new array by
	switch ($sortby) {
		case 'asc' :
			# Sort an array and maintain index association...
			asort ( $myarray );
			break;
		case 'desc' :
		case 'arsort' :
			# Sort an array in reverse order and maintain index association
			arsort ( $myarray );
			break;
		case 'natcasesor' :
			# Sort an array using a case insensitive "natural order" algorithm
			natcasesort ( $myarray );
			break;
	}
	# Rebuild the old array
	foreach ( $myarray as $key => $befree ) {
		$inarray [] = $array [$key];
	}
	return $inarray;
}

/**
	 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码
 * 默认长度6位 字母和数字混合 支持中文
	 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
	 +----------------------------------------------------------
 * @return string
	 +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
	$str = '';
	switch ($type) {
		case 0 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 1 :
			$chars = str_repeat ( '0123456789', 3 );
			break;
		case 2 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
			break;
		case 3 :
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
			break;
	}
	if ($len > 10) { //位数过长重复字符串一定次数
		$chars = $type == 1 ? str_repeat ( $chars, $len ) : str_repeat ( $chars, 5 );
	}
	if ($type != 4) {
		$chars = str_shuffle ( $chars );
		$str = substr ( $chars, 0, $len );
	} else {
		// 中文随机字
		for($i = 0; $i < $len; $i ++) {
			$str .= msubstr ( $chars, floor ( mt_rand ( 0, mb_strlen ( $chars, 'utf-8' ) - 1 ) ), 1 );
		}
	}
	return $str;
}
function pwdHash($password, $type = 'md5') {
	return hash ( $type, $password );
}
//根据ID获得分类名
function getCategoryName($id){
	if (empty ( $id )) {
		return '顶级分类';
	}
	$Category = D ( "Category" );
	$list = $Category->getField ( 'id,title' );
	$name = $list [$id];
	return $name;
}
//根据ID获得用户名
function getUserName($id){
	if (empty ( $id )) {
		return '游客';
	}
	$User = D ( "User" );
	$list = $User->getField ( 'id,nickname' );
	$name = $list [$id];
	return $name;
}
//获取模块名称
function getModuleName($key){
	if (empty ( $key )) {
		return '未知模块';
	}
	$Category = D('Category')->getModule();
	return $Category[$key];
}
//获取上层节点ID
function getParentNodeId($pid){
	if ($pid)
	{
		$id = D('Node')->where('id='.$pid)->getField('pid');
		return $id?$id:0;
	}
	else
	{
		return 0;
	}
}