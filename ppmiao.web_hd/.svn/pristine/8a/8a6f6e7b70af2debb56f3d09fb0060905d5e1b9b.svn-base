
wx.ready(function() {
	// 2. 分享接口
	// 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
	var dataForWeixin = {
		img : imgUrl,
		linkurl : jump_url,
		title : title,
		desc : content,

	};
	wx.onMenuShareAppMessage({
		title : dataForWeixin.title,
		desc : dataForWeixin.desc,
		link : dataForWeixin.linkurl,
		imgUrl : dataForWeixin.img,
		trigger : function(res) {

		},
		success : function(res) {
			// alert('已分享');
			// share();
		},
		cancel : function(res) {
			// alert('已取消');
		},
		fail : function(res) {
			// alert(JSON.stringify(res));
		}
	});

	// 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
	wx.onMenuShareTimeline({
		title : dataForWeixin.desc,
		link : dataForWeixin.linkurl,
		imgUrl : dataForWeixin.img,
		trigger : function(res) {
			// alert('用户点击分享到朋友圈');
		},
		success : function(res) {
			// alert('已分享');
			// share()

		},
		cancel : function(res) {
			// alert('已取消');
		},
		fail : function(res) {
			// alert(JSON.stringify(res));
		}
	});

	wx.onMenuShareQQ({
		title : dataForWeixin.title,
		desc : dataForWeixin.desc,
		link : dataForWeixin.linkurl,
		imgUrl : dataForWeixin.img,
		trigger : function(res) {
			// alert('用户点击分享到QQ');
		},
		complete : function(res) {
			// alert(JSON.stringify(res));
		},
		success : function(res) {
			// alert('已分享');
		},
		cancel : function(res) {
			// alert('已取消');
		},
		fail : function(res) {
			// alert(JSON.stringify(res));
		}
	});
});
