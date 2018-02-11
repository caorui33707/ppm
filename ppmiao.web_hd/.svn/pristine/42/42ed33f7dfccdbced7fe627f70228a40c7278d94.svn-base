// 未登录
$(document).ready(function() {
	if (login == false) {
		$(".watering-day").css("opacity", "0");
		$(".watering-time").css("opacity", "0");
	}

	if(hdStatus > 0){
		activityFinish();
	}

	setDate();
	//未登录
	$("body").on("touchstart", "#closeRules", function() {
		closeDialog();
	});


	$(".buy").click(function() {

		if(hdStatus > 0){
			dialogNotice('活动已经结束了');
		} else {
			go2App();
		}
	});


	$("#54").click(function() {
		hot(54);
	});
    $("#51").click(function() {
        hot(51);
    });

	$("body").on("touchstart", "#closeBuy", function() {
		closeDialog();
	});

	$("body").on("touchstart", "#index_now", function() {
		closeDialog();
	});


	$("body").on("touchstart", "#notice", function() {
		closeDialog();
	});

	//浇水
	var checkInFlag = true;

	$(".checkIn").click(function() {
		if (!login) {
			notLogin();
			return false;
		} else {

			if (!checkInFlag) return false;
			checkInFlag = false;

			$.ajax({
				type: "post",
				async: false,
				data: {
					'mobile': login
				},
				url: "/hd/dailySignin.html",
				dataType: "json",
				success: function(json) {
					checkInFlag = true;
					if (json.status == 0) {
						notLogin();
					} else if (json.status == 2) {

						$('.watering-day').append('<div class="cannot-checkIn"></div>');

						date[date.length-1] = 1;

						setDate();

 		        		if(_days == 7) {
 		        			changeStatusS(66);
 		        		} else if(_days == 12){
 		        			changeStatusS(67);
 		        		}
 		        		//5.1
 		        		if(currenDay == 1){
 		        			changeStatusS(68);
 		        		}
 		        		//5.4
 		        		if(currenDay == 4){
 		        			changeStatusS(69);
 		        		}

						//领取天数加1
						var _days = parseInt($('.watering-time').html()) + 1;
						$('.watering-time').empty().html(parseInt(_days));

						var content = '<div class="water-pic dialog-main"><div class="closeWater" id="water"></div></div>'
						openDialog(content);
						setTimeout('closeDialog()', 3500);

					} else {
						dialogNotice(json.info);
					}
					return false;
				}
			});



		}


	});
	$("body").on("touchstart", "#water", function() {
		closeDialog();
	});
	$("body").on("touchstart", "#hot_pic", function() {
		closeDialog();
	});


	$("body").on("touchstart", "#noticeButton", function() {
		//go2Login();
	});

	tabs(".investment_title", "on", ".investment_con");
});

/*tab标签切换*/
function tabs(tabTit, on, tabCon) {
	$(tabCon).each(function() {
		$(this).children().eq(0).show();

	});
	$(tabTit).each(function() {
		$(this).children().eq(0).addClass(on);
	});
	$(tabTit).children().click(function() {
		$(this).addClass(on).siblings().removeClass(on);
		var index = $(tabTit).children().index(this);
		$(tabCon).children().eq(index).show().siblings().hide();
	});
}

function setDate() {
	for (var i = 1; i < date.length + 1; i++) {
        console.log(i);
		if (date[i - 1] == 0 && i != 5 && i != 8) {
			$("#" + i).children("div").empty();
			$("#" + i).children("div").addClass("uncheck-in");
			if (i < 5) {
				$("#" + i).children("div").css("background-image", "url(" + static_url + (parseInt(i, 10) + 26) + ".png)");
			} else {
				$("#" + i).children("div").css("background-image", "url(" + static_url + (parseInt(i, 10) - 4) + ".png)");
			}
		}
		if (date[i - 1] == 0 && (i == 5 || i == 8)) {
			//$("#" + i).children("div").css("color", "#a8a8a8");
			//$("#" + i).children(".notice-hot").css("color", "#fff");
			//$("#" + i).children(".notice-hot").css("background-color", "#d7cb94");
			$("#" + i).children("div").empty();
			$("#" + i).children("div").addClass("uncheck-in");
			$("#" + i).children("div").css("background-image", "url(" + static_url + (parseInt(i, 10) - 4) + ".png)");
			$("#" + i).children(".notice-hot").remove();;
		}
		if (date[i - 1] == 1) {
			$("#" + i).children("div").empty();
			$("#" + i).children("div").addClass("flower");
		}

		if (date[i - 1] == 1 && (i == 5 || i == 8)) {
			$("#" + i).children(".notice-hot").remove();
		}

	}
}

function openDialog(content) {
	$("#dialog-content").append(content);
	$("#dialog").css('display', 'block');
}

function closeDialog() {
	$("#dialog").css('display', 'none');
	$("#dialog-content").empty()
	$('body').css('pointer-events', 'none');
	setTimeout(function() {
		$('body').css('pointer-events', 'auto');
	}, 800);
}

function notLogin() {
	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><a class="noticeButton" id="noticeButton" href="http://wechat.ppmiao.com/activity/show/75" style="position: relative;top: 3rem;left: 3rem;padding: 1rem;opacity: 0;">asdsasd</a></div>'
	openDialog(content);
}

//随意添加文字
function dialogNotice(text) {
	var content = '<div class="receive-line-pic dialog-main"><div class="close" id="notice"></div><div class="notice-text">' + text + '</div></div>'
	openDialog(content);
}

function changeStatusS(id) {
	var status = $("#award_" + id).attr('status');
	if (status == 0) {
		$("#award_" + id).attr('status', 2);
		$("#award_" + id).attr('src', static_url + 'receive.png');
	}
}

function hot(num) {
	if (num == 54) {
		var content = '<div class="hot-pic2 dialog-main"><div class="close" id="hot_pic"></div></div>';
	} else {
		var content = '<div class="hot-pic dialog-main"><div class="close" id="hot_pic"></div></div>';
	}
	openDialog(content);
}
function activityFinish(){
    $(".watering-day").css("color", "#a8a8a8");
    $(".watering-time").css("color", "#a8a8a8");
    $(".personInfo").css("color", "#a8a8a8");
    $(".personInfo").children("div").children("span").css("color", "#a8a8a8");
	$('.watering-day').append('<div class="cannot-checkIn"></div>');
    $(".receive-line").children("div").children("img").attr('src', static_url+'unreceive.png');
}
