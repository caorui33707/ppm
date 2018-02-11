<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>常见问题</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no,target-densitydpi = medium-dpi">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-touch-fullscreen" content="YES">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link href="<?php echo C('STATIC_ROOT');?>/mobile/css/css.css?v=1.0" rel="stylesheet" type="text/css" media="all"/>
    <script type="text/javascript" src="<?php echo C('STATIC_ROOT');?>/js/jquery.min.js"></script>
    <script>
    document.documentElement.style.webkitTouchCallout = "none";//禁止弹出菜单
    document.documentElement.style.webkitUserSelect = "none";//禁止选中
    document.onselectstart=function(){return false;}
    document.oncontextmenu=function(){return false;}
</script>
</head>

<body id="questionBox">
<div class="wrap">
    <div id="questions">
        <ul>
            <li class="fold">
                <h5><b class="arrowLeft"></b>什么叫银行承兑汇票？</h5>
                <div class="foldContent">
                    <p>银行承兑汇票是由银行开具的到期兑付的书面凭证。根据《票据法》相关规定：银行承兑汇票由银行承兑，银行承诺到期后会无条件兑付该票据金额给予该银承的所有人。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>什么叫无条件兑付？</h5>
                <div class="foldContent">
                    <p>根据《票据法》相关规定：商业汇票是出票人签发的，委托付款人在指定日期无条件支付确定的金额给收款人或者持票人的票据。在“票票喵”推出的投资产品均为银行承兑汇票，所以根据该条文规定，由银行无条件兑付。票据到期后，银行将按照票面金额无条件支付给该票据的所有人。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>银行承兑汇票由谁验真伪？</h5>
                <div class="foldContent">
                    <p>票票喵平台质押的商业汇票均由银行和平台专业人员双重验真。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>质押票据由谁保管和托收？</h5>
                <div class="foldContent">
                    <p>“票票喵”平台与平安银行签订了保管与托收协议，所有质押票据均由合作银行进行保管与托收，保障票据安全。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>到期以后，票据如何质权？</h5>
                <div class="foldContent">
                    <p>投资人委托“票票喵”将票据交由银行托收解付，再将解付款打入平台在银行的托管账户，按投资份额原路径返还给投资人。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>票据权利质押合法吗？</h5>
                <div class="foldContent">
                    <p>根据《票据法》规定：汇票可以设定质押，且根据《担保法》第七十五条,汇票权利可以质押 。两部法律都明确了汇票权利质押的合法性。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>项目没有募集成功怎么办？</h5>
                <div class="foldContent">
                    <p>由第三方支付将已收到的资金按原路径返还给投资人。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>投资者需要支付什么费用？</h5>
                <div class="foldContent">
                    <p>投资者目前注册“票票喵”平台会员进行投资费用全免。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>提现会出现逾期吗？</h5>
                <div class="foldContent">
                    <p>本网站属于票据理财，银行到期无条件承兑，除网络及不可抗力原因，否则基本不会出现逾期情况。
                        票票喵平台与其它网贷平台有什么不同之处？
                        所有理财产品都有对应的银行承兑汇票作为质押，确保投资者保本保息；
                        所有风控参照银行风控，对票据验真，保管，托收全程配合银行完成；
                        票票喵平台目前只做基于银行承兑汇票的低风险产品，平台不设担保，符合监管要求。
                        票票喵平台所有资金均由第三方完成支付及账户全程托管，所有标的票据均由银行进行保管及托收，平台不接触投资人资金和借款人标的票据，无跑路风险。</p>
                </div>
            </li>
            <li class="fold">
                <h5><b class="arrowLeft"></b>投资支付后是否可以取消？</h5>
                <div class="foldContent">
                    <p>您在投资后账户资金将被冻结，这时是不允许取消的，若此标满标并放款后，您账户上的冻结金额自动将转入该标借款人账户。若此标流标，则您账户上的冻结金额将原路返回到您的账户。</p>
                </div>
            </li>


        </ul>


    </div>


</div>
</body>
</html>
<script>
    $(function(){
        $("li>h5","#questions").bind("click",function(){
            var li=$(this).parent();
            if(li.hasClass("fold")){
                li.removeClass("fold");
                $(this).find("b").removeClass("arrowLeft").addClass("arrowUp");
                li.find(".foldContent").slideDown('fast');
            }else{
                li.addClass("fold");
                $(this).find("b").removeClass("arrowUp").addClass("arrowLeft");
                li.find(".foldContent").slideUp('fast');
            }
        });
    });
</script>
</body>
</html>