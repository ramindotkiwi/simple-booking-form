<?php
/* Form handler and validations using PDO and OOP*/
/* I do need to have some sort of error handling for controller to avoid bad data Entries*/

class Form{

	public $error;

	/*
		validate inputs
	*/
	public function validate($array){
		
		//check rules function exists
		if( method_exists($this, 'rules') ) $rules = $this->rules(); else return false; 
		
		foreach($array as $pkey=>$pval):

			//exit if $key not exists in $rules
			if( !isset($rules[$pkey]) ) return false;

			foreach($rules[$pkey] as $rkey=>$rval):
				
				switch($rkey):
					case 'allowEmpty':
						if( $rval == false && (Empty($pval)&&$pval != '0') ){
							$this->error = 'Empty element.';
							return false;
						}
					break;
					case 'maxLength':
						if( strlen($pval) > (int)$rval ){
							$this->error = 'Maximum length error.';
							return false;
						}
					break;
 				endswitch;
				
			endforeach;
		endforeach;

		return true;
	}

}