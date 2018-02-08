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
        if ($id != 1) { //非管理员用户只能看到
            $where['user_id'] = $id;
        }
        $data = D('Orders')->getOrdersList($where, $condition);
        $this->assign('list', $data);
        $this->display();
    }

}