<?php
namespace Admin\Controller;

use Common\Controller\AdminBaseController;

/**
 * 后台首页控制器
 */
class IndexController extends AdminBaseController{
	/**
	 * 首页
	 */
	public function index(){
		//获取权限规则
		$authGroup = D('FinanceAuthGroup')->getAuthGroup($_SESSION['user']['id']);
		if (!empty($authGroup)) {
			$this->assign('authGroup',$authGroup);
		}
		// 分配菜单数据
		$nav_data=D('FinanceNav')->getTreeData('level','order_number,id');
		$assign=array(
			'data'=>$nav_data
            );
		$this->assign($assign);
		
        $user = session('user');
        $dept = M('Dict')->where(array('type'=>'DeptType','value'=>$user['dept_type']))->field('title')->find();
        $duty = M('Dict')->where(array('type'=>'DutyType','value'=>$user['duty']))->field('title')->find();
        $nav = array(
            '1'=>U('Admin/Index/index'),
            '2'=>U('Admin/Index/index'),
            '3'=>U('Admin/Index/index'),
            '4'=>U('Admin/Market/index')
        );
        $navTo = $nav[$user['dept_type']]?$nav[$user['dept_type']]:U('Admin/Index/welcome');
        $this->assign('welcome',$navTo);
        $this->assign('dept','['.($dept['title']?$dept['title']:'').']['.($duty['title']?$duty['title']:'').']');
		$this->display();
	}
	
	/**
	 * welcome
	 */
	public function welcome(){
	    $this->display();
	}



}
