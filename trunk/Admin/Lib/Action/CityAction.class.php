<?php
// 城市信息
class CityAction extends CommonAction
{
    function _filter(&$map)
    {
        $AreaId = $_REQUEST['areaID'];
        if ($AreaId) {
            $Province   = M("Province");
            $ProvinceRs = $Province->where('areaID = ' . $AreaId)->field('id')->select();
            foreach ($ProvinceRs as $rs) {
                $ProvinceIds .= $rs['id'] . ',';
            }
            $ProvinceIds = substr($ProvinceIds, 0, strlen($ProvinceIds) - 1);
            
            $map['provinceID'] = array(
                'in',
                $ProvinceIds
            );
        }
        
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        if (($roleEname != 'admin') || ($roleEname != 'customCenterWorker')) {
            $map['status'] = array(
                'neq',
                -1
            );
        }
    }
    
    public function before_index()
    {
        $Area     = M("Area");
        $AreaList = $Area->field('id, name')->select();
        
        $ProvinceList = array();
        if ($_REQUEST['areaID']) {
            $Province     = M("Province");
            $ProvinceList = $Province->where('areaID = ' . $_REQUEST['areaID'])->field('id, name')->select();
        }
        
        $roleEname = get_role_ename($_SESSION[C('USER_AUTH_KEY')]);
        
        $this->assign('roleEname', $roleEname);
        
        $this->assign('AreaList', $AreaList);
        $this->assign('ProvinceList', $ProvinceList);
    }
    
    public function _before_add()
    {
        $Province     = M("Province");
        $ProvinceList = $Province->field('id, name')->select();
        
        $this->assign('ProvinceList', $ProvinceList);
    }
    
    public function _before_edit()
    {
        $Province     = M("Province");
        $ProvinceList = $Province->field('id, name')->select();
        
        $this->assign('ProvinceList', $ProvinceList);
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
                $areaID   = $_REQUEST['areaID'];
                $Province = M("Province");
                if ($areaID) {
                    $ProvinceList = $Province->where('areaID = ' . $areaID)->field('id, name')->select();
                } else {
                    break;
                }
                $select[] = array(
                    'id' => '',
                    'title' => '--请选择--'
                );
                foreach ($ProvinceList as $ProvinceVo) {
                    $select[] = array(
                        'id' => $ProvinceVo['id'],
                        'title' => $ProvinceVo['name']
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