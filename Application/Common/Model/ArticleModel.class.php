<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 文章模型
 */
class ArticleModel extends BaseModel{

	/**
	 * 加载某栏目下小于当前ID的文章列表
	 * @param  int $group_id 用户组id
	 * @return array         用户数组
	 */
	public function getList($navId,$lastId = 0){
		$nav = M('nav')->where(array('id'=>$navId))->field('page_size')->find();
		if(!$nav){
			return false;
		}
		$pageSize = $nav['page_size'];
		$where = "nav2_id=$navId and status=5";
		if($lastId){
			$aModel = $this->where(array('id'=>$lastId))->field('time')->find();
			if($aModel){
				$time = $aModel['time'];
				$where .=" and ((time=$time and id<$lastId) or time<$time)";
			}else{
				return [];
			}
		}

		$list = $this->where($where)
			->order('time desc,id desc')
			->field('id,title,cover,cover_type,description,time')
			->limit($pageSize)
			->select();
		return $list;
	}
	/**
	 * 加载某关键词下小于当前ID的文章列表
	 * @param  int $group_id 用户组id
	 * @return array         用户数组
	 */
	public function getSearch($key,$lastId = 0){
		preg_replace("/(truncate|delete|drop|insert|update)/",'',$key);
		$where = "status=5";
		if($lastId){
			$aModel = $this->where(array('id'=>$lastId))->field('time')->find();
			if($aModel){
				$time = $aModel['time'];
				$where .=" and ((time=$time and id<$lastId) or time<$time)";
			}else{
				return [];
			}
		}
		if(strlen($key)){
			$where .=" and (title like '%$key%' or description like '%$key%' or content like '%$key%')";
		}
		$list = $this->where($condition)
			->order('time desc,id desc')
			->field('id,title,cover,cover_type,description,time')
			->limit(8)
			->select();
		return $list;
	}

	public function getHome(){
		$return = array(
			'banner'=>array(
				'head'=>null,
				'des'=>[],
				'list'=>[]
			),
			'left_top'=>array(
				'nav_id'=>5,
				'head'=>null,
				'list'=>[]
			),
			'left_bottom'=>array(
				'nav_id'=>6,
				'head'=>null,
				'list'=>[]
			),
			'right_top'=>array(
				'nav_id'=>2,
				'head'=>null,
				'list'=>[]
			),
			'right_middle'=>array(
				'nav_id'=>3,
				'head'=>null,
				'list'=>[]
			),
			'right_bottom'=>array(
				'nav_id'=>4,
				'head'=>null,
				'list'=>[]
			)
		);
		$order = 'time desc,id desc';
		$field = 'id,title,cover,time';
		//构建banner
		$banner1 = $this->where(array('status'=>5))->order($order)->field($field)->limit(3)->select();
		foreach($banner1 as $k=>$v){
			if($k==0)
				$return['banner']['head'] = $v;
			else
				$return['banner']['des'][] = $v;
		}
		// $banner_list = $this->where(array('status'=>5,'cover_type'=>1))->order($order)->field($field)->limit(1)->select();
		// foreach($banner_list as $k=>$v){
		// 	$return['banner']['list'][] = $v;
		// }
		$return['banner']['list'] = $this->where(array('status'=>5,'nav1_id'=>1))->order($order)->field($field)->limit(1)->select();
		//构建left_top
		$return['left_top'] = $this->getArticles($return['left_top']['nav_id'],4,$order,$field);
		$return['left_bottom'] = $this->getArticles($return['left_bottom']['nav_id'],4,$order,$field);
		$return['right_top'] = $this->getArticles($return['right_top']['nav_id'],2,$order,$field);
		$return['right_middle'] = $this->getArticles($return['right_middle']['nav_id'],2,$order,$field);
		$return['right_bottom'] = $this->getArticles($return['right_bottom']['nav_id'],2,$order,$field);
		foreach($return as $k=>$v){
			foreach($v as $k1=>$v1){
				if(!$v1){

				}
				else if(count($v1) == count($v1,1)){
					$return[$k][$k1]['time'] = $v1['time']?date('Y-m-d',$v1['time']):'';
					$return[$k][$k1]['url'] = U('index/detail?id='.$v1['id'].'&nav_hidden=1');
				}else{
					foreach($v1 as $k2=>$v2){
						$return[$k][$k1][$k2]['time'] = $v2['time']?date('Y-m-d',$v2['time']):'';
						$return[$k][$k1][$k2]['url'] = U('index/detail?id='.$v2['id'].'&nav_hidden=1');
					}
				}
			}
		}
		return $return;
	}
	/**
	* 返回对应模块的最新新闻列表
	*/
	private function getArticles($navId,$size,$order,$field){
		$head = $this->where(array('status'=>5,'cover_type'=>1,'nav1_id'=>$navId))->order($order)->field($field)->find();
		$list = $this->where(array('status'=>5,'nav1_id'=>$navId))->order($order)->field($field)->limit($size)->select();
		return array('nav_id'=>$navId,'head'=>$head,'list'=>$list);
	}

	/**
	 * 获得model
	 */
	public function getModel($id){
		$model = $this->where(array('id'=>$id))->find();
		return $model;
	}

}
