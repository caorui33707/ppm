var _layerIndex = 0;

function addPlus(){
    $("#addPlus").toggle();
}
function add(_gid){
    window.location.href = ROOT + "/message/add/type/3/gid/" + _gid + '/p/' + _page + '/_type/' + _type + (_s != '' ? '/s/' + _s : '');
}
function edit(_gid, _id){
    window.location.href = ROOT + "/message/edit/gid/" + _gid + "/id/" + _id + '/p/' + _page + '/type/' + _type + (_s != '' ? '/s/' + _s : '');
}
function del(_id){
    if (confirm('确定删除该条信息吗?')) {
        _layerIndex = layer.load('数据删除中...');
        $.post(ROOT + '/message/delete', {id: _id}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                layer.alert('删除成功~!', -1, function(){
                    window.location.reload();
                });
            }else{
                layer.alert(msg.info);
            }
        });
    }
}
function del2(_id){
    layer.confirm('确定删除该条信息吗?', function(){
        _layerIndex = layer.load('数据删除中...');
        $.post(ROOT + '/message/delete', {id: _id}, function(msg){
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
            $.post(ROOT + '/message/delete', {id: _ids}, function(msg){
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
        layer.alert('请还未选择要删除的项');
    }
}
function pass(_obj, _gid){ // 审核
    layer.confirm('确定发布该消息吗?', function(){
        _layerIndex = layer.load('数据提交中...');
        $.post(ROOT + "/message/pass", {gid: _gid}, function(msg){
            layer.close(_layerIndex);
            if(msg.status){
                $(_obj).parent().html('<span style="color:green;">已发布</span>');
                $.each($(".groupmsg_" + _gid), function(i, n){
                    $(n).find('td').eq(4).html('<span style="color:green;">已发布</span>');
                });
            }else{
                layer.alert(msg.info);
            }
        });
    });
}
function recycle(){ // 回收站
    window.location.href = ROOT + '/message/recycle';
}