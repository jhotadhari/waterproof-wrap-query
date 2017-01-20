/*
	grunt.concat_in_order.declare('init');
*/
/*
	grunt.concat_in_order.declare('group_elements');
	grunt.concat_in_order.require('init');
*/

jQuery(document).ready(function($wpwq) {
	
	// var container = $wpwq('#cmb2-metabox-wpwq_option_metabox');
	var elements = $wpwq('.wpwq-wrapper');
	var classes = [];

	elements.each(function() {

		$wpwq.each( $wpwq( this ).attr( 'class' ).split(' '), function( index, value ){
			if ( value.indexOf( 'wrapper' ) != -1 && value != 'wpwq-wrapper' ) {
				// console.log( value );
				classes.push(value);

			}
		});

	});

	classes = classes.filter(function(item, i, ar){ return ar.indexOf(item) === i; });

	// console.log( classes );
	
	$wpwq.each( classes, function( index, value ){
		$wpwq('.' + value ).wrapAll( '<div class="accordion-wrapper"><div class="accordion-content"></div></div>' );
	});
	
	
	$wpwq( '.accordion-content' ).each(function() {
		$wpwq(this).find('h3').first().detach().insertBefore( this );
	});
	
	

	
	$wpwq( '.accordion-wrapper' ).accordion({
      collapsible: true,
      // disabled: true
    });
	

});