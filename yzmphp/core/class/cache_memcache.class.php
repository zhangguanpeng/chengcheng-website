<?php 
/**
 * cache_memcache.class.php    缓存memcache类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-08-24
 */
 
class cache_memcache {

	private $memcache = null;  //memcache对象

	/**
	 * 构造函数
	 * @param	string   $memcache_host
	 * @param	string   $memcache_port
	 * @param	string   $memcache_timeout
	 */	
	public function __construct($memcache_host, $memcache_port, $memcache_timeout) {
		$this->memcache = new Memcache;
		$this->memcache->connect($memcache_host, $memcache_port, $memcache_timeout);
	}


	/**
	 * 获取数据
	 * @param	string   $name
	 * @return  string
	 */
	public function get($name) {
		$value = $this->memcache->get($name);
		return $value;
	}

	
	/**
	 * 设置数据
	 * @param	string   $name
	 * @param	string   $value
	 * @param	string   $ttl
	 * @return  void
	 */
	public function set($name, $value, $ttl = 0) {
		return $this->memcache->set($name, $value, false, $ttl);
	}

	
	/**
	 * 删除数据
	 * @param	string   $name
	 * @return  void
	 */
	public function delete($name) {
		return $this->memcache->delete($name);
	}

	/**
	 * 清空数据
	 * @return  void
	 */
	public function flush() {
		return $this->memcache->flush();
	}
}
?>