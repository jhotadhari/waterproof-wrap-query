<?php
/*
	grunt.concat_in_order.declare('Wpwq_wrapper_single');
	grunt.concat_in_order.require('Wpwq_wrapper');
*/



class Wpwq_wrapper_single {

	protected $name;
	protected $args;
	protected $args_single;
	protected $inner;                            
		
	function __construct( $name = null, $query_single_obj = null , $args = null, $args_single = null , $single_count = null) {
		$this->set_name( $name );
		$this->set_args( $args, $args_single, $single_count );
	}
	protected function set_name( $name ){
		$this->name = $name;
	}
	public function get_name(){
		return $this->name;
	}		
	protected function set_inner( $query_single_obj ){
		$this->inner = '';
	}
		
	public function get_inner(){
		return $this->inner;
	}
	protected function set_args( $args, $args_single, $single_count = null ){
		$this->args = ( null !== $args ? $args : array() );
		$this->args_single = ( null !== $args_single ? $args_single : array() );
		if ( $single_count !== null ){
			$this->args_single['single_count'] = $single_count;
		}
	}
	public function get_args(){
		return $this->inner;
	}
}


?>