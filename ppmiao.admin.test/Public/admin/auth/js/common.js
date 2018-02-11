function format(num, fix) { // 数字转化到千分位
    return (num.toFixed(fix) + '').replace(/\d{1,3}(?=(\d{3})+(\.\d*)?$)/g, '$&,');
}

function CheckAll(_obj, _control_id){
    if(!$(_obj).is(':checked')){
        $("#" + _control_id + ' tbody').find('input[type=checkbox]').removeAttr('checked');
    }else{
        $("#" + _control_id + ' tbody').find('input[type=checkbox]').attr('checked', 'checked');
    }
}