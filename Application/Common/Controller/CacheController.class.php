<?php
namespace Common\Controller;

use Think\Controller; 

class CacheController extends Controller {
	private static $nameList = array(
		'nav_tree'=>'nav_tree',
		'home_data'=>'home_data',
		'data_collect'=>'data_collect',
		'hot_search'=>'hot_search'
	);
	public function clearAll () {
		foreach(self::$nameList as $k=>$v){
			S($v,null);
		}
	}
	/**
	* 返回所有菜单数据（自动记录缓存）
	*/
	public function getNav(){
		$cacheName = self::$nameList['nav_tree'];
		$list = S($cacheName);
		if(!$list){
			$data = M('nav')->order('level,sort')->select();
			foreach($data as $k=>$v){
            	$data[$k]['url'] = $v['url']?substr($v['url'],0,4)=='http'?$v['url']:U($v['url']):'#';
			}
			S($cacheName,$data,300);
			$list = S($cacheName);
		}
		return $list;
	}

	/**
	* 返回首页显示数据
	*/
	public function getHome(){
		$cacheName = self::$nameList['home_data'];
		$data = S($cacheName);
		if(!$data){
			$data = D('Article')->getHome();
			S($cacheName,$data,300);
		}
		return $data;
	}
	/**
	* 返回数据统计数据集
	*/
	public function getDataCollect(){
		$cacheName = self::$nameList['data_collect'];
		$data = S($cacheName);
		if(!$data){
			$model = M('Dict')->where(array('type'=>'DataCollect'))->field('value,description')->find();
			if(!$model){
				return false;
			}
			S($cacheName,array('md5'=>$model['value'],'list'=>json_decode($model['description'],true)),300);
			$data = S($cacheName);
		}
		return $data;
	}

	/**
	* 返回热搜词组
	*/
	public function getHotSearch(){
		$cacheName = self::$nameList['hot_search'];
		$data = S($cacheName);
		if(!$data){
			$model = M('Dict')->where(array('type'=>'HotSearch'))->field('description')->find();
			if(!$model){
				return false;
			}
			$data = [];

			if($model['description']){
				$list = explode(' ',$model['description']);
				foreach($list as $k=>$v){
					if(strlen($v)){
						$data[] = array('name'=>$v,'url'=>U('index/search').'?key='.urlencode($v));
					}
				}
			}
			S($cacheName,$data,300);
			$data = S($cacheName);
		}
		return $data;
	}
}