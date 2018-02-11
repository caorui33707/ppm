//当前选中模块所在位置 若无数据则为0 若要继续上次的进度可以将这里赋值
var stepData = 0;
var picNum = 0;
//监听定时器
var interval;
var prize = ['随机现金券', '空气净化器', '随机红包', '小米充电宝', '100元京东卡', '随机加息券', '美菱加湿器',
    '红米3S', '随机现金券', '随机现金券', 'Bemega保温杯', '100元现金券', '随机现金券', '小米蓝牙音箱', 'iPhone7P', '爱奇艺会员', '3%加息券', '随机红包', '100元话费',
    '100元京东卡', '随机红包', '300元京东卡', '随机现金券', '爱奇艺会员', '美的小台灯', '100元话费', '10g金条', '小米NOTE', '随机红包券', '500元京东卡','随机现金券'
];


var noticeelem = document.getElementsByClassName('notice');
var notice = dialog({
    content: noticeelem
});
/*
 ***弹窗模块
 */
var raidersfloat = dialog({
    content: '<div class="ultimateRaiders"><div class="close" id="close"></div></div>'
});



//有中奖
var elem = document.getElementById('recordlist');

 var record = dialog({
	 content:elem
 });

//无中奖
var record2 = dialog({
    content: '<div class="record"><div class="close" id="closerecord2"></div><div class="record-no">暂无奖品记录</div></div>'
});
var startX, startY, moveEndX, moveEndY, nDivHight;
var isBottom = false;
$("#closenotice").click(function() {
    closeDialog("notice", "closenotice");
});


$(document).ready(function() {
	
    $("body").on('touchmove',function(event) { event.preventDefault(); }, false);
    setTimeout("$('body').unbind('touchmove')", 3000);
    
	setTimeout("$('#loading').hide()",3000);
    window.addEventListener('scroll', winScroll);

    function winScroll(e) {
        if ($("body").scrollTop() > (document.documentElement.clientHeight / 2)) {
            $(".shaizi-bg").css("display", "block");
        } else {
            $(".shaizi-bg").css("display", "none");
        }
    }
    
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //android终端或者uc浏览器
    var a = false;
    if(a){
    	$('.record-bg').on('touchstart', function(e) {
            $('body').unbind('touchmove');
            startX = e.originalEvent.changedTouches[0].pageX;
            startY = e.originalEvent.changedTouches[0].pageY;
        });
    	 $('.record-bg').on('touchend', function() {
    	        $("body").on('touchmove', function(event) {
    	            event.preventDefault();
    	        }, false);
    	    });
	    $(".record-bg").on("touchmove", function(e) {
	        // e.preventDefault();
	        moveEndX = e.originalEvent.changedTouches[0].pageX;
	        moveEndY = e.originalEvent.changedTouches[0].pageY;
	        X = moveEndX - startX;
	        Y = moveEndY - startY;
	        var nScrollHight = 0; //滚动距离总长(注意不是滚动条的长度)
	        var nScrollTop = 0; //滚动到的当前位置
	        $(".record-bg").scroll(function() {
	            nScrollHight = $(this)[0].scrollHeight;
	            nScrollTop = $(this)[0].scrollTop;
	            if(nScrollTop + nDivHight >= nScrollHight){
	                isBottom =  true;
	            }
	            else{
	                isBottom =  false;
	            }
	        });
	        if (Y > 0 && $(".record-bg").scrollTop() == 0) {
	            $("body").on('touchmove', function(event) {
	                event.preventDefault();
	            }, false);
	        }
	        if (Y < 0 && isBottom) {
	            $("body").on('touchmove', function(event) {
	                event.preventDefault();
	            }, false);
	        }
	    });
    }
    
    var recordflag = true;
    
    $(".random-buttonr").click(function() {
    	
    	if(!mobile) {
    		$("#tips").empty().html('请先登录');
    		showDialog('notice');
    		return;
    	} else {
    		
    		if(!recordflag) return;
    		
    		recordflag = false;    		
    		
        	$.ajax({
    	    	url: "?c=Activity&a=dfw_prizelist",
    	        type: "post",
    	        data: {'mobile':mobile},
    	        success: function (data) {    	        	
    	        	recordflag = true;    	        	
    	        	if(data.status == 0){
    	        		if(data.info.length > 0) {  
    	        			var html = '';
    	        			var len = data.info.length;    	        			
    	        			for (var i = 0;i<len; i++) {    	        				
    	        				var type = '';    	        				
    	        				if(data.info[i]['awardType'] <= 3){
    	        					type = '实时到账';
    	        				} else if(data.info[i]['awardType']==4){
    	        					type = '邮寄发放';
    	        				} else{
    	        					type = '券码发放';
    	        				} 
    	        				
    	        				////0,无；1,红包；2,加息券；3,现金券；4,实物
    	        				
    	        				var dt = data.info[i]['addTime']/1000;
    	        				var dt = userDate(dt);    	        				
    	        				html += '<div><span>'+dt+'</span><span>'+data.info[i]['awardDesc']+'</span><span>'+type+'</span></div>';
    	        			}
    	        			
    	        			if(html){
    	        				$("#prizelist").empty().html(html);
    	        			}
    	        			showDialog('record');
    	        			nDivHight = $(".record-bg").height();
    	        			
    	        		} else {
    	        			showDialog('record2');
    	        		}
    	        		return false;
    	        	}else {
    	        		if(data.json.status == 2) {
    	        			$("#tips").empty().html(data.info);
    	        		} else {
    	        			$("#tips").empty().html(data.info['errorMsg']);
    	        		}
    	        		showDialog('notice');
    	        		return;
    	        	}
    	        }
    	   	})
    	}
        
    });
    $("#raidersfloat").click(function() {
    	showDialog('raidersfloat');
    });
    $("#close").click(function() {
        closeDialog("raidersfloat", "close");
    });
    $("#closerecord").click(function() {
    	 closeDialog("record", "closerecord");
    });
    
    $("#closerecord2").click(function() {
    	 closeDialog("record2", "closerecord");
    });
    /*
     ***投掷筛子模块
     */
    $(".random-buttonl").click(function() {
        throwingBall("throwing");
        if(picNum){
        	play("throwing2");
        }
    });
    $(".shaiziBg-button").click(function() {
        throwingBall("throwing2");
        if(picNum){
        	play("throwing");
        }
    });
    /*
     ***图片自动加载
     */
    //i为元素的索引，从0开始,domEle为当前处理的元素对象
    $.each($(".dafuweng > div"), function(index, domEle) {
        //i为元素的索引，从0开始,domEle为当前处理的元素对象
        if (index > 0 && index <= 30) {

            if (window.devicePixelRatio > 1) {
                $(domEle).children("div").css("background-image", "url("+static_url+index + "@2x.png)");
            }
            if (window.devicePixelRatio > 2) {
                $(domEle).children("div").css("background-image", "url("+static_url+index + "@3x.png)");
            } else {
                $(domEle).children("div").css("background-image", "url("+static_url+index + ".png)");
            }
        }
    });
});

function userDate(uData){
	var myDate = new Date(uData*1000);
	var year = myDate.getFullYear();
	var month = myDate.getMonth() + 1;
	var day = myDate.getDate();
	return year + '-' + month + '-' + day;
}


var lotteryflag = true;

function throwingBall(ball_id) {
		
	var rollNum = false;
    
    if(!mobile) {
    	$("#tips").empty().html('请先登录');
		showDialog('notice');
		return;
	} else {
		
		if(!lotteryflag) return;
		
		lotteryflag = false;    		
		
    	$.ajax({
	    	url: "?c=Activity&a=dfw_lottery",
	        type: "post",
	        data: {'mobile':mobile},
	        async:false,
	        success: function (data) {
	        	
	        	if(data.status == 0){
	        		var _cnt = parseInt($("#lottery_cnt").text()) - 1;
                	if(_cnt<0){
                		_cnt = 0;
                	}
                	$("#lottery_cnt").html(_cnt);
	        		rollNum = parseInt(data.info);
	        	}else{
	        		if(data.status == 2) {
	        			$("#tips").empty().html(data.info);
	        		} else {
	        			$("#tips").empty().html(data.info['errorMsg']);
	        		}
	        		showDialog('notice');
	        		return false;
	        	}
	        }
	   	})
	}    
    
    if(rollNum) {	
    	picNum = rollNum;
	    disableButton('randomButtonl');
	    var isInitial = $("#" + ball_id).hasClass("initial");
	    if (isInitial == true) {
	        $("#" + ball_id).removeClass("initial");
	        $("#" + ball_id).addClass("throwing");
	    }
	    setTimeout("stopThrowing(" + ball_id+","+rollNum+ ")", 2000);
    }
}

function stopThrowing( ball_id,rollNum ) {
	var isInitial = $("#" + ball_id.id).hasClass("initial");
    //随机数
    //rollNum = Math.ceil(Math.random() * 6);
    if (isInitial == false) {
        $("#" + ball_id.id).removeClass("throwing");
        $("#" + ball_id.id).addClass("initial");
        $(".initial").css("background-image", "url("+static_url+"random/dice_" + rollNum + ".png)");
    }
    var step = parseInt(stepData);
    var insidestepData = parseInt(stepData);
    interval = setInterval(function() {
        if (step < (insidestepData + rollNum)) {
            move(step % 30);
            step++;
            //console.log("step"+step);
            if(step > 30){
                if (window.devicePixelRatio > 1) {
                    $(".box-border.first").css("background-image", "url("+static_url+"z@2x.gif)");
                }
                if (window.devicePixelRatio > 2) {
                    $(".box-border.first").css("background-image", "url("+static_url+"z@3x.gif)");
                } else {
                    $(".box-border.first").css("background-image", "url("+static_url+"z.gif)");
                }
            }
        }
    }, 500);
    stepData = parseInt(stepData) + parseInt(rollNum);
    window.setTimeout('window.clearInterval(interval)', 500 * rollNum + 500);    
    $("#tips").empty().html("恭喜您获得"+prize[(stepData % 30)]);
    window.setTimeout("showDialog('notice')", 500 * (rollNum + 2));
    ableButton('randomButtonl');
    //console.log("insidestepData" + insidestepData);
    //console.log("stepData" + stepData);
}

function move(i) {
    //console.log("i" + i);
    if(i == 29){
        $("#box29").removeClass("selected");
        $("#box30").addClass("selected");
    }
    if(i == 0){
        $("#box30").removeClass("selected");
        $("#box1").addClass("selected");
    }
    else{
        $("#box" + i).removeClass("selected");
        $("#box" + ((i + 1) % 30)).addClass("selected");
    }
}
function disableButton(){
    var disableButton = document.getElementById('randomButtonl');
     disableButton.disabled=true; //使用true或false，控制是否让按钮禁用
}
function ableButton(){
    var disableButton = document.getElementById('randomButtonl');
     disableButton.disabled=false; //使用true或false，控制是否让按钮禁用
}
//禁用连续点击
function closeFloat(able_id) {
    disableButton(able_id);
    setTimeout(function() {
        ableButton(able_id);
    }, 500);
}


//封装弹窗方法
function showDialog(dialog) {
    switch (dialog) {
        case "notice":
            notice.showModal();
            break;
        case "record":
            record.showModal();
            break;
        case "record2":
            record2.showModal();
            break;
        case "raidersfloat":
            raidersfloat.showModal();
            break;
        default:
            return;
    }
    $(".ui-popup-backdrop").css("z-index", "1000");
    $(".ui-popup-focus").css("z-index", "1200");
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //android终端或者uc浏览器
    if(!(dialog == "record")){
    	$("body").on('touchmove', function(event) {
            event.preventDefault();
        }, false);
    }
}

function closeDialog(dialog, _id) {
    switch (dialog) {
        case "notice":
            notice.close();
            break;
        case "record":
            record.close();
            break;
        case "record2":
            record2.close();
            break;
        case "raidersfloat":
            raidersfloat.close();
            break;
        default:
            return;
    }
    closeFloat(_id);
    $('body').unbind('touchmove');
    lotteryflag = true;
}

function play(ball_id){
    var isInitial = $("#" + ball_id).hasClass("initial");
    if (isInitial == true) {
        $("#" + ball_id).removeClass("initial");
        $("#" + ball_id).addClass("throwing");
    }
    setTimeout("stop(" + ball_id + ")", 2000);
}
function stop(ball_id){
    //console.log(ball_id);
    $("#" + ball_id.id).removeClass("throwing");
    $("#" + ball_id.id).addClass("initial");
    $(".initial").css("background-image", "url("+static_url+"random/dice_" + picNum + ".png)");
}

