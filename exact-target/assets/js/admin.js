jQuery(function($) {
	$('.xt_add_mailing_list').on('click', function(){ exact_target.add_mailing_list($(this)); });
	$('body').on('click', '.xt_remove_mailing_list', function(){ exact_target.remove_mailing_list($(this)); });
});

exact_target = {
	add_mailing_list: function(elem) {

		var index = 0;
		jQuery('.xt_mailing_lists li input').each(function(){
			jQuery(this).attr('name', 'xt_mailing_lists['+index+']' );
			jQuery(this).attr('id', 'xt_mailing_lists['+index+']' );
			index++;
		});

		jQuery('.xt_add_mailing_list').parents('li').before('<li><input type="text" id="xt_mailing_lists['+index+']" name="xt_mailing_lists['+index+']" value="" ><a href="#" class="xt_remove_mailing_list">(remove)</a></li>');
	},
	remove_mailing_list: function(elem) {
		$li = elem.parents('li');
		$input = $li.find('input');

		if($input.prop('disabled') === true){
			$input.prop('disabled', false);
			$li.find('.xt_remove_mailing_list').html('(remove)');
		} else {
			$input.prop('disabled', true);
			$li.find('.xt_remove_mailing_list').html('(undo)');
		}
	},
};