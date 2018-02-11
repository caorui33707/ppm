var _layerIndex = 0;

function add(){
    window.location.href = ROOT + '/statistics/channel_add/p/' + _page;
}

function edit(_id){
    window.location.href = ROOT + "/statistics/channel_edit/id/" + _id + '/p/' + _page;
}

function del(_id){
    layer.alert('暂不开放');
}