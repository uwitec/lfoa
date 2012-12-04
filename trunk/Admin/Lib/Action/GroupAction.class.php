<?php
//分组模块
class GroupAction extends CommonAction
{
    /**
    +----------------------------------------------------------
    * 默认排序操作
    +----------------------------------------------------------
    * @access public
    +----------------------------------------------------------
    * @return void
    +----------------------------------------------------------
    * @throws FcsException
    +----------------------------------------------------------
    */
    public function sort()
    {
        $node = M('Group');
        if (!empty($_GET['sortId'])) {
            $map           = array();
            $map['status'] = 1;
            $map['id']     = array(
                'in',
                $_GET['sortId']
            );
            $sortList      = $node->where($map)->order('sort asc')->select();
        } else {
            $sortList = $node->where('status=1')->order('sort asc')->select();
        }
        $this->assign("sortList", $sortList);
        $this->display();
        return;
    }
    
    
    // 插入数据
    public function insert()
    {
        // 创建数据对象
        $User = D("Group");
        if (!$User->create()) {
            $this->error($User->getError());
        } else {
            // 写入帐号数据
            if ($result = $User->add()) {
                $this->success('菜单添加成功！');
            } else {
                $this->error('菜单添加失败！');
            }
        }
    }
    
    /**
    +----------------------------------------------------------
    * 显示操作操作
    *
    +----------------------------------------------------------
    * @access public
    +----------------------------------------------------------
    * @return string
    +----------------------------------------------------------
    * @throws FcsException
    +----------------------------------------------------------
    */
    public function show()
    {
        $name      = $this->getActionName();
        $model     = D($name);
        $pk        = $model->getPk();
        $id        = $_REQUEST[$pk];
        $condition = array(
            $pk => array(
                'in',
                $id
            )
        );
        $list      = $model->show($condition);
        if ($list !== false) {
            $this->assign("jumpUrl", $this->getReturnUrl());
            $this->success('状态禁用成功');
        } else {
            $this->error('状态禁用失败！');
        }
    }
    
    /**
    +----------------------------------------------------------
    * 隐藏操作
    *
    +----------------------------------------------------------
    * @access public
    +----------------------------------------------------------
    * @return string
    +----------------------------------------------------------
    * @throws FcsException
    +----------------------------------------------------------
    */
    public function hide()
    {
        $name      = $this->getActionName();
        $model     = D($name);
        $pk        = $model->getPk();
        $id        = $_REQUEST[$pk];
        $condition = array(
            $pk => array(
                'in',
                $id
            )
        );
        $list      = $model->hide($condition);
        if ($list !== false) {
            $this->assign("jumpUrl", $this->getReturnUrl());
            $this->success('状态禁用成功');
        } else {
            $this->error('状态禁用失败！');
        }
    }
    
}