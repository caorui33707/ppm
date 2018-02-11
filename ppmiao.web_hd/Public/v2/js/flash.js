// JavaScript Document
var _ad_count = 0;
$(function(){
	_ad_count = $(".banner ul li").length;
	var sw = 1;
	$(".banner .num a").mouseover(function(){
		sw = $(".num a").index(this);
		myShow(sw);
	});
	function myShow(i){
		$(".banner .num a").eq(i).addClass("cur").siblings("a").removeClass("cur");
		$(".banner ul a").eq(i).stop(true,true).fadeIn(600).siblings("a").fadeOut(600);
	}
	//滑入停止动画，滑出开始动画
	$(".banner").hover(function(){
		if(myTime){
		   clearInterval(myTime);
		}
	},function(){
		myTime = setInterval(function(){
		  myShow(sw)
		  sw++;
		  if(sw==_ad_count){sw=0;}
		} , 3500);
	});
	//自动开始
	var myTime = setInterval(function(){
	   myShow(sw)
	   sw++;
	   if(sw==_ad_count){sw=0;}
	} , 3500);
})