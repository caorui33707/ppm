$(function(){

	var tophtml="<div id=\"izl_rmenu\" class=\"izl-rmenu\"><div class=\"btn btn-wx\"><img class=\"pic\" src=\"images/silderImg05.png\" onclick=\"window.location.href=\'#'\"/></div><div class=\"btn btn-phone\"><div class=\"phone\"></div></div><div class=\"btn btn-question\"></div><div class=\"btn btn-top\"></div></div>";
	$("#top").html(tophtml);
	$("#izl_rmenu").each(function(){
		$(this).find(".btn-wx").mouseenter(function(){
			$(this).find(".pic").fadeIn("fast");
		});
		$(this).find(".btn-wx").mouseleave(function(){
			$(this).find(".pic").fadeOut("fast");
		});
		$(this).find(".btn-phone").mouseenter(function(){
			$(this).find(".phone").fadeIn("fast");
		});
		$(this).find(".btn-phone").mouseleave(function(){
			$(this).find(".phone").fadeOut("fast");
		});
		$(this).find(".btn-top").click(function(){
			$("html, body").animate({
				"scroll-top":0
			},"fast");
		});
	});
	
});




$(function(){
	function showMenu(){
		$(".MCProductListBox01Title h2").click(function(){
			index = $(".MCProductListBox01Title h2").index(this);
			$(this).addClass("active").siblings("h2").removeClass("active");
			$(".MCProductListBox01Content").hide().eq(index).show();
		})
	}
	showMenu()
})// JavaScript