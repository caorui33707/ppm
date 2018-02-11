var _days = 0; // 借款天数
var _formatAmount;
var _editing = false;
var _layerIndex = 0; // 弹出层序号
var _investDirectionEditor, _repaymentSourceEditor;

$(document).ready(function(){
    var _amount = $("#amount").val();
    if(!isNaN(_amount) && _amount > 999){
        $("#formatAmount").html('&nbsp;千分位：' + format(parseInt(_amount, 0)));
    }else{
        $("#formatAmount").html('&nbsp;千分位：' + parseInt(_amount, 0));
    }
    count_duration();
    count_earn();
    create_contract_no();
});

function changeType(_obj){ // 改变类型
    var _type = $(_obj).val();
    if(_type > 0){
        _layerIndex = layer.load('数据提交中...');
        $.post(ROOT + "/Common/getProductStageByType", {type: _type}, function(msg){
            if(msg.status){
                $('input[name=stage]').val(msg.info);
                getTitle();
                create_contract_no();
            }
            layer.close(_layerIndex);
        });
    }
}

function changeRepayment(_obj){ // 改变还款方式
    var repayment = $(_obj).val();
    if(repayment == 2){
        $("#trRepayment").css('display', '');
    }else{
        $("#trRepayment").css('display', 'none');
    }
}
function changeStage(_obj){ // 改变期数
    getTitle();
    create_contract_no();
}
function getTitle(){ // 生成项目标题
	$('input[name=title]').val('票票喵第' + $('input[name=stage]').val() + '期');
}

function create_contract_no(){
	if($('input[name=stage]').val() > 0) {
        $('input[name=contract_no]').val('ppm'+$('input[name=stage]').val());
	}else {
        $('input[name=contract_no]').val('');
    }
}

function create_contract_no(){
	if($('input[name=stage]').val() > 0) {
        $('input[name=contract_no]').val('ppm'+$('input[name=stage]').val());

        var draft_type_arr = ['银行承兑汇票','电子银行承兑汇票','商业承兑汇票','电子商业承兑汇票'];
        var guaranty_type_arr = ['无需担保','担保机构担保'];
		//取合同利息
		$.post(ROOT + "/Common2/getContractInfo", {contract_no: 'ppm'+$('input[name=stage]').val()}, function(msg){

            if(msg.status){
                $('input[name=contract_interest]').val(msg.info.interest);
               // $('input[name=user_interest]').val(msg.info.interest);
               // $('input[name=gid]').val(msg.info.gid);
                
                //$('input[name=accepting_bank]').val(msg.info.acceptor);
                
                if(msg.info.acceptor) {
                	$('input[name=repayment_source_title]').val(msg.info.acceptor + '承兑');
                }
                $('#contract_time').empty().html('合同里的结束日期：'+msg.info.end_time);

                var draft_type =  msg.info.draft_type;
                if(draft_type != undefined) {
                    var draft_type_str = '<label>' +
                        '<input type="hidden" name="draft_type" value="' + draft_type + '" />' + draft_type_arr[draft_type] +
                        '</label>';

                    $('#draft_type').html(draft_type_str);
                }

             //   $('input[name=accepting_bank]').val(msg.info.accepting_institution);

                var guaranty_type = msg.info.guaranty_type;

                if(guaranty_type != undefined) {
                    var guaranty_type_str = '<label>' +
                        '<input type="hidden" name="guaranty_type" value="' + guaranty_type + '" />' + guaranty_type_arr[guaranty_type] +
                        '</label>';
                    $('#guaranty_type').html(guaranty_type_str);
                }

                guaranty_type = parseInt(guaranty_type);
                if(guaranty_type) {
                    $('input[name=guaranty_institution]').parent().parent().show();
                }else{
                    $('input[name=guaranty_institution]').parent().parent().hide();
                }

               // $('input[name=guaranty_institution]').val(msg.info.guaranty_institution);
            }
        });
	}else {
        $('input[name=contract_no]').val('');
    }
}















function resetEditor(){ // 清空富文本
    KE.html('cus_desc1', '');
    KE.html('cus_desc2', '');
}
function count_earn(){ // 计算项目可获利
    var _totle = $("input[name=amount]").val();
    var _userPer = $("input[name=user_interest]").val();
    var _targetPer = $("input[name=contract_interest]").val();
    $("#corporation_earnings").val(format((_targetPer-_userPer)*_totle*_days/(365*100), 2));
}
function count_duration(){ // 计算借款天数
    if($('#start_time').val() && $('#end_time').val()){
        var time1 = new Date($('#start_time').val().replace(/-/g, "\/"));
        var time2 = new Date($('#end_time').val().replace(/-/g, "\/"));
        _days = parseInt((time2.getTime() - time1.getTime()) / 1000 / 60 / 60 / 24); //天数
        $('#duration').text(_days + ' 天');
        $("input[name=days]").val(_days);
        count_earn();
    }
}


function copy(_obj, _id){
    layer.confirm('确定复制该标吗?', function(){
        _layerIndex = layer.load('数据提交中...');
        $.post(ROOT + '/project/project_copy', {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('复制标成功', -1, function(){
                    window.location.href = msg.jump;
                });
            }else{
                layer.alert(msg.info, -1, function(){
                    location.reload();
                    layer.close(layer.index);
                });
            }
        });
    });
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
            if($("#start_time").val() == '' || $("#end_time").val() == ''){
                layer.alert('请选择一个起止日期!', -1, function(){
                    layer.close(layer.index);
                    if($("#start_time").val() == '') {
                        $("#start_time").focus();
                    }else if($("#end_time").val() == '') {
                        $("#end_time").focus();
                    }
                });
            }else{
                if(_editing) return;
                _editing = true;
                _layerIndex = layer.load('数据提交中...');
                $.ajax({
                    url: ROOT + '/project/edit',
                    data: $('#frmMain').serialize(),
                    type: "POST",
                    cache: false,
                    success: function(msg) {
                        layer.close(_layerIndex);
                        if(msg.status){
                            layer.alert('编辑成功~!', -1, function(){
                                window.location.href = msg.info;
                            });
                        }else{
                            layer.alert(msg.info);
                        }
                        _editing = false;
                    }
                });
            }
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
    KindEditor.ready(function(K){
        _investDirectionEditor = K.create('#invest_direction', {
            uploadJson : ROOT + '/Common/uploadWaterImg',
            fileManagerJson : ROOT + '/Common/fileManagerForEditor',
            allowFileManager : true,
            urlType : 'domain',
            items : [
                'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'cut', 'copy', 'paste',
                'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                'superscript', 'clearhtml', 'selectall', '|', 'fullscreen', '/',
                'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image',
                'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                'anchor', 'link', 'unlink', '|', 'about'
            ],
            afterBlur: function(){this.sync();}
        });
        _repaymentSourceEditor = K.create('#repayment_source', {
            uploadJson : ROOT + '/Common/uploadWaterImg',
            fileManagerJson : ROOT + '/Common/fileManagerForEditor',
            allowFileManager : true,
            urlType : 'domain',
            items : [
                'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'cut', 'copy', 'paste',
                'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                'superscript', 'clearhtml', 'selectall', '|', 'fullscreen', '/',
                'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image',
                'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                'anchor', 'link', 'unlink', '|', 'about'
            ],
            afterBlur: function(){this.sync();}
        });
    });
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