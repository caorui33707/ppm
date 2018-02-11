/**
 * 
 */

var u = navigator.userAgent, app = navigator.appVersion;
var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //android终端或者uc浏览器
var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
var wxurl = 'http://wechat.ppmiao.com/login/projects';
function go2App() {
	if (isAndroid == true) {
		
		if(isWeiXin()) {
			location.href = wxurl;
		}else{
			ppm.toAction(2);
		}
		
	} else if (isiOS == true) {
		if(isWeiXin()) {
			location.href = wxurl;
		}else{
			loadURL('skip2');
		}
	} else if(isWeiXin()){
		location.href = wxurl;
	}
	return;
}

function go2Login() {
	if (isAndroid == true) {
		if(isWeiXin()) {
			location.href = wxurl;
		}else{
			ppm.toAction(10);
		}
		
	} else if (isiOS == true) {
		if(isWeiXin()) {
			location.href = wxurl;
		}else{
			loadURL('skip2');
		}
	} else if(isWeiXin()){
		location.href = wxurl;
	}
	return;
}

function loadURL(url) {
	var iFrame;
	iFrame = document.createElement("iframe");
	iFrame.setAttribute("src", url);
	iFrame.setAttribute("style", "display:none;");
	iFrame.setAttribute("height", "0px");
	iFrame.setAttribute("width", "0px");
	iFrame.setAttribute("frameborder", "0");
	document.body.appendChild(iFrame);
	iFrame.parentNode.removeChild(iFrame);
	iFrame = null;
}

function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}


function checkPhone(phone){ 
	if(!(/^1[3|4|5|7|8]\d{9}$/.test(phone))){ 
		return false; 
	} 
	return true;
}