/*
* @Author: by
* @Date:   2017-06-27 16:40:11
* @Last Modified by:   Administrator
* @Last Modified time: 2017-06-30 05:58:48
*/

$(function () {

	var banks = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15'],
	endNums = [1234,3443,1002,2344,1245,2543,3443,1002,2344,1245,2543,3443,1002,2344,1245],
	num = Math.floor(Math.random()*banks.length),
	endNum = endNums[num],
	bank = banks[num],
	bankname = '';

	changeBank(bank);
	
	changeList("repayment");
	changeList("repayments");
	changeList("capital");
	changeList("record");
	changeList("enterprise");


	$(".echo li").click(function() {
		$(this).removeClass('spanclick').addClass('spanclick').siblings().removeClass('spanclick');
	});
	
	$("#cashBtn").click(function() {
		
		$(".popup").css({display: 'block'});
		var h = $(".dialog").height() + 50>window.innerHeight ? $(".dialog").height() + 100 : window.innerHeight;
		$(".popup").css({height: h + 'px'});
		$(".container").css({overflow: 'hidden', height: (window.innerHeight - 50) + 'px'}); /*-50 设置了 .container {padding-bottom: 50px;}*/
	});
	$(".close-btn").click(function() {
		$(".popup").css({display: 'none'});
		$(".container").css({overflow: '', height: 'auto'});
	});

	/*切换信息*/
	function changeList(ele){
		$("#"+ele).click(function() {
			$(this).css({color: '#ff7128',border: '1px solid #ff7128'}).siblings().css({color: '#020202',border: '1px solid #fff'});
			$("."+ele).css({display: 'block'}).siblings().css({display: 'none'});;
		});
	}
	/*判断用户当前使用的是什么银行卡*/
	function changeBank(bank){
		switch(bank){
			case '1': 
				bankname = '中国银行';
				break;
			case '2': 
				bankname = '中国浦发银行';
				break;
			case '3': 
				bankname = '中国建设银行';
				break;
			case '4': 
				bankname = '中国农业银行';
				break;
			case '5': 
				bankname = '中国招商银行';
				break;
			case '6': 
				bankname = '交通银行';
				break;
			case '7': 
				bankname = '中国工商银行';
				break;
			case '8': 
				bankname = '兴业银行';
				break;
			case '9': 
				bankname = '中信银行';
				break;
			case '10': 
				bankname = '光大银行';
				break;
			case '11': 
				bankname = '平安银行';
				break;
			case '12': 
				bankname = '中国邮政储蓄银行';
				break;
			case '13': 
				bankname = '上海银行';
				break;
			case '14': 
				bankname = '中国民生银行';
				break;
			case '15': 
				bankname = '中国广发银行';
				break;
			default: 
				bankname = '中国银行';
		}

		$(".bank-log").siblings('.bankname').html(bankname + '&nbsp;<span>尾号' + endNum + '</span>');
		$(".bank-log").attr('src','./images/bank/' + bank + '@2x.png');
	}

	/*input鼠标进入移出样式*/
	$("input[type='text']").focus(function() {
		if($(this).css("color") === "rgb(191, 191, 191)" || $(this).css("color") === "#bfbfbf") {
			$(this).val("").css({'color': '#333'});
		} else if ($(this).val() !== "" && $(this).css("color") === "#333") {
			var val = $(this).val();
			$(this).val(val).focus();
		}
	});
	$("input[type='text']").blur(function(event) {
		var val = $(this).val();
		var pla = $(this).attr('placeholder');
		if ($(this).css("color") === "#333" && $(this).val() !== "") {
			$(this).val(val).css({"color": "#333"});
		} else if($(this).val() === "") {
			$(this).val(pla).css({'color': '#bfbfbf'});
		}
	});

})
