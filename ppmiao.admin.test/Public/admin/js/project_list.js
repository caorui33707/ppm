var _layerIndex = 0;

function add(){
    window.location.href = ROOT + "/project/add";
}
function pdetail(_id){
    window.location.href = ROOT + "/project/detail/id/" + _id + '/p/' + _page + '/type/' + _type + '/status/' + _status + (_s != '' ? '/s/' + _s : '');
}
function edit(_id){
    window.location.href = ROOT + "/project/edit/id/" + _id + '/p/' + _page + '/type/' + _type + '/status/' + _status + (_s != '' ? '/s/' + _s : '');
}
function del(_id){
    layer.confirm('确定删除该条信息吗?', function(){
        _layerIndex = layer.load('数据删除中...');
        $.post(ROOT + '/project/delete', {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('删除成功~!', -1, function(){
                    window.location.reload();
                });
            }else{
                layer.alert(msg.info);
            }
        });
    });
}
function foreverdel(){ // 批量删除
    var items = $("#checkList tbody").find("input[type=checkbox]:checked").not("#check");
    if(items.length > 0){
        layer.confirm('确定要删除选择的条目吗?', function(){
            var _ids = '';
            $.each(items, function(i, n){
                _ids += ',' + $(n).attr('alt');
            });
            if(_ids) _ids = _ids.substr(1);
            _layerIndex = layer.load('数据删除中...');
            $.post(ROOT + '/project/delete', {id: _ids}, function(msg){
                layer.close(_layerIndex);
                if(msg.status){
                    layer.alert('删除成功~!', -1, function(){
                        window.location.reload();
                    });
                }else{
                    layer.alert(msg.info);
                }
            });
        });
    }else{
        layer.alert('请选择要删除的项');
    }
}
function pass(_obj, _id){ // 审核
    layer.confirm('确定审核通过吗?', function(){
        _layerIndex = layer.load('数据提交中...');
        $.post(ROOT + '/project/verify', {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert(msg.info, -1, function(){
                    $("#status_" + _id).html('<span style="color:green;">销(待)售中</span>');
                    $(_obj).remove();
                    layer.close(layer.index);
                });
            }else{
                layer.alert(msg.info);
            }
        });
    });
}
function repay(_id){ // 还本付息
    window.open(ROOT + "/project/repay/id/" + _id, 'repay', 'height=600,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
}
function preview(_id){ // 预览项目
    window.open(ROOT + "/project/preview/id/" + _id + '/act/detail', 'preview', 'height=800,width=600,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
}
function recycle(){ // 回收站
    window.location.href = ROOT + '/project/recycle';
}
function forecast_push(_obj, _id){ // 一键推送
    window.location.href = ROOT + '/project/forecast_push/id/'+_id;
}
function onesystem(_obj, _id){ // 一键发送系统消息
    layer.confirm('确定一键发送标系统消息吗?', function(){
        _layerIndex = layer.load('正在发送中...');
        $.post(ROOT + "/project/onesystem", {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('发送成功~!', -1);
            }else{
                layer.alert(msg.info);
            }
        });
    });
}
function push(_id){ // 订阅标提醒
    layer.confirm('确定发送订阅提醒吗?', function(){
        _layerIndex = layer.load('正在发送中...');
        $.post(ROOT + "/project/push", {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('发送成功~!', -1);
            }else{
                layer.alert(msg.info);
            }
        });
    });
}
function establish(_id){
	layer.confirm('确定要成标操作吗?', function(){
        _layerIndex = layer.load('正在发送中...');
        $.post(ROOT + "/project/establish", {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('发送成功~!', -1);
            }else{
                layer.alert(msg.info);
            }
        });
    });
}

function chargeoff(_id){
	layer.confirm('确定要出账操作吗?', function(){
        _layerIndex = layer.load('正在发送中...');
        $.post(ROOT + "/project/project_chargeoff", {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert(msg.info, -1);
            }else{
                layer.alert(msg.info);
            }
        });
    });
}

function pass(_id){ // 审核产品
    layer.confirm('确定审核通过吗?', function(){
        _layerIndex = layer.load('数据提交中...');
        $.post(ROOT + '/project/verify', {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('平台已审核通过~!', -1, function(){
                    location.reload();
                    layer.close(layer.index);
                });
            }else{
                layer.alert(msg.info);
            }
        });
    });
}

function bankPass(_id){ // 审核产品
    layer.confirm('确定提交银行审核吗?', function(){
        _layerIndex = layer.load('数据提交中...');
        $.post(ROOT + '/project/bankVerify', {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('银行已审核通过~!', -1, function(){
                    location.reload();
                    layer.close(layer.index);
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
