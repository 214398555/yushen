<?php
namespace Admin\Controller;
use Common\Controller\LoginBaseController;
/**
 * 商城首页Controller
 */
class LoginController extends LoginBaseController{
	/**
	 * 首页
	 */
	public function index(){
        if(IS_POST){
            // 做一个简单的登录 组合where数组条件 
            $map=I('post.');
            $map['password']=md5($map['password']);
            $data=M('FinanceUsers')->where($map)->find();
            if (empty($data)) {
                $this->error('账号或密码错误');
            }else{
                $_SESSION['user']=array(
                    'id'=>$data['id'],
                    'username'=>$data['username'],
                    'realname'=>$data['realname'],
                    'dept_type'=>$data['dept_type'],
                    'duty'=>$data['duty']
                    );
                $this->success('登录成功、前往管理后台',U('Admin/Index/index'));
            }
        }else{
           // $data=check_login() ? $this->success('登录成功、前往管理后台',U('Admin/Index/index')) : '未登录';
            if (check_login()) {
            	$this->success('登录成功、前往管理后台',U('Admin/Index/index'));exit;
            }
            
           /* $assign=array(
                'data'=>$data
                );*/
           // $this->assign($assign);
            $this->display();
        }
	}

    /**
     * 退出
     */
    public function logout(){
        session('user',null);
        $this->success('退出成功、前往登录页面',U('Admin/Login/index'));
    }


    /**
     * geetest submit 提交验证
     */
    public function geetest_submit_check(){
        $data=I('post.');
        if (geetest_chcek_verify($data)) {
            echo '验证成功';
        }else{
            echo '验证失败';
        }
    }

    /**
     * geetest ajax 验证
     */
    public function geetest_ajax_check(){
        $data=I('post.');
        echo intval(geetest_chcek_verify($data));
    }


    /**
     * 用来做测试用
     */
    public function test(){
        
    }


}

