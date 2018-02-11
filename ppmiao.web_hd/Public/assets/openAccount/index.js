
$(document).ready(function() {
    $("#shadow").click(function(){
        $('.bigPic').css('display','none');
        $('body').css('overflow', 'auto');
    });
});
function closeDialog() {
    $('.dialog').css('display','none');
    $('body').css('overflow', 'auto');
}
var countdown = 60;
function settime(obj) {
    console.log(1);
    if (countdown == 0) {
        obj.removeAttribute("disabled");
        obj.value = "获取验证码";
        countdown = 60;
        $('.dialog-container2__button').css('background-color','#ff580f');
        return;
    } else {
        obj.setAttribute("disabled", true);
        obj.value = countdown + "秒以后重新获取";
        countdown--;
        $('.dialog-container2__button').css('background-color','#d9d9d9');
    }
    setTimeout(function() {
        settime(obj)
    }, 1000)
}
