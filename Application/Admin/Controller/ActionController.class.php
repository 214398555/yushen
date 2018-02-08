<?php
namespace Admin\Controller;

use Think\Controller;

/**
 * 后台定时任务控制器
 */
class ActionController extends Controller{
	public $start_time_list = 0;
	public $start_time_detail =0;
	public function test(){
		log_1('开始更新');
		$result = M('dict')->where(array('type'=>'Test'))->save(array('value'=>time()));
		if($result){
			log_1('更新成功');
		}else{
			log_1('更新失败');
		}
		echo 'success';
	}
	private function checkTimeEnd($type,$memo = ''){
		if($type=='list'){
			//最大执行时间限制为25分钟，即1500s
			$time = time() - $this->start_time_list;
			if($time > 1500){
				log_1('updateList执行至【'.$memo.'】时间已达'.$time.'秒，终止执行，并返回Success');
				echo 'success';
				exit;
			}
		}else if($type=='detail'){
			//最大执行时间限制为8分钟，即480s
			$time = time() - $this->start_time_detail;
			if($time > 480){
				log_1('updateDetail执行至【'.$memo.'】时间已达'.$time.'秒，终止执行，并返回Success');
				echo 'success';
				exit;
			}
		}
	}
	/**
	 * 【公开】更新文章列表，可以通过定时器请求
	 */
	public function update_list(){
		$this->start_time_list = time();
		$dict = M('dict');
		$data_init = $dict->where(array('type'=>'DataStatus'))->field('value')->find();
		if(!$data_init){
			log_1('因无法判断数据是否初始化，忽略本次执行，请查看DataStatus数据是否存在');
			echo 'fail';
		}
		$initStatus = $data_init['value']==1?1:0;//标记是否初始化

		$navs = M('nav')->where(array('data_type'=>1))->field('id,pid,name,host,src_list,reg_list,reg_li,reg_detail,max_page,cur_page')->select();
		foreach($navs as $k=>$v){
			$this->_update_list($v,$initStatus);
			if($initStatus){
				$this->checkTimeEnd('list','栏目“'.$v['name'].'”新闻列表更新完成');
			}
		}
		if(!$initStatus){
			//若尚未进行初始化，则更新初始化状态
			$dict->where(array('type'=>'DataStatus'))->save(array('value'=>1));
			log_1('数据初始化完成，状态已更新');
		}else{
			log_1('本次数据更新全部完成，时间共计'.(time() - $this->start_time_list).'秒');
		}
		echo 'success';
	}
	/**
	* 【公开】更新尚无内容的文章，可以通过定时器请求
	*/
	public function update_detail(){
		$this->start_time_detail = time();
		$navs = M('nav')->where(array('data_type'=>1))->field('id,pid,name,host,src_list,reg_list,reg_li,reg_detail,max_page,cur_page')->select();
		foreach($navs as $k=>$v){
			log_1($v['name'].'：开始更新文章内容');
			$this->_update_detail($v);
			log_1($v['name'].'：更新文章内容完成');
			$this->checkTimeEnd('detail','栏目“'.$v['name'].'”文章内容更新完成');
		}
		log_1('本次数据更新全部完成，时间共计'.(time() - $this->start_time_detail).'秒');
		echo 'success';
	}
	/**
	* 更新某个栏目下的文章列表
	* @param $v 栏目模型，存放栏目相关信息
	* @param $initStatus 数据初始化状态，用来判断是之抓取前几页还是 全部抓取
	*/
	public function _update_list($v,$initStatus){
		$articleModel = M('article');
		$dict = M('dict');
		if(!$v['src_list']){
			log_1($v['name'].'：未设置抓取地址，自动忽略');
			return;
		}
		if(!$v['reg_list']){
			//当不存在列表页正则时，说明是为JSON数据，直接存储
			$file = dirname(THINK_PATH)."/Public/home/js/data_collect.json";
			$content = $this->get_json($v['src_list']);
			if(strlen($content)<500){
				log_1($v['name'].'：数据请求失败：'.$content);
				return;
			}
			$md5 = md5($content);
			$data = $dict->where(array('type'=>'DataCollect'))->field('value')->find();
			if($data){
				if($data['value'] == $md5){
					//若数据相等则无需更新
					log_1($v['name'].'：数据没有变化，自动忽略');
				}else{
					$dict->where(array('id'=>$data['id']))->save(array(
						'title'=>'数据中心|'.date('Y-m-d H:i:s',time()),
						'value'=>$md5,
						'description'=>''
					));
					file_put_contents($file,$content);
					log_1($v['name'].'：数据更新成功');
				}
			}else{
				//不存在则插入
				$dict->add(array(
					'title'=>'数据中心|'.date('Y-m-d H:i:s',time()),
					'value'=>$md5,
					'type'=>'DataCollect',
					'description'=>''
				));
				file_put_contents($file,$content);
				log_1($v['name'].'：数据插入成功');
			}
			return;
		}
		$maxPage = $initStatus?$v['cur_page']:$v['max_page'];
		for($i = $maxPage;$i>0;$i--){
			$url = str_replace('{page}',$i,$v['src_list']);
			$list = $this->get_content($url,$v['reg_list'],$v['reg_li']);
			if(!$list || count($list)==0){
				//若结果为空，则说明到最后一页，则中断循环
				log_1($v['name'].'：第'.$i.'页，没有数据，自动忽略');
				continue;
			}
			$adds = [];
			foreach($list as $k1=>$v1){
				if(!$v1['source_link']){
					log_1('ERROR：链接为空，第'.$i.'页，'.$v1['title']);
					continue;
				}
				$link = $v['host'].$v1['source_link'];
				$count = $articleModel->where(array('data_type'=>1,'source_link'=>$link))->count();
				if(!$count){
					$v1['source_link'] = $link;
					if($v['pid']){
						$v1['nav1_id'] = $v['pid'];
						$v1['nav2_id'] = $v['id'];
					}
					if($v1['cover'] && substr($v1['cover'],0,4)!='http'){
						$v1['cover'] = $v['host'].$v1['cover'];
					}
					$adds[] = $v1;
				}
			}
			log_1($v['name'].'：第'.$i.'页，共计'.count($list).'条，插入'.count($adds).'条');
			if(count($adds)){
				$articleModel->addAll($adds);
				sleep(1);
			}
			if($initStatus){
				$this->checkTimeEnd('list','栏目“'.$v['name'].'”列表倒序更新'.$maxPage.'页第'.$i.'页更新完成');
			}
		}
		log_1($v['name'].'：列表更新完成，时间共计'.(time() - $this->start_time_list).'秒');
	}
	/**
	* 更新某个栏目下无内容的文章
	* @param $nav 栏目模型，记录栏目基础信息
	*/
	public function _update_detail($nav){
		$articleModel = M('article');
		$article = $articleModel->where(array('nav2_id'=>$nav['id'],'data_type'=>1,'status'=>0))->field('id,source_link')->select();
		log_1($nav['name'].'：共找到'.count($article).'条待更新文章');
		foreach($article as $k=>$v){
			$link = $v['source_link'];
			if($link){
				sleep(1);
				$data = $this->get_detail($link,$nav['reg_detail'],$nav['host']);
				if($data){
					if($v['description'] && $data['description'])
						$data['description'] = $v['description'];
					$articleModel->where(array('id'=>$v['id']))->save($data);
					log_1($nav['name'].'：文章'.($k+1).'_'.$v['id'].'更新成功已发布');
				}else{
					log_1($nav['name'].'：文章'.($k+1).'_'.$v['id'].'内容读取失败：'.$link);
				}
			}else{
				log_1($nav['name'].'：文章'.($k+1).'_'.$v['id'].'链接不存在');
			}
			$this->checkTimeEnd('detail','栏目“'.$nav['name'].'”需更新文章'.count($article).'个，第'.($k+1).'['.$v['id'].']个更新完成');
		}
	}
	/**
	* 格式化抓取过来的内容
	*/
	private function formatContent($content){
		$content = preg_replace("/\r\n/"," ",$content);
		$content = preg_replace("/\r/"," ",$content);
		$content = preg_replace("/\n/"," ",$content);
		$content = preg_replace("/[\t]+/"," ",$content);
		$content = preg_replace("/[ ]+/"," ",$content);
		$content = preg_replace("/>[ ]+</","><",$content);
		return $content;
	}
	/**
	* 获取JSON数据
	*/
	private function get_json($url){
		$content = curl_get($url);
		return $content;
	}
	/**
	* 获取对应链接的返回结果并使用reg_list进行获取匹配
	*/
	private function get_content($url,$reg_list,$reg_li){
		$content = curl_get($url);
		$content = $this->formatContent($content);
		$match= array();
		preg_match("/$reg_list/",$content,$match);
		return $this->get_li($match,$reg_li);
	}
	/**
	* 通过reg_li对上步获取后的内容进行再次匹配，返回构建好的数据集
	*/
	private function get_li($match,$reg_li){
		$list = [];
		if($match){
			$matches = [];
			preg_match_all("/$reg_li/",$match[0],$matches);
			if($matches && count($matches)>0){
				foreach($matches[0] as $k=>$v){
					$item = array(
						'title'=>trim($matches['title'][$k]),
						'cover_type'=>0,
						'cover'=>'',
						'description'=>'',
						'time'=>strtotime(trim($matches['time'][$k])),
						'source'=>'',
						'author'=>'',
						'editor'=>'',
						'content'=>'',
						'data_type'=>1,
						'source_link'=>trim($matches['source_link'][$k]),
						'nav1_id'=>0,
						'nav2_id'=>0,
						'create_time'=>time(),
						'create_user'=>'',
						'status'=>0
					);
					if(isset($matches['description'])){
						$item['description'] = trim($matches['description'][$k]);
					}
					if(isset($matches['cover']) && $matches['cover'][$k]){
						$item['cover'] = trim($matches['cover'][$k]);
						$item['cover_type'] = 1;
					}
					$list[] = $item;
				}
			}
		}
		return $list;
	}
	/**
	* 获取URL返回的内容并使用reg_detail进行获取匹配，返回构建好的更新模型
	*/
	private function get_detail($url,$reg_detail,$host){
		$content = curl_get($url);
		$content = $this->formatContent($content);
		$match= array();
		preg_match("/$reg_detail/",$content,$match);
		if($match){
			$item = array(
				'status'=>5
			);
			if(isset($match['time']) && $match['time']){
				$time = strtotime(trim(str_replace('发布时间：','',$match['time'])));
				if($time)
					$item['time'] = $time;
			}
			if(isset($match['source']) && $match['source']){
				$source = str_replace('来源：','',$match['source']);
				if($source){
					$item['source'] = trim($source);
				}
			}
			if(isset($match['author']) && $match['author']){
				$author = str_replace('作者：','',$match['author']);
				if($author)
					$item['author'] = trim($author);
			}
			if(isset($match['editor']) && $match['editor']){
				$editor = str_replace('编辑：','',$match['editor']);
				if($editor)
					$item['editor'] = trim($editor);
			}
			if(isset($match['content']) && $match['content']){
				$content = $match['content'];
				$reg = "/(?<l><img (((?!>).)*)src=\")(?<src>[^http]((?!\").)*)(?<r>\"(((?!>).)*)\/>)/";
				$matches = [];
				preg_match_all($reg,$content,$matches);
				if($matches && count($matches)){
					foreach($matches[0] as $k=>$v){
						$l = $matches['l'][$k];
						$r = $matches['r'][$k];
						$src = $matches['src'][$k];
						$content = str_replace($l.$src.$r, $l.$host.$src.$r, $content);
					}
				}
				$item['content'] = $content;
				$reg_description= "/<p(?:.*?)>(?<para>((?!<).){20,})<\/p>/";
				$des = array();
				preg_match($reg_description,$content,$des);
				if($des && count($des)){
					$item['description'] = trim($des['para']);
				}
			}
			return $item;
		}else{
			return false;
		}
	}
}
