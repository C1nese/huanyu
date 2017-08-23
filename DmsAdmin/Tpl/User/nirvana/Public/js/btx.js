$(function(){
	$(window).scroll(function() {
        if ($(window).scrollTop() >= 240 && $(window).width() < 992 && !$('#btx_nav').hasClass('x-navigation-open')) {
        	if($('#btx_top').hasClass('x-navigation-open')){
        		$('#btx_top').removeClass('x-navigation-open');
        	}
        	$('#btx').show();         
        }else {
    		$('#btx').hide();
        }
    });
	$('#btx_control').click(function(){
		$('#btx_nav').addClass('x-navigation-open');
		$(window).scrollTop('0');
		$('#btx').hide();
	});
});