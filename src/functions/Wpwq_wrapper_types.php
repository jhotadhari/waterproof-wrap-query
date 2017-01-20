<?php
/*
	grunt.concat_in_order.declare('Wpwq_wrapper_types');
	grunt.concat_in_order.require('init');
*/

/**
* Wpwq_wrapper_types class
*
* Class for the wpwq_wrapper_types object
* Object to manage the available display types
*
*/
class Wpwq_wrapper_types {
	protected $types = array();
	
	public function add_type( $arr ){
		foreach ( $arr as $key => $val ){
			$types = $this->types;

			$types[$key] = $val;

		}
		$this->types = $types;
	}
	
	public function get_types( $return_type = null, $wrapper_type = 'all' ){
		if ( $return_type == 'types_string' ){
			// return 'types_string'
			$types_string = '';
			
			// add each type to string
			if ( gettype($this->types) == 'array' ){
				foreach ( $this->types as $key => $val ){
					
					if ( $wrapper_type == 'all' || $wrapper_type == $key){
						$types_string .= $key . ', ';

					}
					
				}
				// remove last seperator
				if ( strlen($types_string) > 0 ){
					$types_string = rtrim ( $types_string, ', ' );
				}
			}
			return $types_string;

		} elseif ( $return_type == 'array_key_val' ){
			// return 'array_key_val'
			$array_key_val = array();
			
			// add each type to array
			if ( gettype($this->types) == 'array' ){
				foreach ( $this->types as $key => $val ){
					if ( $wrapper_type == 'all' || $wrapper_type == $key){
						$array_key_val[$key] = $key;
					}
				}
			}
			return $array_key_val;
			
		} else {
			$full_arr = array();
			
			foreach ( $this->types as $key => $val ){
				if ( $wrapper_type == 'all' || $wrapper_type == $key){
					$full_arr[$key] = $val;
				}
			}
			return $full_arr;
		}
	}
}

/**
* initialize the wpwq_wrapper_types object
*/
function wpwq_init_wrapper_types(){
	global $wpwq_wrapper_types;
	$wpwq_wrapper_types = new Wpwq_wrapper_types();
}
add_action( 'admin_init', 'wpwq_init_wrapper_types' , 2);
add_action( 'init', 'wpwq_init_wrapper_types' , 2);

?>