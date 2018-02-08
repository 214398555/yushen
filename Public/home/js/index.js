//JS
$(document).ready(function(){
	$('.nav_main ul.ul1>li').click(function(){
		$(this).addClass('active').siblings().removeClass('active')
	})
	$('.all_nav_box ul li').click(function(){
		$(this).addClass('active').siblings().removeClass('active')
	})
})
