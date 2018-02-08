<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 字典库操作model
 */
class DictModel extends BaseModel{

	/**
	 * 删除数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function deleteData($map){
		$this->where(array($map))->delete();
		return true;
	}
}
