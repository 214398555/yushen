<?php
namespace Admin\Controller;

use Common\Controller\AdminBaseController;

/**
 * 后台首页控制器
 */
class ModuleController extends AdminBaseController{


    /**
     * 内容列表
     */
    public function getList ()
    {
        $module = M('Nav') -> where(array('level' => 1)) -> select();
        $searchData = $this -> _searchCondition('content');
        $count = M('Article') -> where($searchData['where']) -> count();
        $pagesize = $searchData['condition']['pagesize'];
        $page = getpage($count, $pagesize);
        foreach($searchData['condition'] as $keys=>$vals) {
            $page->parameter[$keys]   =   urlencode($vals);
        }
        $show       = $page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        if(I('get.export_csv') == '1'){//导出
            $limit = '';
        } else {
            $limit = $page->firstRow.','.$page->listRows;
        }
        $data = M('Article') -> field('id,title,description,time,source,author,editor,data_type,create_time,status')
                            -> where($searchData['where'])
                            -> limit($limit)
                            -> order('create_time desc,time desc')
                            -> select();
        $this -> assign('condition',$searchData['condition']);
        $this -> assign('list',$data);
        $this -> assign('page',$show);
        $this -> assign('module', $module);
        $this -> display();
    }



    /**
     * 编辑内容
     */
    public function editContent ()
    {
        if (IS_POST) {
            $post = I('post.');
            $coverImg = $_FILES['coverImage'];
            $coverVideo = $_FILES['coverVideo'];
            $data = [];
            if ($post['coverType'] == 1 && ($coverImg['name'] || $post['coverImageOld'])) { //封面图
                if ($coverImg['name'])
                    $data['cover'] = $this -> _upFile('image', $coverImg);
                else
                    $data['cover'] = $post['coverImageOld'];
                $data['cover_type'] = 1;
            } else if ($post['coverType'] == 2 && ($coverVideo['name'] || $post['coverVideoOld'])) { //封面视频
                if ($coverVideo['name'])
                    $data['cover'] = $this -> _upFile('video', $coverVideo);
                else
                    $data['cover'] = $post['coverVideoOld'];
                $data['cover_type'] = 2;
            } else {
                $data['cover_type'] = 0;
                $data['cover'] = '';
            }
            if ($post['articleName']) {
                $data['title'] = $post['articleName'];
            } else {
                $data['title'] = '';
            }
            if ($post['description'])
                $data['description'] = $post['description'];
            else 
                $data['description'] = '';

            if ($post['source'])
                $data['source'] = $post['source'];
            else 
                $data['source'] = '';

            if ($post['author'])
                $data['author'] = $post['author'];
            else 
                $data['author'] = '';

            if ($post['editor'])
                $data['editor'] = $post['editor'];
            else 
                $data['editor'] = '';

            if ($post['time'])
                $data['time'] = strtotime($post['time']);
            else 
                $data['time'] = '';

            if ($post['detail'])
                $data['content'] = htmlspecialchars_decode($post['detail']);
            else 
                $data['content'] = '';

            if ($post['moduleOne']) {
                $data['nav1_id'] = $post['moduleOne'];
                if ($post['moduleTwo'])
                    $data['nav2_id'] = $post['moduleTwo'];  
                else
                    $data['nav2_id'] = 0;  
            } else {
                $data['nav1_id'] = 0;  
                $data['nav2_id'] = 0;  
            }
            $data['data_type'] = 2;
            $data['create_time'] = time();
            $data['create_user'] = $_SESSION['user']['username'];
            $data['status'] = 5;
            if ($post['id'] && M('Article') -> where(array('id' => $post['id'])) -> find()) {
                $res = M('Article') -> where(array('id' => $post['id'])) -> save($data);
            } else {
                $res = M('Article') -> add($data);
            }
            if ($res !== fasle) {
                //$this -> success('编辑数据成功', '/Admin/Module/getList');
                $this -> redirect('getList', [], 1, '编辑数据成功');
            } else {
                $this -> error('编辑数据失败');
            }
        }

        $id = I('get.id', '');
        if ($id) {
            $data = M('Article') -> where(array('id' => $id)) -> find();
            if (!$data)
                $this -> error('文章不存在');
        }
        $module = M('Nav') -> where(array('level' => 1)) -> select();
        $this -> assign('data', $data);
        $this -> assign('id', $id);
        $this -> assign('module', $module);
        $this -> display();
    }


    /**
     * 删除内容
     */
    public function delArticle ()
    {
        $id = I('post.id', '');
        if (!$id) {
            $this -> ajaxReturn(array('code' => 403, 'msg' => '数据错误'));
        }
        $data = M('Article') -> field('id') -> where(array('id' => $id)) -> find();
        if (!$data)
            $this -> ajaxReturn(array('code' => 403, 'msg' => '文章不存在'));
        $res = M('Article') -> where(array('id' => $id)) -> delete();
        if ($res) 
            $this -> ajaxReturn(array('code' => 200));
        else
            $this -> ajaxReturn(array('code' => 500, 'msg' => '删除文章失败'));
    }

    /**
     * 隐藏或显示文章
     */
    public function hideArticle ()
    {
        $id = I('post.id', '');
        if (!$id) {
            $this -> ajaxReturn(array('code' => 403, 'msg' => '数据错误'));
        }
        $data = M('Article') -> field('status') -> where(array('id' => $id)) -> find();
        if (!$data)
             $this -> ajaxReturn(array('code' => 403, 'msg' => '文章不存在'));
        $save = [];
        if ($data['status'] == 5)
            $save['status'] = 9;
        else if ($data['status'] == 9)
            $save['status'] = 5;
        else
            $this -> ajaxReturn(array('code' => 403, 'msg' => '数据错误'));
         $res = M('Article') -> where(array('id' => $id)) -> save($save);
         if ($res) 
             $this -> ajaxReturn(array('code' => 200));
         else
             $this -> ajaxReturn(array('code' => 500, 'msg' => '操作文章失败'));
    }

    /**
     * 获取板块文件列表
     */
    public function fileList ()
    {
        $module = M('Nav') -> where(array('level' => 1)) -> select();
        $searchData = $this -> _searchCondition('pic');
        $count = M('File') -> where($searchData['where']) -> count();
        $pagesize = $searchData['condition']['pagesize'];
        $page = getpage($count, $pagesize);
        foreach($searchData['condition'] as $keys=>$vals) {
            $page->parameter[$keys]   =   urlencode($vals);
        }
        $show       = $page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        if(I('get.export_csv') == '1'){//导出
            $limit = '';
        } else {
            $limit = $page->firstRow.','.$page->listRows;
        }
        $data = M('File') -> field('id,file_name,file_size,file_ext,file_type,file_url,create_time')
                            -> where($searchData['where'])
                            -> limit($limit)
                            -> order('create_time desc')
                            -> select();
        $this -> assign('condition',$searchData['condition']);
        $this -> assign('list',$data);
        $this -> assign('page',$show);
        $this -> assign('module', $module);
        $this -> display();
    }

    /**
     * 上传文件（图片编辑）
     */
    public function upFile ()
    {
        $file = $_FILES['file'];       
        $path_parts = pathinfo($file['name']);
        $extension = $path_parts['extension'];
        $name = $path_parts['filename'];
        $fileType = $file['type'];
        $fileType = substr($fileType, 0, strpos($fileType, '/'));
        $size = $file['size'];       
        // $nav1Id = I('post.module_id_one');
        // $nav2Id = I('post.module_id_two');
        // if (!$nav1Id || !$nav2Id) {
        //     header('HTTP/1.1 500 Internal Server Error');
        //     $this -> ajaxReturn(array('msg' => '主板块或分版块未选择'));
        //     return;
        // }
        $fileUrl = $this -> _upFile($fileType, $file, 'material/file/');
        $inserRes = M('File') -> add(array(
                                       'file_name' => $name,
                                       'file_size' => $size,
                                       'file_ext' => $extension,
                                       'file_type' => $fileType,
                                       'file_url' => $fileUrl,
                                       // 'nav1_id' => $nav1Id,
                                       // 'nav2_id' => $nav2Id,
                                       'create_time' => time(),
                                       'create_user' => $_SESSION['user']['username']
                                    ));
        if ($inserRes) {
            $this -> ajaxReturn(array('msg' => '上传成功'));
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            $this -> ajaxReturn(array('msg' => '上传失败3'));
        }  
    }
    /**
     * 删除文件
     */
    public function delFile ()
    {
        if (IS_POST) {
            $id = I('post.fileIds', '');
            if (count($id) < 1) {
                 $this -> ajaxReturn(array('code' => 403, 'msg' => '数据传输错误'));
            }
             $ids = implode(',', $id);
             $data = M('File') -> where(array('id' => array('in', $ids))) -> field('id') -> select();
             if (!$data) {
                 $this -> ajaxReturn(array('code' => 403, 'msg' => '文件不存在'));
             }
             $del = M('File') -> where(array('id' => array('in', $ids))) -> delete();
             if ($del !== false) {
                 $this -> ajaxReturn(array('code' => 200));
             } else {
                 $this -> ajaxReturn(array('code' => 500, 'msg' => '删除失败'));
             }
         } else {
             $this -> ajaxReturn(array('code' => 403, 'msg' => '数据传输错误'));
         }
    }
    /**
     * 获取子版块
     */
    public function getModule ()
    {
        $id = I('post.module_id_one', '');
        if (!$id)
            $this -> ajaxReturn(array('code' => 201));
        $data = M('Nav') -> where(array('pid' => $id)) -> select();
        if ($data === false)
            $this -> ajaxReturn(array('code' => 201));
        $this -> ajaxReturn(array('code' => 200, 'data' => $data));
    }

    public function upLoad ()
    {
        $currentFile = current($_FILES);
        $fileType = $currentFile['type'];
        $fileType = substr($fileType, 0, strpos($fileType, '/'));
        if ($fileType == 'image')
            $path = 'article/pic/';
        else if ($fileType == 'video') 
            $path = 'article/video/';
        else {
            header('HTTP/1.1 500 Internal Server Error');
			$this->error('非法文件上传');
        }
        $src = $this -> _upFile($fileType, $currentFile, $path);
        $data = [];
        $data['state'] = "SUCCESS";
        $data['url'] = $src;
        $data['title'] = $currentFile['name'];
        $data['original'] = $currentFile['name'];
        echo json_encode($data);
    }


    /**
     * 上传文件(图文编辑)
     */
    private function  _upFile ($type, $file, $path)
    {
        $upload = new \Think\Upload();// 实例化上传类
        if ($type == 'image') {
            $upload->maxSize   = 1024 * 1024 * 5;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        } else if ($type == 'video') {
            $upload->maxSize   = 1024 * 1024 * 10;// 设置附件上传大小
            $upload->exts = array('mp4', 'rmvb', 'wmv', 'avi', 'rm');// 设置附件上传类型
        } else {
            $upload->maxSize   = 1024 * 1024 * 5;// 设置附件上传大小
			$upload->exts = array('jpg','jpeg','doc','docx','xls','xlsx','ppt','pptx','pdf');// 设置附件上传类型
        }
        $upload->savePath = $path; // 设置附件上传（子）目录
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->autoSub = true;
		$upload->subName  = array('date','Ym');

		$upload->saveName = 'uniqid';
		// 上传文件
        $info   =   $upload->uploadOne($file);
        if(!$info) {// 上传错误提示错误信息
            header('HTTP/1.1 500 Internal Server Error');
			$this->error($upload->getError());
        }else{// 上传成功 获取上传文件信息
            return C('default_host').'/Uploads/'.$info['savepath'].$info['savename'];
        }
    }


    /**
	 * 搜索条件
	 */
	private function _searchCondition ($type)
	{
		//搜索条件
		$condition['module_id_one'] = I('get.module_id_one'); 
		$condition['keyword'] = trim(I('get.keyword'));		 //搜索关键字，对应搜索类型
		$condition['module_id_two'] = I('get.module_id_two');
        //每页显示多少行	
        if ($type == 'content')
            $condition['pagesize'] = empty(I('get.pagesize')) ? 20 : I('get.pagesize');
        else if ($type == 'pic')			
		    $condition['pagesize'] = empty(I('get.pagesize')) ? 24 : I('get.pagesize');
		if (!empty($condition['keyword'])) {
            $keyword = $condition['keyword'];
            if ($type == 'content') {
                $recombinationWhere['description'] = array('like',"%$keyword%");
                $recombinationWhere['title'] = array('like',"%$keyword%");
                $recombinationWhere['_logic'] = 'or';
                $where['_complex'] = $recombinationWhere;
            } else if ($type == 'pic') {
                $where['file_name'] = array('like',"%$keyword%");
            }
		}
		//时间
		// if(!empty($condition['start_time']) && !empty($condition['end_time'])) {
		// 	$start_time = strtotime(urldecode($condition['start_time']));
		// 	$end_time = strtotime(urldecode($condition['end_time']));
		// 	$where['w.time_start_presale'] = array('egt',$start_time);
		// 	$where['w.time_end_presale'] = array('elt',$end_time);
		// }
		if (!empty($condition['module_id_one'])) {
			$where['nav1_id'] = $condition['module_id_one'];
		}
		if (!empty($condition['module_id_two'])) {
			$where['nav2_id'] = $condition['module_id_two'];
		}
		return array('condition'=>$condition, 'where'=>$where);
	}
         
}