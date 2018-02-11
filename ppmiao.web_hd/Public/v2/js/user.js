$(document).ready(function() {
    $(".newsBox01Title h2").click(function(){
        var index = $(".newsBox01Title h2").index(this);
        $(this).addClass("selected").siblings("h2").removeClass("selected");
        $(".newsBox01Content").hide().eq(index).show();
        $(".page").hide().eq(index).show();
    });
    $(".newsBox01ProTitle h2").click(function(){
        var index = $(".newsBox01ProTitle h2").index(this);
        $(this).addClass("selected").siblings("h2").removeClass("selected");
        $(".innerRight_user_myRecord_block01").hide().eq(index).show();
        $(".page").hide().eq(index).show();
    });
});
function loadSysMsg(_page){
    $.post(_root+"/user/message", {target:'system',page:_page}, function(msg){
        if(msg.status){
            $(".sysmsg").html(msg.data.list);
            $(".system").prop('innerHTML', $(msg.data.show).html());
        }
    });
}
function loadPerMsg(_page){
    $.post(_root+"/user/message", {target:'personal',page:_page}, function(msg){
        if(msg.status){
            $(".permsg").html(msg.data.list);
            $(".personal").prop('innerHTML', $(msg.data.show).html());
        }
    });
}
function showMore(_obj,_index){
    $(".pd"+_index).css('display','none');
    $(".p"+_index).css('display','');
    $(_obj).remove();
}
function loadWalletRecord(_page){
    $.post(_root+"/user/wallet", {page:_page}, function(msg){
        if(msg.status){
            $(".item").remove();
            $(".itemheader").after(msg.data.list);
            $(".page").prop('outerHTML', msg.data.show);
        }
    });
}
function loadNRepayProRecord(_page){
    $.post(_root+"/user/product", {target:'norepay',page:_page}, function(msg){
        if(msg.status){
            $(".norepaymentdata").html(msg.data.list);
            if(msg.data.show) $(".norepay").prop('innerHTML', $(msg.data.show).html());
        }
    });
}
function loadRepayProRecord(_page){
    $.post(_root+"/user/product", {target:'repay',page:_page}, function(msg){
        if(msg.status){
            $(".repaymentdata").html(msg.data.list);
            if(msg.data.show) $(".repay").prop('innerHTML', $(msg.data.show).html());
        }
    });
}