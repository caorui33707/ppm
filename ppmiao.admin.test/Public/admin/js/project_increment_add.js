var _formatAmount;
var _adding = false;
var _layerIndex = 0;

function changeType(_obj){ // 改变类型
    var _type = $(_obj).val();
    if(_type > 0){
        if(_type == 109 || _type == 110 || _type == 104) { // 基金类(包括打新股)
            window.location.href = ROOT + '/project/add_fund/type/' + _type;
        }else if(_type == 139) { // 股权众筹
            window.location.href = ROOT + '/project/add_equity/type/' + _type;
        }else if(_type == 148) { // 区间收益型
            window.location.href = ROOT + '/project/add_section/type/' + _type;
        }else if(_type == 149){ // 增值产品
            window.location.href = ROOT + '/project/add_increment/type/' + _type;
        }else{  // 其他
            window.location.href = ROOT + '/project/add/type/' + _type;
        }
    }
}
function changeRepayment(_obj){ // 改变还款方式
    var repayment = $(_obj).val();
    if(repayment == 2){
        $("#trRepayment").css('display', '');
    }else{
        $("#repayment_day").val(20);
        $("#trRepayment").css('display', 'none');
    }
}
function changeStage(_obj){ // 改变期数
    getTitle();
}
function getTitle(){ // 生成项目标题
    if($('select[name=type]').val() > 0)
        $('input[name=title]').val($('select[name=type]').find('option:selected').text() + '第' + $('input[name=stage]').val() + '期');
    else
        $('input[name=title]').val('');
}
function count_earn(){ // 计算项目可获利
    var _totle = $("input[name=amount]").val();
    var _userPer = $("input[name=user_interest]").val();
    var _targetPer = $("input[name=contract_interest]").val();
    $("#corporation_earnings").val(format((_targetPer-_userPer)*_totle*_days/(365*100), 2));
}
function count_duration(){ // 计算借款天数
    if($('#start_time').val() && $('#end_time').val()){
        var time1 = new Date($('#start_time').val().replace("-", ","));
        var time2 = new Date($('#end_time').val().replace("-", ","));
        _days = parseInt((time2.getTime() - time1.getTime()) / 1000 / 60 / 60 / 24); //天数
        $("#days").val(_days);
        $('#duration').text(_days + ' 天');
        count_earn();
    }
}
$(document).ready(function(){
    $("#frmMain").Validform({ // 表单验证
        tiptype: 3,
        datatype:{
            "f": /^(\d+)(\.?)(\d{0,2})$/,
            "semoney": function(gets, obj, curform, datatype){ // 起止金额
                if(!isNaN($("#money_min").val()) && !isNaN($("#money_max").val())) return true;
                return false;
            }
        },
        callback: function(form){
            if(_adding) return;
            _adding = true;
            _layerIndex = layer.load('数据提交中...');
            $.ajax({
                url: ROOT + '/project/add_increment',
                data: $('#frmMain').serialize(),
                type: "POST",
                cache: false,
                success: function(msg) {
                    layer.close(_layerIndex);
                    if(msg.status){
                        layer.alert('添加成功~!', -1, function(){
                            window.location.href = ROOT + '/project/index';
                        });
                    }else{
                        layer.alert(msg.info);
                    }
                    _adding = false;
                }
            });
            return false;
        }
    });
    _formatAmount = $("#formatAmount");
    $('#amount').keyup(function(event){
        if(!isNaN(this.value) && this.value > 999){
            _formatAmount.html('&nbsp;千分位：' + format(parseInt(this.value, 0)));
        }else{
            _formatAmount.html('&nbsp;千分位：' + parseInt(this.value, 0));
        }
    });
    if(_stage > 0) getTitle();
});

function addTemplate(_type){
    var _name = prompt('请输入模板名称');
    if($.trim(_name) != ''){
        _layerIndex = layer.load('数据提交中...');
        var _content = (_type == 1 ? $('#invest_direction').val() : $('#repayment_source').val());
        $.post(ROOT + "/project/project_template_add", {name: _name, type: _type, content: _content}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('添加成功~!');
            }else{
                layer.alert(msg.info);
            }
        });
    }
}

function selectTemplate(_type){ // 产品内容模板选择
    window.open(ROOT + "/project/project_template/type/" + _type, 'template', 'height=800,width=600,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
}

function selectTemplateCallback(_id){ // 选择模板内容回调函数
    _layerIndex = layer.load('模板加载中...');
    $.post(ROOT + "/project/project_template_detail", {id: _id}, function(msg){
        layer.close(_layerIndex);
        if(msg.status){
            if(msg.data.type == 1){
                _investDirectionEditor.html(msg.data.content);
                _investDirectionEditor.sync();
            }else if(msg.data.type == 2){
                _repaymentSourceEditor.html(msg.data.content);
                _repaymentSourceEditor.sync();
            }
        }else{
            layer.alert(msg.info);
        }
    });
}

function changeInterestType3(_obj){
    if($(_obj).val() == 2){
        $("#interest_datetime").css('display', '');
    }else{
        $("#interest_datetime").css('display', 'none');
        $("#interest_time3").val('');
    }
}

function itemSelected(_id, _name, _code){
    $("#fund_name").html(_name);
    $("#fund_code").val(_code);
    $("#fund_id").val(_id);
}