
isint = /^[-+]?\d*$/;//整数正则表达式
isdouble = /^[-\+]?\d+(\.\d+)?$/;//双精度正则表达式
ischinese = /^[\u0391-\uFFE5]+$///中文正则表达式
isemail = /[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?///邮箱地址正则表达式
iscardid = /^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$///身份证号码正则


//判断内容是否为空
function IsNull(id, message) {
    if ($("#" + id + "").val() == null || $("#" + id + "").val().length == 0 || $("#" + id + "").val() == undefined || $("#" + id + "").val()=='') {
        alert(message);
        return false
    }
}

//判断内容是否为整数类型
function  IsInteger(id, message) {
    if (!isint.test($("#" + id + "").val()) || $("#" + id + "").val().length == 0) {
        alert(message);
        return false;
    }
}


//判断内容是否为双精度类型
function IsDouble(id, message) {
    if (!isdouble.test($("#" + id + "").val()) || $("#" + id + "").val().length == 0) {
        alert(message);
        return false;
    }
}

//判断内容是否为中文类型
function IsChinese(id, message) {
    if (!ischinese.test($("#" + id + "").val()) || $("#" + id + "").val().length == 0) {
        alert(message);
        return false;
    }
}

//判断内容是否为邮箱格式
function IsEmail(id, message) {
    if (!isemail.test($("#" + id + "").val()) || $("#" + id + "").val().length == 0) {
        alert(message);
        return false;
    }
}

//判断内容是否为身份证号码格式
function IsCardID(id, message) {
    if (!iscardid.test($("#" + id + "").val()) || $("#" + id + "").val().length == 0) {
        alert(message);
        return false;
    }
}