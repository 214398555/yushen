<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
/**
 * 字典库管理
 */
class DictController extends AdminBaseController{
	/**
	 * 菜单列表
	 */
	public function index(){
		$data=D('Dict')->getPage(M('Dict'),array(),'type,listorder,id',20);
		$this->assign($data);
		$this->display();
	}

	public function clear_cache(){
		A('Common/Cache')->clearAll();
		$this->ajaxReturn(array('code'=>200));
	}
	/**
	 * 添加菜单
	 */
	public function add(){
		$data=I('post.');
		unset($data['id']);
		$data['create_timespan'] = time();
		if($data['title']){
			$data['title'] = htmlspecialchars_decode($data['title']);
		}
		if($data['value']){
			$data['value'] = htmlspecialchars_decode($data['value']);
		}
		if($data['description']){
			$data['description'] = htmlspecialchars_decode($data['description']);
		}
		$result=D('Dict')->addData($data);
		if ($result) {
			$this->success('添加成功',U('Admin/Dict/index'));
		}else{
			$this->error('添加失败');
		}
	}

	/**
	 * 修改菜单
	 */
	public function edit(){
		$data=I('post.');
		$data['create_timespan'] = time();
		if($data['title']){
			$data['title'] = htmlspecialchars_decode($data['title']);
		}
		if($data['value']){
			$data['value'] = htmlspecialchars_decode($data['value']);
		}
		if($data['description']){
			$data['description'] = htmlspecialchars_decode($data['description']);
		}
		$map=array(
			'id'=>$data['id']
			);
		$result=D('Dict')->editData($map,$data);
		if ($result) {
			$this->success('修改成功',U('Admin/Dict/index'));
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
		$result=D('Dict')->deleteData($map);
		if($result){
			$this->success('删除成功',U('Admin/Dict/index'));
		}else{
			$this->error('删除失败');
		}
	}

	public function detail(){
		$id = I('post.id');
		$model = M('Dict')->where(array('id'=>$id))->find();
		$this->ajaxReturn(array('code'=>200,'data'=>$model));
	}

}
