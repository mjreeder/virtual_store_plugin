(function($){
	$(function(){
		var variationColors = {
			'empty':'',
			'baby-blue':'#D2EBFD',
			'baby-pink':'#F7CECD',
			'black':'#000000',
			'blue':'#0F38C4',
			'brown':'#603D11',
			'burnt-sienna':'#CDA163',
			'gray':'#808080',
			'green':'#4EAB4E',
			'khaki':'#CFC29A',
			'orange':'#F0964A',
			'purple':'#BF6FF7',
			'red':'#EA3323',
			'salmon':'#C06E5F',
			'white':'#FFFFFF',
			'yellow':'#FFFE54'
		};

		$('body').on('change', '#pa_color', updateColorIndicator);

		$('.product.has-post-thumbnail .images').append($('<div class="color-indicator" id="color-indicator" style="height:30px; border:1px solid silver; text-transform:uppercase; font-size:10px; text-align:center; line-height:30px; color:silver; user-select:none; cursor:default;">Color Preview</div>'));

		function updateColorIndicator(){
			var value = $(this).val() || 'empty';
			$('#color-indicator').css('background',variationColors[value]);
		}
	});
})(jQuery);