<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 权限规则model
 */
class OrdersModel extends BaseModel{

	/**
     * 获取订单列表
     * 
     */
    public function getOrdersList($where, $condition)
    {
        $count = $this->alias('o')
                      ->join("cmf_finance_users u ON o.user_id = u.id")
                      ->where($where)
                      ->count();
        $pagesize = $condition['pagesize'];
        $page = getpage($count, $pagesize);
        foreach($condition as $keys=>$vals) {
            $page->parameter[$keys]   =   urlencode($vals);
        }
        $show       = $page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        if(I('get.export_csv') == '1'){//导出
            $limit = '';
        } else {
            $limit = $page->firstRow.','.$page->listRows;
        }
        $data = $this->field('o.id,o.title,o.content,o.create_time,o.last_time,o.status,u.username')
                     ->alias('o')
                     ->join("cmf_finance_users u ON o.user_id = u.id")
                     ->where($where)
                     ->limit($limit)
                     ->order('create_time desc')
                     ->select();
        return $data;
    }

    /**
     * 添加订单
     */
    public function addOrder($data)
    {
        if (empty($data)) {
            return false;
        }
        $data['create_time'] = time();
        $data['last_time'] = time();
       
        if ($data = $this->create($data)) {
            return $this->add($data);
        }
        return false;
        
    }

     /**
     * 修改订单
     */
    public function eidtOrder($data, $where)
    {
        if (empty($data)) {
            return false;
        }
        $data['last_time'] = time();
        return $this->where($where)->save($data);
    }


    /**
     * 获取订单数据
     */
    public function getOrderDetail($where)
    {
        return $this->where($where)->find();
    }

}
