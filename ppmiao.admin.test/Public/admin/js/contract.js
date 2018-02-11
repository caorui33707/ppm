
function changeGuaranty(){ // 改变担保
    create_contract_no();
}

changeGuaranty();

function create_contract_no(){
    var guaranty_institution = $('input[name=guaranty_institution]').val();
	if(guaranty_institution) {
		//$('input[name=stone_no]').val('ppm'+$('input[name=stage]').val());	
		//取合同利息
		$.post(ROOT + "/Common2/getGuarantyInfo", {guaranty_institution: guaranty_institution}, function(msg){
            if(msg.status){
                $('#guaranty_tips').html('<span class="Validform_checktip Validform_right"></span>');
                $('input[name=tips_val]').val(1);
                $('input[name=gid]').val(msg.info.id);
            }else{
                $('input[name=tips_val]').val(0);
                $('input[name=gid]').val(0);
                $('#guaranty_tips').html('<span class="Validform_checktip Validform_wrong">' + msg.info + '</span>')
            }
        });
	}else {
        $('#guaranty_tips').html('<span class="Validform_checktip Validform_wrong">请输入担保机构</span>')
        //$('input[name=stone_no]').val('');
    }
}