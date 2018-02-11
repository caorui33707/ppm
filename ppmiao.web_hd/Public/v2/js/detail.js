$(document).ready(function(){

});
function changeAllow(_obj){
    if($(_obj).attr('class') == 'moni_radio'){
        $(_obj).removeClass('moni_radio');
        $(_obj).addClass('moni_radio_un');
        _allow = false;
    }else{
        $(_obj).removeClass('moni_radio_un');
        $(_obj).addClass('moni_radio');
        _allow = true;
    }
}
function buy(){
    if(ts > 0) return;
    if(!_allow){
        layer.alert('请同意"服务协议"', -1);
        return;
    }
    var _money = $("#money").val();
    if(_money != null && !isNaN(_money) && _money > 0){
        if(_money % _minMoney != 0){
            layer.alert('购买金额必须是'+_minMoney+'的整数倍', -1);
            return;
        }
        $("#frmMain").submit();
    }else{
        layer.alert('请输入正确的金额', -1);
    }
}
function getChart(_month) {
    _chart.showLoading();
    $.post(_root + "/common/getFundData", {fundid: _fund_id, month: _month, ptype: _product_type, endtime: _endTime}, function (msg) {
        if (msg.status) {
            $("#cStartTime").text(msg.starttime);
            $("#cZdf").text(msg.drop + '%');
            $("#cZhnh").text(msg.rate + '%');
            var _data = new Array();
            var _sy = new Array();
            $.each(msg.arr, function(i, n){
                _data.push([n.datetime, parseFloat(n.val)]);
            });
            $.each(msg.arr_sy, function(i, n){
                _sy.push([n.datetime, parseFloat(n.val)]);
            });
            _chart.series[0].setData(_data);
            _chart.series[1].setData(_sy);
            _chart.hideLoading();
        }else{
            layer.alert(msg.info, -1);
        }
    });
}