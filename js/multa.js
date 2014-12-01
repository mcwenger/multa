$(document).ready(function(){ 
	
	$('.multa-blocks .rel-made').sortable({
		placeholder: 'ui-state-highlight',
		axis: 'y',
		containment: 'parent',
		handle: '.handle',
		start: function( event, ui ){
			var height = ui.item.height();
			$('.ui-state-highlight').css({'height':height});
		}
	});

	$('body').on('click', '.multa-block label', function(){
		
		var $this = $(this),
			instance = $this.closest('.multa-blocks');
			options = $('.rel-options',instance),
			made = $('.rel-made',instance),
			label = $this.html(),
			id = $this.attr('for'),
			name = $this.attr('data-name'),
			value = $this.attr('data-value'),
			max_entries = made.attr('data-max_entries');
			made_entries = $('label',made).length;
			input = ''
				+'<label class="active" data-value="'+value+'"><span class="handle"></span>'+label+'<input type="hidden" name="'+name+'" id="'+id+'" value="'+value+'"></label>';
		
		if ($this.closest('.multa-block').is('.rel-options')){
			
			if ($this.hasClass('active')){
				
				$this.removeClass('active');
				$('label[data-value="'+value+'"]',made).remove();
			} else {
				// can we add more?
				if (made_entries < max_entries){
					
					$this.addClass('active');
					made.prepend(input);
				} else {
					
					flashFieldError(made, $('.errors',instance), 'You may only select '+max_entries+' entries');
				}
			}
		} else {
			
			$this.remove();
			$('label[data-value="'+value+'"]',options).removeClass('active');
		}
	});
	
	$('body').on('keyup', '.multa-blocks .search input', function(){

		var $this = $(this),
			instance = $this.closest('.multa-blocks');
			options = $('.rel-options',instance),
			made = $('.rel-made',instance),
			id = instance.attr('id'),
			json_prefix = 'multa_options_',
			value = $this.val(),
			json = window[json_prefix+id];
		
		if (value != ''){
			
			$('label',options).hide();
			
			$('label',options).each(function(i){
				
				var label = $(this).html().replace(/<span[^>]*>([^<]*)<\/span>/g,"").toLowerCase(),
					regex = new RegExp(value, 'ig');
				
				if (label.match(regex)){
					$(this).show();
				}
			});
		} else {
			$('label',options).show();
		}
	});
	
	// flash error
	function flashFieldError(object,textObject,text,delay,objectClass){
		
		if (!object) return false;
		
		var objectClass = objectClass ? objectClass : 'flash-field-error',
			delay = delay ? delay : 2000;
		
		object.addClass(objectClass);
		setTimeout(function(){
			object.removeClass(objectClass);
		}, delay);
		
		if (text && typeof text == 'string'){
			if (textObject){
				textObject.html('<span>'+text+'</span>');
				$('span',textObject).fadeIn(300);
				setTimeout(function(){
					$('span',textObject).fadeOut(300, function(){
						$('span',textObject).remove();
					});
				}, delay);
			}
		}
	}
});