<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_sys_class('form','',0);

class content_form {
	
	public $modelid;

    function __construct($modelid) {
		$this->modelid = $modelid;
    }

	public function content_add() {
		$modelinfo = $this->get_modelinfo();
		$string = getcache($this->modelid.'_model_string', 2);
		if($string === false){
			define('DATETIME', 1);  //因前面已加载过js
			$string = '';
			foreach($modelinfo as $val){
				$fieldtype = $val['fieldtype'];
				if($fieldtype == 'input' || $fieldtype == 'number'){
						$required = $val['isrequired'] ? ' required="required" ' : '';
						$string .= $this->tag_start($val['name']).'<input type="text" class="input-text" value="'.$val['defaultvalue'].'"  '.$required.' name="'.$val['field'].'" placeholder="'.$val['tips'].'">'.$this->tag_end();		   
				}elseif($fieldtype == 'textarea'){
						$string .= $this->tag_start($val['name']).'<textarea name="'.$val['field'].'" class="textarea"  placeholder="'.$val['tips'].'" >'.$val['defaultvalue'].'</textarea>'.$this->tag_end();
				}elseif($fieldtype == 'select'){
						$string .= $this->tag_start($val['name']).'<span class="select-box">'.form::select($val['field'],'',string2array($val['setting'])).'</span>'.$this->tag_end();
				}elseif($fieldtype == 'radio' || $fieldtype == 'checkbox'){
						$string .= $this->tag_start($val['name']).form::$fieldtype($val['field'],$val['defaultvalue'],string2array($val['setting'])).$this->tag_end();
				}elseif($fieldtype == 'datetime'){
						$string .= $this->tag_start($val['name']).form::datetime($val['field'], '', $val['setting']).$this->tag_end();
				}else{
						$string .= $this->tag_start($val['name']).form::$fieldtype($val['field']).$this->tag_end();
				}
				
			}
			setcache($this->modelid.'_model_string', $string, 2);
		}		
		return $string;
	}
	
	
	public function content_edit($data) {
		$modelinfo = $this->get_modelinfo();
		define('DATETIME', 1);  //因前面已加载过js
		$string = '';
		foreach($modelinfo as $val){
			$fieldtype = $val['fieldtype'];
			if($fieldtype == 'input' || $fieldtype == 'number'){
					$required = $val['isrequired'] ? ' required="required" ' : '';
					$string .= $this->tag_start($val['name']).'<input type="text" class="input-text" value="'.$data[$val['field']].'" '.$required.' name="'.$val['field'].'" placeholder="'.$val['tips'].'">'.$this->tag_end();		   
			}elseif($fieldtype == 'textarea'){
					$string .= $this->tag_start($val['name']).'<textarea name="'.$val['field'].'" class="textarea"  placeholder="'.$val['tips'].'" >'.$data[$val['field']].'</textarea>'.$this->tag_end();
			}elseif($fieldtype == 'select'){
					$string .= $this->tag_start($val['name']).'<span class="select-box">'.form::select($val['field'],$data[$val['field']],string2array($val['setting'])).'</span>'.$this->tag_end();
			}elseif($fieldtype == 'radio' || $fieldtype == 'checkbox'){
					$string .= $this->tag_start($val['name']).form::$fieldtype($val['field'],$data[$val['field']],string2array($val['setting'])).$this->tag_end();
			}elseif($fieldtype == 'datetime'){
					$string .= $this->tag_start($val['name']).form::datetime($val['field'],$data[$val['field']], $val['setting']).$this->tag_end();
			}else{
					$string .= $this->tag_start($val['name']).form::$fieldtype($val['field'],$data[$val['field']]).$this->tag_end();
			}
		}		
		return $string;
	}
	
	
	public function tag_start($tip) {
		return '<div class="row cl"><label class="form-label col-xs-4 col-sm-2">'.$tip.'：</label><div class="formControls col-xs-8 col-sm-9">';
	}
	

	public function tag_end() {
		return '</div></div>';
	}

	
	public function get_modelinfo() {
		$modelinfo = getcache($this->modelid.'_model', 2);
		if($modelinfo === false){
			if(!D('model')->where(array('modelid' => $this->modelid))->find()) showmsg('模型不存在！');
			$modelinfo = D('model_field')->where(array('modelid' => $this->modelid, 'disabled' => 0))->order('listorder ASC')->select();
			setcache($this->modelid.'_model', $modelinfo, 2);
			delcache($this->modelid.'_model_string');
		}
		return $modelinfo;
	}
}