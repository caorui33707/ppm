var flag = true;
var lotteryRecord = '<div class="congratulate dialog-main" id="prizeInfo"><div class="close" id="closeCongratulate"></div><div class="congratulatePic"></div><div class="congratulateText" id="prizeName">恭喜你砸中IPHONE7</div><div class="congratulateButton"></div></div>';
$(document).ready(function() {
	var constRecord = '<div class="prizes-pic dialog-main" id="recordlist"><div class="close" id="closePrize"></div><div class="prize-overflow"></div></div>';
	
	initCnt()
	
	//console.log(constRecord);
    $(".post-demo-content").click(function() {
        $("#myCanvas").css("display", "none");
        stopAnimotion();
    });
    /*
     ***弹窗模块
     */
    //规则
    $(".rules").click(function() {
        var content = '<div class="rules-pic dialog-main"><div class="close" id="closeRules"></div><div class="rulesBox"><div class="rules-Text"></div></div></div>'
        openDialog(content);
        $(".Overflow").css('overflow-y','hidden');
        $("body").css('overflow','hidden');
        //$(".Overflow").css('bottom','-10rem');
    });
    $("body").on("touchstart", "#closeRules", function() {
        closeDialog();
        $("#myCanvas").css("display", "block");
        beginAnimotion();
        $(".Overflow").css('overflow-y','auto');
        $("body").css('overflow','auto');
       // $(".Overflow").css('bottom','');
    });

    
    var elem = document.getElementById('recordlist');
    //查看礼品
    $(".prizes").click(function() {
        
    	if(userId<=0){
        	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><div class="notice-text">请先登录</div></div>'
            openDialog(content);
            return;
        }
        
        $.ajax({
   			type: "post",
   		    async: false,
   		    data:{'lotteryId':lotteryId,'userId':userId},
   		    url: "?c=Lottery&a=get_lottery_log", 
   		    dataType: "json",
   		    success: function (json) {
   		        if(json.status == 1) {
   		        	var arr = [];
   		        	for(var item in json.info) {       
   		        		var _img = '';
   		        		if(json.info[item]['type'] == 1){
   		        			_img = 'list-3@2x.png';
   		        		}else if(json.info[item]['type'] == 2){
   		        			_img = 'list-2@2x.png';
   		        		}else {
   		        			_img = 'list-5@2x.png';
   		        		}
   		        		var s = '<div class="prizeList">';
   		        		s += '<div class="prizeListPic" style="background-image:url('+static_url+'img/'+_img+')"></div>';
   		        		s += '<div class="prizeListText">'+json.info[item]['prize_name']+'</div>';
   		        		s += '<div class="prizeListTime">'+json.info[item]['create_time']+'</div></div>';
   		        		
   		        		arr.push(s);
   		        	}
   		        	$('#record').append(constRecord);
   		        	$('.prize-overflow').empty().html(arr.join(''));
   		         	openDialog(document.getElementById('recordlist'));
   		        } else {
   		        	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><div class="notice-text">'+json.info+'</div></div>'
   		            openDialog(content);
   		        }            		        
   		    }
   		});
        
        
        
    });
    $("body").on("touchstart", "#closePrize", function() {
        closeDialog()
        beginAnimotion();
        $("#myCanvas").css("display", "block");
    });

    //关闭没砸中弹框
    $("body").on("touchstart", "#closeSorry", function() {
        closeDialog()
        beginAnimotion();
        $("#myCanvas").css("display", "block");
    });
    //关闭砸中弹框
    $("body").on("touchstart", "#closeCongratulate", function() {
        closeDialog();
        beginAnimotion();
        $("#myCanvas").css("display", "block");
    });
    //砸中弹框查看奖品
    $("body").on("touchstart", ".congratulateButton", function() {
    	$("#dialog-content").empty();
    	$(".prizes").click();
    });

    
    $("#pot1").click(function() {
 
    	if(userId<=0){
        	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><div class="notice-text">请先登录</div></div>'
        	openDialog(content);
        	return;
        }
    	
        if (!flag)return;
        flag = false;        
               
        do_lottery('pot1');  
    });
    $("#mnc-demo-dot4").click(function() {
    	
    	if(userId<=0){
        	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><div class="notice-text">请先登录</div></div>'
            openDialog(content);
            return;
        }
    	
    	if (!flag) return;            
        flag = false;  

        do_lottery('pot2');   
    });
    $("#mnc-demo-dot2").click(function() {
    	
    	if(userId<=0){
        	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><div class="notice-text">请先登录</div></div>'
            openDialog(content);
            return;
        }
    	
    	if (!flag) return;            
        flag = false;  
        
        do_lottery('pot3');   
    });
    $("#pot3").click(function() {
    	
    	if(userId<=0){
        	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><div class="notice-text">请先登录</div></div>'
            openDialog(content);
            return;
        }
    	
    	if (!flag) return;            
        flag = false;  
        
        do_lottery('pot4');   
    });
    $(".guide-btn").click(function() {
    	$('body').css('position','inherit');
        $(".guide-bg").slideToggle(1000);
        $(".guide-bg").css('display', 'none');
    });
});

function stopAnimotion() {
    $(".mnc-demo-dot0").css("opacity", "0");
    $(".mnc-demo-dot").css("opacity", "1");
    $("#mnc-demo-dot2").css("opacity", "1");
    $(".mnc-demo-dot3").css("opacity", "1");
    $("#mnc-demo-dot4").css("opacity", "1");
    $(".talk").css("opacity", "1");
}

function beginAnimotion() {
    $(".mnc-demo-dot0").css("opacity", "1");
    $(".mnc-demo-dot").css("opacity", "0");
    $("#mnc-demo-dot2").css("opacity", "0");
    $(".mnc-demo-dot3").css("opacity", "0");
    $("#mnc-demo-dot4").css("opacity", "0");
    $(".talk").css("opacity", "0");
    $(".hammer").css({
        "top": "11.5rem",
        "left": "1rem"
    });
}

function ishasClass() {
    var animotionName = ["clickEgg1", "clickEgg2", "clickEgg3", "clickEgg4"];
    for (var i = 0; i < animotionName.length; i++) {
        if ($(".hammer").hasClass(animotionName[i])) {
            $(".hammer").removeClass(animotionName[i]);
        }
    }
}
//砸金蛋弹出奖品



function clear(classID) {
	var prizeElem = document.getElementById('prizeInfo');
	flag = true;
    var content = prizeElem;
    openDialog(content);
    ishasClass();
    recovery(classID);
}

function broken(classID) {
    $(classID).css("background-image", "url("+static_url+"img/badegg.png)");
}

function recovery(classID) {
    $(classID).css("background-image", "url("+static_url+"img/egg.png)");
}

function closeDialog() {
    $("#dialog").css('display', 'none');
    $("#dialog-content").empty();
    $('.post-demo-content').css('pointer-events', 'none');
    setTimeout(function() {
        $('.post-demo-content').css('pointer-events', 'auto');
    }, 800);
}

function openDialog(content) {
    $("#dialog-content").append(content);
    $("#dialog").css('display', 'block');
}

function initCnt(){
	$.ajax({
        type: "post",
        async: false,
        data:{'lotteryId':lotteryId,'userId':userId},
        url: "?c=Lottery&a=get_user_lottery_cnt", 
        dataType: "json",
        success: function (json) {
            	$(".lottery_cnt").html(json.info);
        }
	});
}

function do_lottery(_pos){
	$('#record2').append(lotteryRecord);
	var pos = _pos
	$.ajax({
	        type: "post",
	        async: false,
	        data:{'lotteryId':lotteryId,'userId':userId,'userName':userName},
	        url: "?c=Lottery&a=do_lottery", 
	        dataType: "json",
	        success: function (json) {
	        	if(json.status == 1) {
	        		$("#prizeName").empty().html(json.info.name);
	        		//console.log($("#prizeName"));
	        		//1红包，2现金券，3加息券，
	        		var _backImg = '';
	        		if(json.info.type == '1'){
	        			_backImg = 'prize-3@2x.png';
	        		}else if(json.info.type == '2'){
	        			_backImg = 'prize-2@2x.png';
	        		}else{
	        			_backImg = 'prize-5@2x.png';
	        		}
	        		
	        		if (window.devicePixelRatio > 2) {
	        			_backImg = _backImg.replace("2x","3x");
	        		}
	        		
	        		$('.congratulatePic').css("background-image","url("+static_url+"img/"+_backImg+")");
	        		
	        		var _cnt = parseInt($("#lotteryCnt2").text()) - 1;
	        		//console.log(_cnt);
                	if(_cnt<0){
                		_cnt = 0;
                	}
                	$(".lottery_cnt").html(_cnt);
                	
                	if(pos == 'pot1') {
                		$(".hammer").css({
                            "top": "13.5rem",
                            "left": "-1rem"
                        });       
                        
                        $(".hammer").addClass("clickEgg1");
                        setTimeout("broken('#pot1')", 800);
                        setTimeout("clear('#pot1')", 1200);
                		
                	} else if(pos == 'pot2') {
                		$(".hammer").css("left", "4rem");
                        $(".hammer").addClass("clickEgg2");
                        setTimeout("broken('#mnc-demo-dot4')", 800);
                        setTimeout("clear('#mnc-demo-dot4')", 1200);
                	} else if(pos == 'pot3') {
                		$(".hammer").css({
                            "top": "15.5rem",
                            "left": "6rem"
                        });
                        $(".hammer").addClass("clickEgg3");
                        setTimeout("broken('#mnc-demo-dot2')", 800);
                        setTimeout("clear('#mnc-demo-dot2')", 1200);
                	}else if(pos == 'pot4') {
                		$(".hammer").css({
                            "top": "13.5rem",
                            "left": "11rem"
                        });
                        $(".hammer").addClass("clickEgg4");
                        setTimeout("broken('#pot3')", 800);
                        setTimeout("clear('#pot3')", 1200);
                	}
                	
	        	} else{
	        		flag = true;
	            	var content = '<div class="notice-pic dialog-main"><div class="close" id="closeRules"></div><div class="notice-text">'+json.info+'</div></div>'
	                openDialog(content);
	                return;
	        	}			        	
	        }
	});
}
