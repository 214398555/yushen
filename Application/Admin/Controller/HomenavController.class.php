<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
/**
 * 前台菜单管理
 */
class HomenavController extends AdminBaseController{
	/**
	 * 菜单列表
	 */
	public function index(){
		$data=D('Nav')->getTreeData('sort,id');
		foreach($data as $k=>$v){
			switch($v['data_type']){
				case 1:$data[$k]['data_type'] = '自动抓取';break;
				case 2:$data[$k]['data_type'] = '后台录入';break;
				default:$data[$k]['data_type'] = '忽略';break;
			}
			switch($v['status']){
				case 1:$data[$k]['status'] = '显示';break;
				default:$data[$k]['status'] = '隐藏';break;
			}
		}
		$assign=array(
			'data'=>$data
			);
		$this->assign($assign);
		$this->display();
	}

	/**
	 * 添加菜单
	 */
	public function add(){
		$data=I('post.');
		unset($data['id']);
		$user = session('user');
		if(!$data['page_size']){
			$data['page_size'] = 5;
		}
		if($data['pid']){
			$data['level'] = 2;
		}
		if($data['host']){
			$data['host'] = htmlspecialchars_decode($data['host']);
		}
		if($data['src_list']){
			$data['src_list'] = htmlspecialchars_decode($data['src_list']);
		}
		if($data['reg_list']){
			$data['reg_list'] = htmlspecialchars_decode($data['reg_list']);
		}
		if($data['reg_li']){
			$data['reg_li'] = htmlspecialchars_decode($data['reg_li']);
		}
		if($data['reg_detail']){
			$data['reg_detail'] = htmlspecialchars_decode($data['reg_detail']);
		}
		$data['create_user'] = $user['username'];
		$data['create_time'] = time();
		$result=D('Nav')->addData($data);
		if ($result) {
			$this->success('添加成功',U('Admin/Homenav/index'));
		}else{
			$this->error('添加失败');
		}
	}

	/**
	 * 修改菜单
	 */
	public function edit(){
		$data=I('post.');
		$user = session('user');
		if(!$data['page_size']){
			$data['page_size'] = 5;
		}
		if($data['pid']){
			$data['level'] = 2;
		}
		if($data['host']){
			$data['host'] = htmlspecialchars_decode($data['host']);
		}
		if($data['src_list']){
			$data['src_list'] = htmlspecialchars_decode($data['src_list']);
		}
		if($data['reg_list']){
			$data['reg_list'] = htmlspecialchars_decode($data['reg_list']);
		}
		if($data['reg_li']){
			$data['reg_li'] = htmlspecialchars_decode($data['reg_li']);
		}
		if($data['reg_detail']){
			$data['reg_detail'] = htmlspecialchars_decode($data['reg_detail']);
		}
		$data['create_user'] = $user['username'];
		$data['create_time'] = time();
		$map=array(
			'id'=>$data['id']
			);
		$result=D('Nav')->editData($map,$data);
		if ($result) {
			$this->success('修改成功',U('Admin/Homenav/index'));
		}else{
			$this->error('修改失败');
		}
	}

	/**
	 * 删除菜单
	 */
	public function delete(){
		$id=I('get.id');
		$map=array(
			'id'=>$id
			);
		$result=D('Nav')->deleteData($map);
		if($result){
			$this->success('删除成功',U('Admin/Homenav/index'));
		}else{
			$this->error('请先删除子菜单');
		}
	}

	/**
	 * 菜单排序
	 */
	public function order(){
		$data=I('post.');
		$result=D('Nav')->orderData($data,'id','sort');
		if ($result) {
			$this->success('排序成功',U('Admin/Homenav/index'));
		}else{
			$this->error('排序失败');
		}
	}

	public function detail(){
		$id = I('post.id');
		$model = M('nav')->where(array('id'=>$id))->find();
		$this->ajaxReturn(array('code'=>200,'data'=>$model));
	}

}
