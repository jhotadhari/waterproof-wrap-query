// wpwq_widget

jQuery(document).ready(function($){

	// when widget hovered, trigger wrapper
	function wpwq_hoverWidget(){
	
		$('.widget.wpwq-widget-menu ul.current a').hover(
			function(e) {	// handlerIn
				
				var id = getId( $( this ) );
				var unique = getUnique( $( this ) );
				if ( id === null || unique === null ) { return; }
				
				var single = $('.wpwq-query-wrapper.' + unique + '-unique').find('.post-' + id);
						
				if ( single.hasClass('hovertrigger') ){

					single.addClass('hover');


				} else {

					single.find('.hovertrigger').addClass('hover');

				}
				
			}, function(e) {	// handlerOut
				
				var id = getId( $( this ) );
				var unique = getUnique( $( this ) );
				if ( id === null || unique === null ) { return; }
				
				var single = $('.wpwq-query-wrapper.' + unique + '-unique').find('.post-' + id);
				
				if ( single.hasClass('hovertrigger') ){
					single.removeClass('hover');

				} else {
					single.find('.hovertrigger').removeClass('hover');

				}
			}
		);
		
		function getId( el ){
			var id = /(post-)\w+/g.exec( el.parent().attr('class') );
			return id !== null ? id[0].replace('post-', '') : null;
		}
		
		function getUnique( el ){
			var unique = /(unique-)\w+/g.exec( el.parent().attr('class') );
			return unique !== null ? unique[0].replace('unique-', '') : null;
		}
	}
	wpwq_hoverWidget();
	
	// when wrapper hovered, trigger widget
	function wpwq_hoverWrapper(){
		
		$('.wpwq-query-wrapper .hovertrigger').hover(
			function(e) {	// handlerIn
	
				var id = getId( $( this ) );
				// alert(id);
				var unique = getUnique( $( this ) );
				// alert(unique);
				if ( id === null || unique === null ) { return; }
				
				$('.widget.wpwq-widget-menu ul.current li.unique-' + unique + '.post-' + id + ' a').addClass('hover');
				
			}, function(e) {	// handlerOut
				
				var id = getId( $( this ) );
				var unique = getUnique( $( this ) );
				if ( id === null || unique === null ) { return; }
				
				$('.widget.wpwq-widget-menu ul.current li.unique-' + unique + '.post-' + id + ' a').removeClass('hover');
				
			}
		);

		function getId( el ){
			var id;
			if (el.hasClass('wpwq-query-wrapper-item') ){
				id = /(post-)\w+/g.exec( el.attr('class') );
			} else {
				id = /(post-)\w+/g.exec( el.parents('.wpwq-query-wrapper-item').attr('class') );
			}
			return id !== null ? id[0].replace('post-', '') : null;
		}
		
		function getUnique( el ){
			var unique = /\w+(?=-unique)/g.exec( el.parents('.wpwq-query-wrapper').attr('class') );
			return unique !== null ? unique[0] : null;
		}
		
	}
	wpwq_hoverWrapper();
	
});