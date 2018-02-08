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
        $count = $this->where($where)->count();
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
        $data = $this->field('id,title,content,create_time,last_time,status')
                            -> where($where)
                            -> limit($limit)
                            -> order('create_time desc')
                            -> select();
        return $data;
    }


}
