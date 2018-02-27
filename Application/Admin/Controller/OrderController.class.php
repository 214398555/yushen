<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
/**
 * 订单管理
 */
class OrderController extends AdminBaseController
{

    /**
     * 订单管理列表
     */
    public function orderList()
    {
        $id = $_SESSION['user']['id'];
        $condition['pagesize'] = empty(I('get.pagesize')) ? 20 : I('get.pagesize');
        $where = [];
        $where['status'] = array('neq', 30);
        if ($id != 1) { //非管理员用户只能看到
            $where['user_id'] = $id;
        }
        $data = D('Orders')->getOrdersList($where, $condition);
        $this->assign('list', $data);
        $this->display();
    }

    /**
     * 添加订单
     */
    public function add()
    {
        $title = trim(I('post.orderName', ''));
        $content = trim(I('post.orderContent', ''));
        if (empty($title) || empty($content)) {
            $this -> error('订单信息填写不完整');
        }
        $userId = $_SESSION['user']['id'];
        $res = D('Orders')->addOrder(array('title' => $title, 'content' => $content, 'user_id' => $userId));
        if (empty($res))
            $this -> error('添加订单失败');
        else
            $this -> success('添加订单成功');
    }

    /**
     * 修改订单
     */
    public function edit()
    {
        $model = D('Orders');
        $userId = $_SESSION['user']['id'];
        $id = I('post.pid', '');
        $title = trim(I('post.orderName', ''));
        $content = trim(I('post.orderContent', ''));
        if (empty($title) || empty($content) || empty($id)) {
            $this -> error('订单信息填写不完整');
        }
        $orderData = $model->getOrderDetail(array('id' => $id));
        if (empty($orderData)) {
            $this->error('订单不存在');
        }
        if ($userId != 1) { //非管理员
            if ($orderData['user_id'] != $userId) {
                $this->error('非法访问');
            } else if (in_array($orderData['status'], [20, 30])) {
                $this->error('该订单状态不可修改');
            }
        }
        $res = D('Orders')->eidtOrder(array('title' => $title, 'content' => $content), array('id' => $id));
        if (empty($res))
            $this -> error('修改订单失败');
        else
            $this -> success('修改订单成功');


    }

    /**
     * 删除订单
     */
    public function delete()
    {
        $model = D('Orders');
        $id = I('post.id', '');
        $userId = $_SESSION['user']['id'];
        if (empty($id)) {
            $this -> ajaxReturn(array('code' => '201', 'msg' => '非法请求'));
        }
        $orderData = $model->getOrderDetail(array('id' => $id));
        if (empty($orderData)) {
            $this -> ajaxReturn(array('code' => '201', 'msg' => '订单不存在'));
        }
        if ($userId != 1) { //非管理员
            if ($orderData['user_id'] != $userId) {
                $this -> ajaxReturn(array('code' => '201', 'msg' => '非法访问'));
            }
        }
        if (in_array($orderData['status'], [20])) {
            $this -> ajaxReturn(array('code' => '201', 'msg' => '当前订单不可删除'));
        }
        $res = D('Orders')->eidtOrder(array('status' => 30), array('id' => $id));
        if (empty($res))
            $this -> ajaxReturn(array('code' => '201', 'msg' => '删除订单失败'));
        else
            $this -> ajaxReturn(array('code' => '200', 'msg' => '删除订单成功'));

    }

    /**
     * 提交订单
     */
    public function submitOrder()
    {
        $model = D('Orders');
        $id = I('get.id', '');
        $userId = $_SESSION['user']['id'];
        if (empty($id)) {
            $this -> error('非法请求');
        }
        $orderData = $model->getOrderDetail(array('id' => $id));
        if (empty($orderData)) {
            $this->error('订单不存在');
        }
        if ($userId != 1) { //非管理员
            if ($orderData['user_id'] != $userId) {
                $this->error('非法访问');
            }
        }
        if (in_array($orderData['status'], [10, 20])) {
            $this->error('当前订单已经提交');
        }

        $res = D('Orders')->eidtOrder(array('status' => 10), array('id' => $id));
        if (empty($res))
            $this -> error('提交订单订单失败');
        
        //发送邮件
        $mailRes = send_mail(C('THINK_EMAIL.RECIPIENTS'), C('THINK_EMAIL.RECIPIENTS_NAME'), $orderData['title'], $orderData['content']);
        if (empty($mailRes)) {
            D('Orders')->eidtOrder(array('status' => 0), array('id' => $id));
            $this->error('发送邮件失败，请重新提交');
         }
        $this->success('提交订单成功');
    }

    /**
     * 审核订单
     */
    public  function audit()
    {
        $model = D('Orders');
        $id = I('get.id', '');
        $userId = $_SESSION['user']['id'];
        if (empty($id)) {
            $this -> error('非法请求');
        }
        $orderData = $model->getOrderDetail(array('id' => $id));
        if (empty($orderData)) {
            $this->error('订单不存在');
        }
        if ($userId != 1) { //非管理员
            $this->error('非法访问');
        }
        if (in_array($orderData['status'], [0, 20])) {
            $this->error('当前订单已经提交');
        }
        $res = D('Orders')->eidtOrder(array('status' => 20), array('id' => $id));
        if (empty($res))
            $this -> error('审核订单订单失败');
        
         //发送邮件
         $mailRes = send_mail(C('THINK_EMAIL.RECIPIENTS'), C('THINK_EMAIL.RECIPIENTS_NAME'), $orderData['title'], $orderData['content']);
         if (empty($mailRes)) {
            D('Orders')->eidtOrder(array('status' => 10), array('id' => $id));
            $this->error('发送邮件失败，请重新审核');
         }
         $this->success('审核订单成功');
    }

}