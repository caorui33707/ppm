var _loading = false;
var _lastTime = 0;
$(document).ready(function(){
    $(".login").click(function(){
        $(".loginBg,.shadow").css('display', '');
    });
    $(".close").click(function(){
        $(".loginBg,.shadow").css('display', 'none');
    })
});
function getVerifyCode(_obj) {
    if($.trim($("input[name=phone]").val()).length != 11){
        layer.alert('手机号码格式不正确', -1);
        return;
    }
    _lastTime = _cdTime;
    _cdtObj.text(_lastTime+'s后重新获取');
    $("input[name=phone]").attr('readonly', 'readonly');
    clearInterval(_pid);
    _pid = setInterval("countdown()", 1000);
    var _phone = $("input[name=phone]").val();
    $.post(_root+"/login/sendsms", {phone: _phone}, function(msg){
        if(msg.status){
            _phone = _phone.substr(0,3) + '****' + _phone.substr(7,4);
            $(".msg").html('<p>验证码已发送到'+_phone+'，请注意查收！</p>');
            $(".msg").css('display', 'block');
        }else{
            _cdtObj.text('获取验证码');
            $("input[name=phone]").removeAttr('readonly');
            clearInterval(_pid);
            _lastTime = 0;
            _cdtObj.text('获取验证码');
            layer.alert(msg.info, -1);
        }
    });
}
function countdown(){
    _lastTime -= 1;
    _cdtObj.text(_lastTime+'s后重新获取');
    if(_lastTime <= 0){
        _cdtObj.text('获取验证码');
        $("input[name=phone]").removeAttr('readonly');
    }
}
function login(_obj){
    if(!_loading){
        if($("input[name=phone]").val().length != 11){
            layer.alert('手机号码格式不正确', -1);
            return;
        }
        if($.trim($("input[name=verifycode]").val()) == ''){
            layer.alert('请输入短信验证码', -1);
            return;
        }
        _loading = true;
        $(_obj).val('正在登录...');
        $("#frmMain").ajaxSubmit(function(msg){
            _loading = false;
            if(msg.status){
                window.location.href = msg.info;
            }else{
                $(_obj).val('立即登录');
                layer.alert(msg.info, -1);
            }
        });
    }
}