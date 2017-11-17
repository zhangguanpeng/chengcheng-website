<?php
/**
 * db_mysqli.class.php	 MYSQLI数据库类
 *  
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-03-03  
 */

class db_mysqli{
	
	public  static $link = null;       //数据库连接资源句柄
	private $table;                    //数据库表名 
	private $key = array();            //存放条件语句
	private $lastsql = '';             //存放sql语句
		
		
	/**
	 * 初始化链接数据库，并给表名赋值
	 */
	public function __construct($tablename){
		if(self::$link == null){
			self::$link = self::connect();
		}		
		$this->table = C('db_prefix').$tablename;
	}
	

	/**
	 * 真正开启数据库连接
	 * 			
	 * @return object mysqli
	 */	
	public static function connect(){
		self::$link = @new mysqli(C('db_host'), C('db_user'), C('db_pwd'), C('db_name'), C('db_port'));	
		if(mysqli_connect_errno()){
			self::$link = null;
			application::halt("Can not connect to MySQL server!");
		}                              
		self::$link->query("SET names utf8, sql_mode=''"); 	
		return self::$link;
	}

		
	/**
	 * 内部方法：过滤函数 把用户传入变量中的引号加上转义字符
	 * @param $value
	 * @return string
	 */	
	private function safe_data($value){
		if(!MAGIC_QUOTES_GPC) $value = addslashes($value);
		return $value;
	}
	
	/**
	 * 内部方法：过滤数组，把不是表单的元素过滤掉
	 * @param $arr
	 * @param $primary 是否过滤主键
	 * @return array
	 */
	private function del_arr($arr, $primary = true){
        $re = array();		
		if(!is_array($arr)) return false;		
		$fields = $this->get_fields();		
		foreach ($arr as $k => $v){
			if(in_array($k,$fields)){
				$re[$k] = $v;
			}
		}
		if($primary){
			$p = $this->get_primary();
			if(isset($re[$p])) unset($re[$p]);
		}
		return $re;
	}

	
	/**
	 * 内部方法：数据库查询执行方法
	 * @param $sql 要执行的sql语句
	 * @return 查询资源句柄
	 */
	private function execute($sql) {
		$this->lastsql = $sql;
		$res = self::$link->query($sql) or $this->geterr($sql);
		$this->key = array();
		debug::addmsg($sql, 1);
		return $res;
	}	
	

	
	/**
	 * 组装where条件，将数组转换为SQL语句
	 * @param array $where  要生成的数组,参数可以为数组也可以为字符串，建议数组。
	 * return string
	 */
	public function where($arr = ''){
		if(empty($arr)) {
		   return $this;
		}		
		if(is_array($arr)) {
			$args = func_get_args();
			$str = '(';
			foreach ($args as $v){
				foreach($v as $k => $value){
					$value = $this->safe_data($value);
					if(!strpos($k,'>') && !strpos($k,'<') && !strpos($k,'=') && substr($value, 0, 1) != '%' && substr($value, -1) != '%'){    //where(array('age'=>'22'))
						$str .= $k." = '".$value."' AND ";
					}else if(substr($value, 0, 1) == '%' || substr($value, -1) == '%'){	//where(array('name'=>'%php%'))
						$str .= $k." LIKE '".$value."' AND "; 
					}else{
						$str .= $k."'".$value."' AND ";      //where(array('age>'=>'22'))
					}
				}
				$str = rtrim($str,' AND ').')';
				$str .= ' OR (';
			}
			$str = rtrim($str,' OR (');
			$this->key['where'] = $str;
			return $this;
		}else{
			$this->key['where'] = str_replace('yzmcms_', C('db_prefix'), $arr);	
			return $this;
		}
	}
	
	
	/**
	 * 内部方法：查询部分，开始组装SQL
	 * @param $name
	 * @param $value
	 * @return object
	 */
	public function __call($name, $value){
		if(in_array($name, array('field', 'order', 'limit', 'group', 'having'))){
			$this->key[$name] = $value[0];
			return $this;
		}else{
			$this->geterr($name.'方法不存在！'); 
		}
	}
	
	
	/**
	 * 执行添加记录操作
	 * @param $data         要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $filter       第二个参数选填 如果为真值[1为真] 则开启实体转义
	 * @param $primary 		是否过滤主键
	 * @return int/boolean  成功：返回自动增长的ID，失败：false
	 */
	public function insert($data, $filter = false, $primary = true){
		if(!is_array($data)) {
		    $this->geterr('insert方法：传入的数据必须是以数组形式！'); 
			return false;
		}
		$data = $this->del_arr($data, $primary);
		$clo = '';
		$vs = '';
		if(!$filter){
			foreach($data as $k => $v){
				$clo .= $k.',';
				$vs .= "'".$this->safe_data($v)."'".",";
			}
		}else{
			foreach($data as $k => $v){
				$clo .= $k.',';
				$vs .= "'".htmlspecialchars($this->safe_data($v))."'".",";
			}		
		}
		$clo = rtrim($clo,',');
		$vs = rtrim($vs,',');
		$sql = 'INSERT INTO `'.$this->table.'`('.$clo.') VALUES ('.$vs.')';
		$this->execute($sql);
		return self::$link->insert_id;
	}
	
	/**
	 * 执行删除记录操作
	 * @param $where 		参数为数组，删除数据条件,不充许为空。
	 * @param $many 		是否删除多个，多用在批量删除，取的主键在某个范围内，例如 $admin->delete(array(3,4,5), true);
	 *                      结果为： DELETE FROM `yzmcms_admin` WHERE id IN (3,4,5);
	 *
	 * @return int          返回影响行数
	 */
	public function delete($where, $many = false){	
		if(is_array($where) && !empty($where)){
            if(!$many){
				$this->where($where);   
			}else{
				$sql = '';
				foreach($where as $v){
					$sql .= intval($v).',';
				}
				$sql = rtrim($sql, ',');
				$this->key['where'] = $this->get_primary().' IN ('.$sql.')';
			}			
			$sql = 'DELETE FROM `'.$this->table.'` WHERE '.$this->key['where'];
		}else{
			$this->geterr('delete方法：没有给定条件或条件不是数组！'); 
			return false;
		}
		$this->execute($sql);
		return self::$link->affected_rows;
	}

	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='myname',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'php','password'=>'123456')]						
	 * @param $where 		更新数据时的条件,参数为数组类型
	 * @param $filter 		第三个参数选填 如果为真值[1为真] 则开启实体转义
	 * @param $primary 		是否过滤主键
	 * @return int          返回影响行数
	 */	
	public function update($data, $where = '', $filter = false, $primary = true){	
		$this->where($where);
		if(is_array($data)){
			$data = $this->del_arr($data, $primary);				
			$value = '';
			if(!$filter){
				foreach($data as $k => $v){
					$value .= $k." = "."'".$this->safe_data($v)."'".",";
				}
			}else{
				foreach($data as $k => $v){
					$value .= $k." = "."'".htmlspecialchars($this->safe_data($v))."'".",";
				}		
			}		
			$value=rtrim($value,',');				
		}else{
			$value=$data;		
		}			
		$sql = 'UPDATE `'.$this->table.'` SET '.$value.' WHERE '.$this->key['where'];
		$this->execute($sql);
		return self::$link->affected_rows;	
	}

	
	/**
	 * 获取查询多条结果，返回二维数组
	 * @return array
	 */	
	public function select(){
        $rs = array();		
		$field = isset($this->key['field']) ? str_replace('yzmcms_', C('db_prefix'), $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? $this->key['join'] : '';
		$where = isset($this->key['where']) ? ' WHERE '.$this->key['where'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = isset($this->key['limit']) ? ' LIMIT '.$this->key['limit'] : '';				
		
		$sql = 'SELECT '.$field.' FROM `'.$this->table.'`'.$join.$where.$group.$having.$order.$limit;
		$selectquery = $this->execute($sql);
        while($data = $selectquery->fetch_assoc()){
	      $rs[] = $data;
	    }
	    return $rs;
	}

	/**
	 * 获取查询一条结果，返回一维数组
	 * @return array or null
	 */	
	public function find(){
		$field = isset($this->key['field']) ? str_replace('yzmcms_', C('db_prefix'), $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? $this->key['join'] : '';
		$where = isset($this->key['where']) ? ' WHERE '.$this->key['where'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = ' LIMIT 1';		
		
		$sql = 'SELECT '.$field.' FROM `'.$this->table.'`'.$join.$where.$group.$having.$order.$limit;
		$findquery = $this->execute($sql);
	    return $findquery->fetch_assoc();
	}
		
	/**
	 * 链接查询
	 * @param $join 	string SQL语句，如yzmcms_admin ON yzmcms_admintype.id=yzmcms_admin.id
	 * @param $type 	可选参数,默认是inner
	 * @return object
	 */	
	public function join($join, $type = 'INNER'){
		$join = str_replace('yzmcms_', C('db_prefix'), $join);    //如果存在表前缀，则开启此项	 
        $this->key['join'] = stripos($join,'JOIN') !== false ? $join : ' '.$type.' JOIN '.$join;
	    return $this;
	}	
	
	/**
	 * 用于调试程序，输入SQL语句
	 * @param $echo 	可选参数,默认是输出
	 * @return string
	 */	
	public function lastsql($echo = true){
		$sql = $this->lastsql;
		if($echo)
			echo '<div style="font-size:14px;text-align:left; border:1px solid #9cc9e0;line-height:25px; padding:5px 10px;color:#000;font-family:Arial, Helvetica,sans-serif;"><p><b>SQL：</b>'.$sql.'<p></div>'; 	
		else
			return $sql;		
	}

	/**
	 * 自定义执行SQL语句
	 * @param  $sql sql语句
	 * @return （self::$link->query返回值）
	 */		
	public function query($sql = ''){
		 $sql = str_replace('yzmcms_', C('db_prefix'), $sql);       //如果存在表前缀，则开启此项	 
         return $this->execute($sql);	 
	}


	/**
	 * 返回一维数组，与query方法结合使用
	 * @param  resource
	 * @return array
	 */		
    public function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		if(!is_object($query))   return false;
		return $query->fetch_array($result_type);
	}	

	/**
	 * 返回二维数组，与query方法结合使用
	 * @param  resource
	 * @return array
	 */		
    public function fetch_all($query, $result_type = MYSQLI_ASSOC) {
		if(!is_object($query))   return false;
		$arr = array();
		while($data = $query->fetch_array($result_type)) {
			$arr[] = $data;
		}
		return $arr;
	}
	
	
	/**
	 * 获取错误提示
	 */		
	private function geterr($msg = ''){
		if(APP_DEBUG){
			echo '<div style="font-size:14px;text-align:left; border:1px solid #9cc9e0;line-height:25px; padding:5px 10px;color:#000;font-family:Arial, Helvetica,sans-serif;"><b> Error : </b>'. $msg .' <br /><b> MySQL Errno : </b>'. self::$link->errno .' <br /> <b>MySQL Error : </b> <span>'. self::$link->error.'</span> <br /><a href="http://www.yzmcms.com/" target="_blank" style="color:red">Need Help?</a></div>';
			exit;
		}else{
			error_log('<?php exit;?> MySQL Error: '.date('m-d H:i:s').' | Errno: '.self::$link->errno.' | Error: '.self::$link->error.' | SQL: '.$msg."\r\n", 3, YZMPHP_PATH.'cache/error_log.php');
			application::halt('SQL Error!');
			exit;
		}
	}

	
	/**
	 * 返回记录行数。
	 * @return int 
	 */	
	public function total(){
		$join = isset($this->key['join']) ? $this->key['join'] : '';
		$where = isset($this->key['where']) ? ' WHERE '.$this->key['where'] : '';		
		$sql = 'SELECT COUNT(*) AS total FROM `'.$this->table.'`'.$join.$where;
		$totquery = $this->execute($sql);
		$total = $totquery->fetch_assoc();   
        return $total['total'];		
	}

	
	/**
	 * 获取数据表主键
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_primary($table = '') {
		$table = empty($table) ? $this->table : $table;
		$sql = "SHOW COLUMNS FROM `$table`";
		$r = self::$link->query($sql) or $this->geterr($sql);
		while($data = $r->fetch_assoc()){
			if($data['Key'] == 'PRI') break;
		}
		return $data['Field'];
	}
	

	/**
	 * 获取数据库 所有表
	 * @return array 
	 */		
	public function list_tables() {
		$tables = array();
		$listqeury = $this->execute('SHOW TABLES');
		while($r = $this->fetch_array($listqeury, MYSQLI_NUM)) {
			$tables[] = $r[0];
		}
		return $tables;
	}	


	/**
	 * 获取表字段
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_fields($table = '') {
		$table = empty($table) ? $this->table : $table;
		$fields = array();
		$sql = "SHOW COLUMNS FROM `$table`";
		$r = self::$link->query($sql) or $this->geterr($sql);
		while($data = $r->fetch_assoc()){
			$fields[] = $data['Field'];
		}		
		return $fields;
	}

	
	/**
	 * 检查表是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	public function table_exists($table) {
		$tables = $this->list_tables();
		return in_array($table, $tables);
	}


	/**
	 * 检查字段是否存在
	 * @param $table 表名
	 * @param $field 字段名
	 * @return boolean
	 */
	public function field_exists($table, $field) {
		$fields = $this->get_fields($table);
		return in_array($field, $fields);
	}
	
	
	/**
	 * 返回 MySQL 服务器版本信息
	 * @return string 
	 */	
	public function version(){
	    return self::$link->server_info;	
	}
	

	/**
	 * 关闭数据库连接
	 */	
	public function close(){
	    return self::$link->close();
	}
	
}