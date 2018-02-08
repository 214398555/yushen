<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 权限规则model
 */
class FinanceAuthGroupModel extends BaseModel{

	/**
	 * 传递主键id删除数据
	 * @param  array   $map  主键id
	 * @return boolean       操作是否成功
	 */
	public function deleteData($map){
		$this->where($map)->delete();
		$group_map=array(
			'group_id'=>$map['id']
			);
		// 删除关联表中的组数据
		$result=D('FinanceAuthGroupAccess')->deleteData($group_map);
		return $result;
	}
	
	/**
	 * 获取用户组权限
	 */
	public function getAuthGroup($uid) {
		$authGroups = array();
		$data = $this->field('ag.rules')
						->alias('ag')
						->join('cmf_finance_auth_group_access aga ON aga.group_id=ag.id', 'LEFT')
						->where('aga.uid='.$uid)
						->select();
		if (count($data)>0) {
			$authGroup = '';
			foreach ($data as $key=>$val) {
				$authGroup .= $val['rules'].',';
			}	
			//权限组Id		
			$authGroups = array_unique(explode(',', rtrim($authGroup, ',')));
			//权限组规则id
			$where['id']=array('in',$authGroups);
			$authRule = M('FinanceAuthRule')->field('name')->where($where)->select();
			//具体权限规则
			$authRules = array();
			foreach ($authRule as $val) {
				$authRules[] = $val['name'];
			}
			
			return $authRules;
		}
		return false;
	}



}
