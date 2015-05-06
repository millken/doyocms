<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syModel {
	public $verifier = null;
	public $addrules = array();
	public $pk;
	public $table;
	public $linker = null;
	public $tbl_name = null;
	public $_db;
	public function __construct()
	{
		if( null == $this->tbl_name )$this->tbl_name = $GLOBALS['G_DY']['db']['prefix'] . $this->table;
		if( '' == $GLOBALS['G_DY']['db_driver_path'] ){
			$GLOBALS['G_DY']['db_driver_path'] = $GLOBALS['G_DY']['sp_drivers_path'].'/'.$GLOBALS['G_DY']['db']['driver'].'.php';
		}
		$this->_db = syClass('db_'.$GLOBALS['G_DY']['db']['driver'], array(0=>$GLOBALS['G_DY']['db']), $GLOBALS['G_DY']['db_driver_path']);
	}
	
	public function notable($value)
	{
		if ($this->_db->showtables($value)==0){return TRUE;}else{return FALSE;}
	}
	
	public function find($conditions = null, $sort = null, $fields = null)
	{
		if( $record = $this->findAll($conditions, $sort, $fields, 1) ){
			return array_pop($record);
		}else{
			return FALSE;
		}
	}

	public function findAll($conditions = null, $sort = null, $fields = null, $limit = null)
	{
		$where = "";
		$fields = empty($fields) ? "*" : $fields;
		if(is_array($conditions)){
			$join = array();
			foreach( $conditions as $key => $condition ){
				$condition = $this->escape($condition);
				$join[] = "{$key} = {$condition}";
			}
			$where = "WHERE ".join(" AND ",$join);
		}else{
			if(null != $conditions)$where = "WHERE ".$conditions;
		}
		if(null != $sort){
			$sort = "ORDER BY {$sort}";
		}else{
			$sort = "ORDER BY {$this->pk}";
		}
		$sql = "SELECT {$fields} FROM {$this->tbl_name} {$where} {$sort}";
		if(null != $limit)$sql = $this->_db->setlimit($sql, $limit);
		return $this->_db->getArray($sql);
	}

	public function escape($value)
	{
		return $this->_db->__val_escape($value);
	}

	public function __val_escape($value){return $this->escape($value);}

	public function create($row)
	{
		
		if(!is_array($row))return FALSE;
		$row = $this->__prepera_format($row);
		if(empty($row))return FALSE;
		foreach($row as $key => $value){
			$cols[] = $key;
			$vals[] = $this->escape($value);
		}
		$col = join(',', $cols);
		$val = join(',', $vals);

		$sql = "INSERT INTO {$this->tbl_name} ({$col}) VALUES ({$val})";
		if( FALSE != $this->_db->exec($sql) ){
			if( $newinserid = $this->_db->newinsertid() ){
				return $newinserid;
			}else{
				$a=$this->find($row, "{$this->pk} DESC",$this->pk);
				return array_pop($a);
			}
		}
		return FALSE;
	}

	public function createAll($rows)
	{
		foreach($rows as $row)$this->create($row);
	}

	public function delete($conditions)
	{
		$where = "";
		if(is_array($conditions)){
			$join = array();
			foreach( $conditions as $key => $condition ){
				$condition = $this->escape($condition);
				$join[] = "{$key} = {$condition}";
			}
			$where = "WHERE ( ".join(" AND ",$join). ")";
		}else{
			if(null != $conditions)$where = "WHERE ( ".$conditions. ")";
		}
		$sql = "DELETE FROM {$this->tbl_name} {$where}";
		return $this->_db->exec($sql);
	}

	public function findBy($field, $value)
	{
		return $this->find(array($field=>$value));
	}

	public function updateField($conditions, $field, $value)
	{
		return $this->update($conditions, array($field=>$value));
	}

	public function findSql($sql)
	{
		return $this->_db->getArray($sql);
	}

	public function runSql($sql)
	{
		return $this->_db->exec($sql);
	}
	public function query($sql){return $this->runSql($sql);}

	public function dumpSql()
	{
		return end( $this->_db->arrSql );
	}

	public function affectedRows()
	{
		return $this->_db->affected_rows();
	}

	public function findCount($conditions = null)
	{
		$where = "";
		if(is_array($conditions)){
			$join = array();
			foreach( $conditions as $key => $condition ){
				$condition = $this->escape($condition);
				$join[] = "{$key} = {$condition}";
			}
			$where = "WHERE ".join(" AND ",$join);
		}else{
			if(null != $conditions)$where = "WHERE ".$conditions;
		}
		$sql = "SELECT COUNT({$this->pk}) AS SP_COUNTER FROM {$this->tbl_name} {$where}";
		$result = $this->_db->getArray($sql);
		return $result[0]['SP_COUNTER'];
	}

	public function __call($name, $args)
	{
		if(in_array($name, $GLOBALS['G_DY']["auto_load_model"])){
			return syClass($name)->__input($this, $args);
		}elseif(!method_exists( $this, $name )){
			syError("方法 {$name} 未定义");
		}
	}

	public function update($conditions, $row)
	{
		$where = "";
		$row = $this->__prepera_format($row);
		if(empty($row))return FALSE;
		if(is_array($conditions)){
			$join = array();
			foreach( $conditions as $key => $condition ){
				$condition = $this->escape($condition);
				$join[] = "{$key} = {$condition}";
			}
			$where = "WHERE ".join(" AND ",$join);
		}else{
			if(null != $conditions)$where = "WHERE ".$conditions;
		}
		foreach($row as $key => $value){
			$value = $this->escape($value);
			$vals[] = "{$key} = {$value}";
		}
		$values = join(", ",$vals);
		$sql = "UPDATE {$this->tbl_name} SET {$values} {$where}";
		return $this->_db->exec($sql);
	}

	public function replace($conditions, $row)
	{
		if( $this->find($conditions) ){
			return $this->update($conditions, $row);
		}else{
			if( !is_array($conditions) )syError('replace方法的条件务必是数组形式！');
			$rows = spConfigReady($conditions, $row);
			return $this->create($rows);
		}
	}

	public function incrField($conditions, $field, $optval = 1)
	{
		$where = "";
		if(is_array($conditions)){
			$join = array();
			foreach( $conditions as $key => $condition ){
				$condition = $this->escape($condition);
				$join[] = "{$key} = {$condition}";
			}
			$where = "WHERE ".join(" AND ",$join);
		}else{
			if(null != $conditions)$where = "WHERE ".$conditions;
		}
		$values = "{$field} = {$field} + {$optval}";
		$sql = "UPDATE {$this->tbl_name} SET {$values} {$where}";
		return $this->_db->exec($sql);
	}

	public function decrField($conditions, $field, $optval = 1)
	{
		return $this->incrField($conditions, $field, - $optval);
	}

	public function deleteByPk($pk)
	{
		return $this->delete(array($this->pk=>$pk));
	}

	private function __prepera_format($rows)
	{
		$columns = $this->_db->getTable($this->tbl_name);
		$newcol = array();
		foreach( $columns as $col ){
			$newcol[$col['Field']] = $col['Field'];
		}
		return array_intersect_key($rows,$newcol);
	}

}

class syPager {
	private $model_obj = null;
	private $pageData = null;
	private $input_args = null;
    public function __input(& $obj, $args){
		$this->model_obj = $obj;
		$this->input_args = $args;
		return $this;
	}

	public function __call($func_name, $func_args){
		if( ( 'findAll' == $func_name || 'findSql' == $func_name ) && 0 != $this->input_args[0]){
			return $this->runpager($func_name, $func_args);
		}elseif(method_exists($this,$func_name)){
			return call_user_func_array(array($this, $func_name), $func_args);
		}else{
			return call_user_func_array(array($this->model_obj, $func_name), $func_args);
		}
	}

	public function getPager(){
		return $this->pageData;
	}

	private function runpager($func_name, $func_args){
		$this->pageData = null;
		$page = $this->input_args[0];
		if($page==0)$page=1;
		$pageSize = $this->input_args[1];
		@list($conditions, $sort, $fields ) = $func_args;
		$total_count = $this->input_args[2];
		if($total_count > $pageSize){
			$total_page = ceil( $total_count / $pageSize );
			$page = min(intval(max($page, 1)), $total_count); // 对页码进行规范运算
			$this->pageData = array(
				"total_count" => $total_count,                                 // 总记录数
				"page_size"   => $pageSize,                                    // 分页大小
				"total_page"  => $total_page,                                  // 总页数
				"first_page"  => 1,                                            // 第一页
				"prev_page"   => ( ( 1 == $page ) ? 1 : ($page - 1) ),         // 上一页
				"next_page"   => ( ( $page == $total_page ) ? $total_page : ($page + 1)),     // 下一页
				"last_page"   => $total_page,                                  // 最后一页
				"current_page"=> $page,                                        // 当前页
				"all_pages"   => array()	                                   // 全部页码
			);
			for($i=1; $i <= $total_page; $i++)$this->pageData['all_pages'][] = $i;
			$limit = ($page - 1) * $pageSize . "," . $pageSize;
			if('findSql'==$func_name)$conditions = $this->model_obj->_db->setlimit($conditions, $limit);
		}
		if('findSql'==$func_name){
			return $this->model_obj->findSql($conditions);
		}else{
			return $this->model_obj->findAll($conditions, $sort, $fields, $limit);
		}
	}
}

class syVerifier {
	private $add_rules = null;
	private $verifier = null;
	private $messages = null;
	private $checkvalues = null;
    public function __input(& $obj, $args){
		$this->verifier = (null != $obj->verifier) ? $obj->verifier : array();
		if(isset($args[1]) && is_array($args[1])){
			$this->verifier["rules"] = $this->verifier["rules"] + $args[1]["rules"];
			$this->verifier["messages"] = isset($args[1]["messages"]) ? ( $this->verifier["messages"] + $args[1]["messages"] ) : $this->verifier["messages"];
		}
		if(is_array($obj->addrules) && !empty($obj->addrules) ){foreach($obj->addrules as $addrule => $addveri)$this->addrules($addrule, $addveri);}
		if(empty($this->verifier["rules"]))syError("无对应的验证规则！");
		return is_array($args[0]) ? $this->checkrules($args[0]) : TRUE;
	}
	public function addrules($rule_name, $checker){
		$this->add_rules[$rule_name] = $checker;
	}

	private function checkrules($values){ 
		$this->checkvalues = $values;
		foreach( $this->verifier["rules"] as $rkey => $rval ){
			$inputval = isset($values[$rkey]) ? $values[$rkey] : '';
			foreach( $rval as $rule => $rightval ){
				if(method_exists($this, $rule)){
					if(TRUE == $this->$rule($inputval, $rightval))continue;
				}elseif(null != $this->add_rules && isset($this->add_rules[$rule])){
					if( function_exists($this->add_rules[$rule]) ){
						if(TRUE == $this->add_rules[$rule]($inputval, $rightval, $values))continue;
					}elseif( is_array($this->add_rules[$rule]) ){
						if(TRUE == syClass($this->add_rules[$rule][0])->{$this->add_rules[$rule][1]}($inputval, $rightval, $values))continue;
					}
				}else{
					syError("未知规则：{$rule}");
				}
				$this->messages[$rkey][] = (isset($this->verifier["messages"][$rkey][$rule])) ? $this->verifier["messages"][$rkey][$rule] : "{$rule}";
			}
		}
		return (null == $this->messages) ? FALSE : $this->messages; 
	}

	private function notnull($val, $right){return $right === ( strlen($val) > 0 );}
	private function minlength($val, $right){return $this->cn_strlen($val) >= $right;}
	private function maxlength($val, $right){return $this->cn_strlen($val) <= $right;}
	private function equalto($val, $right){return $val == $this->checkvalues[$right];}
	private function isgold($val, $right){return $right == ( preg_match("/^[0-9\.]*$/", $val) != 0 );}
	private function isabc($val, $right){return $right == ( preg_match("/^[a-zA-Z0-9]*$/", $val) != 0 );}
	private function isdir($val, $right){return $right == ( preg_match("/^[a-zA-Z0-9_\/\-]*$/", $val) != 0 || $val=='');}
	private function isfile($val, $right){return $right == ( preg_match("/^[a-zA-Z0-9_\-]*$/", $val) != 0 || $val=='');}
	private function isdirfile($val, $right){return $right == ( preg_match("/^[a-zA-Z0-9_\-\.\/]*$/", $val) != 0 || $val=='');}
	private function isabcno($val, $right){return $right == ( preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/", $val) != 0 );}
	private function isabcnocn($val, $right){return $right == ( preg_match("/^(?!_|\')[A-Za-z0-9_\x80-\xff\']+$/", $val) != 0 );}
	private function istime($val, $right){$test = @strtotime($val);return $right == ( $test !== -1 && $test !== false );}
	private function email($val, $right){
		return $right == ( preg_match('/^[A-Za-z0-9]+([._\-\+]*[A-Za-z0-9]+)*@([A-Za-z0-9-]+\.)+[A-Za-z0-9]+$/', $val) != 0 );
	}
	public function cn_strlen($val){$i=0;$n=0;
		while($i<strlen($val)){$clen = ( strlen("快速") == 4 ) ? 2 : 3;
			if(preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/",$val[$i])){$i+=$clen;}else{$i+=1;}$n+=1;}
		return $n;
	}
}

class syCache {

	public $life_time = 3600;
	private $model_obj = null;
	private $input_args = null;
    public function __input(& $obj, $args){
		$this->model_obj = $obj;
		$this->input_args = $args;
		return $this;
	}

	public function __call($func_name, $func_args){
		if( isset($this->input_args[0]) && -1 == $this->input_args[0] )return $this->clear($this->model_obj, $func_name, $func_args);
		$cache_id = get_class($this->model_obj) . md5($func_name);
		if( null != $func_args )$cache_id .= md5(serialize($func_args));
		if( $cache_file = syAccess('r', "sp_cache_{$cache_id}") )return unserialize( $cache_file );
		return $this->cache_obj($cache_id, call_user_func_array(array($this->model_obj, $func_name), $func_args), $this->input_args[0]);
	}

	public function cache_obj($cache_id, $run_result, $life_time = null ){
		if( null == $life_time )$life_time = $this->life_time;
		syAccess('w', "sp_cache_{$cache_id}", serialize($run_result), $life_time);
		if( $cache_list = syAccess('r', 'sp_cache_list') ){
			$cache_list = explode("\n",$cache_list);
			if( ! in_array( $cache_id, $cache_list ) )syAccess('w', 'sp_cache_list', join("\n", $cache_list) . $cache_id . "\n");
		}else{
			syAccess('w', 'sp_cache_list', $cache_id . "\n");
		}
		return $run_result;
	}

	public function clear(& $obj, $func_name, $func_args = null){
		$cache_id = get_class($obj) . md5($func_name);
		if( null != $func_args )$cache_id .= md5(serialize($func_args));
		if( $cache_list = syAccess('r', 'sp_cache_list') ){
			$cache_list = explode("\n",$cache_list);
			$new_list = '';
			foreach( $cache_list as $single_item ){
				if( $single_item == $cache_id || ( null == $func_args && substr($single_item,0,strlen($cache_id)) == $cache_id ) ){
					syAccess('c', "sp_cache_{$single_item}");
				}else{
					$new_list .= $single_item. "\n";
				}
			}
			syAccess('w', 'sp_cache_list', substr($new_list,0,-1));
		}
		return TRUE;
	}

	public function clear_all(){
		if( $cache_list = syAccess('r', 'sp_cache_list') ){
			$cache_list = explode("\n",$cache_list);
			foreach( $cache_list as $single_item )syAccess('c', "sp_cache_{$single_item}");
			syAccess('c', 'sp_cache_list');
		}
		return TRUE;
	}
}