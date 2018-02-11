var ruleHtml = '<div class="cut-off__rule"></div>'
$(document).ready(function() {
    // $(".PPMHeader").load("../header/index.html");
    // $(".PPMFooter").load("../footer/index.html");
    $.ajax({
        headers: {
            Accept: "application/json; charset=utf-8"
        },
        //提交数据的类型 POST GET
        type: "GET",
        //提交的网址
        // url: "http://testing.ppmiao.com/product/index.html"
        url: "/product/get_product_list.html",
        //返回数据的格式
        datatype: "json", //"xml", "html", "script", "json", "jsonp", "text".
        //成功返回之后调用的函数
        success: function(data) {
        	data = JSON.parse(data)

            for (var index = 0; index < data.length; index++) {
                if (data[index].categoryName == '票票喵-新手标') {

                	for (var i = 0; i < data[index].list.length; i++) {
                        if (data[index].list[i] && (data[index].list[i].isComplete == 1 || data[index].list[i].expiryDates < 1)) {
                            data[index].list.splice(i, 1);
                            i--;
                        }
                    }
                	
                } else {
                	
                    for (var i = 0; i < data[index].list.length; i++) {
                        if (data[index].list[i] && (data[index].list[i].newPreferential == 1 ||data[index].list[i].newPreferential == 9)) {
                            data[index].list.splice(i, 1);
                            i--;
                        }

                    }
                }
            }
            $.each(data, function(i, list) {
                if (list.list.length > 0) {
                    var newProduct = '<p class="prefecture-title">' + list.categoryName + '</p><div class="ui divider"></div>';
                    $.each(list.list, function(i, n) {
                        var tagName;
                        if (n.tagName != '普通') {
                            tagName = '<div class="tag">' + n.tagName + '</div>';
                        } else {
                            tagName = ''
                        }
                        var interest;
                        if(n.rate == 0) {
                            interest = n.userInterest + '<span style="font-size:14px">%</span>';
                        }
                        else {
                            interest = n.rate + '<span style="font-size:14px">%</span>+'+n.userPlatformSubsidy + '<span style="font-size:14px">%</span>';
                        }
                        var htmlActive = '<div class="object" id="' + i + '"><div class="object-title"><img src="'+static_root+'/assets/images/fire.png " /><span>' + n.title + '</span><img src="'+static_root+'/assets/images/bankName.png" style="margin-left:15px" /><span>' + n.acceptingBank + '</span>' + tagName + '</div><div class="object-data"><div class=object-data__number><div class="data-number__earnings"><div class="number-earnings__number">' + interest + '</div><div class="number-earnings__text">预计年化收益</div></div><div class="data-number__dates"><div class="number-earnings__number dates">' + n.duration + '<span style="font-size:14px">天</span></div><div class="number-earnings__text">期限</div></div><div class="data-number__money"><div class="number-earnings__number dates">' + n.moneyMin + '<span style="font-size:14px">元</span></div><div class="number-earnings__text">起投金额</div></div></div><div class="object-data__progress"><div class="ui progress progress__cover"><div class="bar bar__cover" style="width:' + n.percent * 2.5 + 'px"></div></div><div class="data-progress__text">剩余可投：' + n.able + '元</div></div><a href="/product/detail.html?id='+n.id+'"><button class="ui primary button object-data__button">立即抢购 </button></a></div></div>';
                        newProduct = newProduct + htmlActive;
                    });
                    newProduct = newProduct + ruleHtml;
                    $('#new').append(newProduct);
                    $('#new').children('#0').css('border-top', 0);
                } else {
                    //alert('数据出错，请刷新页面')
                }
            });
        },
        //调用出错执行的函数
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest.status);
            console.log(XMLHttpRequest.readyState);
            console.log(textStatus);
        }
    });
});
