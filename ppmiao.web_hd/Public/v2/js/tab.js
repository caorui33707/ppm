/*~模拟单选框~*/
$(function(){
	$("#checkDXB").click(function(){
		var frm = document.forms['form1'];
		var isDXB = $("#isDXB").val();
		if(isDXB==0){
			$("#isDXB").val('1');
			$(this).addClass("moni_radio_checked");
			frm.elements['pwd'].disabled=false;
		}else{
			$("#isDXB").val('0');
			$(this).removeClass("moni_radio_checked");
			frm.elements['pwd'].disabled=true;
		}
	});
	var radio = $(".moni_radio1");
	radio.click(function(){
		radio.removeClass("moni_radio_checked1");
		$(this).addClass("moni_radio_checked1");

	});
	$(".moni_radio").click(function(){
		if($(this).hasClass("moni_radio_checked"))
		{
			$(this).removeClass("moni_radio_checked");
		}else{
			$(this).addClass("moni_radio_checked");
		}
	});
	$(".newActiveTitle h2").click(function(){
		index = $(".newActiveTitle h2").index(this);
		$(this).addClass("active").siblings("h2").removeClass("active");
		$(".newActiveContent").hide().eq(index).show();
	});
	$(".proListBox02Title h2").click(function(){
		index = $(".proListBox02Title h2").index(this);
		$(this).addClass("active").siblings("h2").removeClass("active");
		$(".proListBox02Content").hide().eq(index).show();
	});
	$(".ProDetailsBox02Title h2").click(function(){
		index = $(".ProDetailsBox02Title h2").index(this);
		$(this).addClass("active").siblings("h2").removeClass("active");
		$(".ProDetailsBox02Txt").hide().eq(index).show();
	});
	$(".newsBox01Title h2").click(function(){
		index = $(".newsBox01Title h2").index(this);
		$(this).addClass("selected").siblings("h2").removeClass("selected");
		$(".newsBox01Content").hide().eq(index).show();
	});
	$("li>h5","#questions").bind("click",function(){
		var li=$(this).parent();
		if(li.hasClass("fold")){
			li.removeClass("fold");
			$(this).find("b").removeClass("arrowLeft").addClass("arrowUp");
			li.find(".foldContent").slideDown();
		}else{
			li.addClass("fold");
			$(this).find("b").removeClass("arrowUp").addClass("arrowLeft");
			li.find(".foldContent").slideUp();
		}
	});
});