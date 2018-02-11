<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo ($meta_title); ?></title>
    <meta name="keywords" content="<?php echo ($meta_keywords); ?>" />
    <meta name="description" content="<?php echo ($meta_description); ?>" />
    <meta name="author" content="<?php echo C('META_AUTHOR');?>" />
    <link rel="shortcut icon" href="<?php echo C('WEB_ROOT');?>/favicon.ico">
    <link href="<?php echo C('STATIC_ROOT');?>/ppmiao/css/reset.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo C('STATIC_ROOT');?>/ppmiao/css/css.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo C('STATIC_ROOT');?>/ppmiao/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo C('STATIC_ROOT');?>/ppmiao/js/flash.js"></script>
<script type="text/javascript">
	$(function(){
	$(".b1").click(function(){
        if($(this).next(".rightInner").css('display')=='none'){
            $(this).next(".rightInner").slideToggle(300);
            $(this).addClass('arrow07');
            $(this).next(".rightInner").parents('.b1Box').siblings().find('.rightInner').slideUp(500);
            $(this).parents('.b1Box').siblings().find('.b1').removeClass('arrow07');
        }else{
            $(this).next(".rightInner").slideUp(500);
            $(this).removeClass('arrow07');
        }
        
        
	})	
})
</script>
</head>

<body>
    <div class="headerBg">
    <div class="header w1003 clear">
        <div class="MCLogo fLe clear">
            <img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCLogo.png" />
        </div>
        <div class="MCPhone fLe clear">
            <img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCphone.png" />
        </div>
        <div class="header fRi clear">
            <ul>
                <?php if($path == 'index'): ?><li class="active"><a href="<?php echo C('WEB_ROOT');?>">首页</a></li> <?php else: ?> <li><a href="<?php echo C('WEB_ROOT');?>">首页</a></li><?php endif; ?>
                <?php if($path == 'product'): ?><li class="active"> <a href="<?php echo C('WEB_ROOT');?>/product/">理财产品</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/product/">理财产品</a></li><?php endif; ?>
                <?php if($path == 'security'): ?><li class="active"><a href="<?php echo C('WEB_ROOT');?>/security.html">安全保障</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/security.html">安全保障</a></li><?php endif; ?>
                <?php if($path == 'about'): ?><li class="active"> <a href="<?php echo C('WEB_ROOT');?>/about.html">关于我们</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/about.html">关于我们</a></li><?php endif; ?>
                <?php if($path == 'contact'): ?><li class="active"> <a href="<?php echo C('WEB_ROOT');?>/contact.html">联系我们</a></li> <?php else: ?> <li> <a href="<?php echo C('WEB_ROOT');?>/contact.html">联系我们</a></li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $(".userHeader").hover(function(){
            $(this).find(".pulldown-nav").addClass("hover");
            $(this).find(".uderHeaderDl").show();
        },function(){
            $(this).find(".uderHeaderDl").hide();
            $(this).find(".pulldown-nav").removeClass("hover");
        });
    });
</script>
    
    <div class="MCQuestionBigBoxBg">
        <div class="MCQuestionBigBox w1003 clear">
            <div class="MCQuestionLeft fLe clear">
            	<h5>常见问题</h5>
                <ul>
                	<li class="active"><a href="#">零钱喵</a></li>
                    <li><a href="#">票票喵</a></li>
                    <li><a href="#">注册登录</a></li>
                    <li><a href="#">充值提现</a></li>
                    <li><a href="#">安全保障</a></li>
                    <li><a href="#">投诉建议</a></li>
                </ul>
                <a href="#" class="joinUs">加入我们</a>
            </div>
            <div class="MCJoinUsRight fRi clear">
            	 <div class="MCJoinUsBox01 w797 clear">
                 	<div class="MCJoinUsBox01Title fLe clear">
                  		<h5>你的椅子空在这里</h5> 
                    </div>
                    
                    <div class="joinDetailsBox02 w797 clear">
                        <div class="b1Box">
                            <div class="b1 clear">
                                <div class="b1Left fLe clear">
                                    <h3>平面设计师</h3>
                                    <h4>Graphic Designer</h4>
                                    <p>招聘人数:<em>1</em>人</p>
                                </div>
                            </div>
                            <div class="rightInner fLe clear d1" style="display:none;">
                                <p class="p1">
                                    岗位职责Job Description<br/>
                                    1、负责社交媒体创意设计；<br/>
                                    2、利用社交媒体、多媒体平台进行品牌形象、产品推广等创意设计；<br/>
                                    3、有一定的用户体验理念，有良好的页面版式规划能力，并可独立进行创作设计；<br/>
                                    任职要求Qualifications<br/>
                                    1、艺术设计、计算机等相关专业专科及以上学历，熟悉社交媒体终端平台的特性；<br/>
                                    2、优秀的审美能力和独特的创意，有较强的平面设计和网页设计创意能力；<br/>
                                    3、有良好的沟通能力，对新技术、新领域的探索精神；<br/>
                                    4、敬业且具备高度的责任感，具备良好的沟通能力和团队合作精神。<br/>
                                </p>
                                <p class="p2">请发送您的简历至hr@stlc.cn，标题请注明所申请职位。</p>
                            </div>
                        </div>
                        
                        <div class="b1Box">
                            <div class="b1 clear">
                                <div class="b1Left fLe clear">
                                    <h3>产品经理（偏UE方向）</h3>
                                    <h4>Product Manager</h4>
                                    <p>招聘人数:<em>1</em>人</p>
                                </div>
                            </div>
                            <div class="rightInner fLe clear d1"  style="display:none;">
                                <p class="p1">
                                    岗位职责Job Description<br/>
                                    1、根据公司网站和APP产品的用户体验反馈，引导进行界面视觉优化，完成产品创新；<br/>
                                    2、基于用户行为研究、进行竞争对手竞品研究和数据分析，持续改进产品用户体验度；<br/>
                                    3、根据人机交互、界面设计等理论，配合产品经理、UI、开发团队完成产品升级迭代；<br/>
                                    4、确认产品交互流界面和展示的设计风格，达成符合市场用户需求的目标。<br/>
                                    任职要求Qualifications<br/>
                                    1、全日制大专及以上学历，美术、视觉、设计、人机交互等相关专业毕业；<br/>
                                    2、2年以上互联网行业设计从业经验，有web应用和移动APP产品的交互设计经验；<br/>
                                    3、熟练使用主流交互流程设计工具，如Axure、Visio、思维导图等；<br/>
                                    4、了解基于浏览器和客户端的技术，具备一定的html、CSS等代码技能；<br/>
                                    5、具有较强逻辑思维能力，熟练掌握业务需求分析、产品需求分解的技巧和方法。<br/>
                                </p>
                                <p class="p2">请发送您的简历至hr@stlc.cn，标题请注明所申请职位。</p>
                            </div>
                        </div>
                        
                         <div class="b1Box">
                            <div class="b1 clear">
                                <div class="b1Left fLe clear">
                                    <h3>金融产品经理</h3>
                                    <h4>Finance Product Manager</h4>
                                    <p>招聘人数:<em>1</em>人</p>
                                </div>
                            </div>
                            <div class="rightInner fLe clear d1"  style="display:none;">
                                <p class="p1">
                                    岗位职责Job Description<br/>
                                    1、负责拟定公司金融产品整体规划和设计要求，包括财务指标、规模、募资期、最低认购额及其他相关细节要求等；<br/>
                                    2、调研和收集金融资本市场、投资市场和同行业产品情况，根据客户及市场需求并结合项目实际情况，设计有竞争优势的金融产品，增强客户满意度；<br/>
                                    3、负责产品发行前的研究、包装和策划及文案；负责支持金融产品的销售，制作销售工具包，对已有金融产品进行优化和重新包装定位、组合，并开展相关产品培训及协助产品推介工作。负责售后服务和反馈信息收集，并建立产品档案；<br/>
                                    4、负责项目计划书、招募说明书、合伙协议等文书的撰写、美化及排版，对已有金融产品进行优化和重新包装定位、组合；负责撰写各类分析报告及实施方案；负责公司各类基金产品的方案设计以及产品推广方案的制定，从事过私募基金设计、产业投资基金设计并购基金设计者优先；<br/>
                                    5、负责对公司金融产品需求调研、设计、产品创新及产品生命周期进行全过程管理；<br />
                                    6、制定金融产品创新方法、管理原则和市场需求分析方法、工具，确保金融产品的实用性、安全性和便捷性。<br/>
                                    7、对产品的长期发展战略提出建设性意见，为企业决策层提供讨论参考依据。<br/>
                                    任职要求Qualifications<br/>
                                    1、本科以上学历，金融相关专业；<br/>
                                    2、曾在基金公司、信托公司、银行投行部等从事过信托设计，基金产品设计和理财产品设计，3年以上产品设计岗位工作经验；<br/>
                                    3、熟悉财务、法律、税务等知识，会做盈利预测分析、行业分析等；<br/>
                                    4、熟悉各种产品的交易结构和风险控制措施；<br/>
                                    5、身体健康、品行端正、态度良好、积极向上、普通话流利，良好的谈判技能及沟通能力；<br/>
                                    6、精通各种办公软件。
                                </p>
                                <p class="p2">请发送您的简历至hr@stlc.cn，标题请注明所申请职位。</p>
                            </div>
                        </div>
                        
                        <div class="b1Box">
                            <div class="b1 clear">
                                <div class="b1Left fLe clear">
                                    <h3>风控经理</h3>
                                    <h4>Risk Control Manager</h4>
                                    <p>招聘人数:<em>1</em>人</p>
                                </div>
                            </div>
                            <div class="rightInner fLe clear d1"  style="display:none;">
                                <p class="p1">
                                    岗位职责Job Description<br/>
                                    1、负责公司业务的风险控制工作，根据公司风险管理控制体系要求，制定风险管控方案，构建业务风险评价及预警体系，组建风控团队；<br/>
                                    2、负责审查项目资料，撰写风控报告，对项目的可行性和风险的可控性进行审核，组织、参与项目评审，根据具体情况提出风险防范建议；<br/>
                                    3、负责协助建立、完善风险管理流程，控制项目风险；<br/>
                                    4、负责通过评审项目的条件与担保措施的落实监督； 负责督促检查已开展项目监管情况，提出风险预警，控制项目风险；<br/>
                                    5、负责组织、协调、推进贷后管理和资产保全工作；审核资产保全方案，监控保全过程；<br />
                                    6、负责建立、维护风险管理信息库并建立业务风险追偿机制。<br/>
                                    任职要求Qualifications<br/>
                                    1、专科及以上学历，法律、金融、审计等经济类专业。具有律师、注册会计师（CPA)、金融分析师 (CFA)执照优先；<br/>
                                    2、具有3年以上投行工作经验或大型担保公司风险控制从业经验，或3年以上信托、基金、证券公司法律风控工作经验。能熟练运用信用风险识别与计量的方法和工具，精通财务分析、项目评估、组合风险管理等理论方法与实务操作；具有较强的分析判断能力、风险识别能力、风险防范方案设计能力。具有较强的综合协调和组织能力。<br/>
                                    3、有扎实的经济、金融、投资等领域的相关理论知识，熟悉各类金融产品结构及交易结构设计，对不同种类资产的内在逻辑、运行路径十分熟悉；<br/>
                                    4、思维缜密、原则性强、正直，成熟、稳重，具有高度的责任心与敬业精神，良好的团队精神与领导能力。<br/>
                                </p>
                                <p class="p2">请发送您的简历至hr@stlc.cn，标题请注明所申请职位。</p>
                            </div>
                        </div>
                        
                        <div class="b1Box">
                            <div class="b1 clear">
                                <div class="b1Left fLe clear">
                                    <h3>品牌运营官</h3>
                                    <h4>Brand Operations Officer</h4>
                                    <p>招聘人数:<em>1</em>人</p>
                                </div>
                            </div>
                            <div class="rightInner fLe clear d1"  style="display:none;">
                                <p class="p1">
                                    岗位职责Job Description<br/>
                                    1、负责线上的推广、分析，进行各类线上活动的策划工作并落实执行；<br/>
                                    2、负责完成策划方案、品牌推广方案、报告的撰写；<br/>
                                    3、负责完成品牌、产品推广的效果评估，提出改进方案；<br/>
                                    4、负责公司品牌和产品宣传推广的文案策划，公司品牌和产品的宣传广告与新闻稿撰写，公司媒介推广计划的执行及跟踪；<br/>
                                    5、分析客户需求，挖掘产品亮点与卖点，实现产品描述的多样化、内容化、品牌化；<br />
                                    6、能够独立创作和完成稿件的撰写。较强的文字功底，文笔流畅，有较丰富的金融知识；<br/>
                                    7、擅长新媒体策划、事件策划；<br/>
                                    任职要求Qualifications<br/>
                                    1、新闻、公关、广告、市场营销专业本科及以上学历；<br/>
                                    2、有互联网产品策划经验或互联网公司工作经验，熟悉互联网金融产品，例如p2p产品者优先；<br/>
                                    3、有敏锐市场洞察力，富有创新精神；有较强的文字撰写能力与表达沟通能力；<br/>
                                    4、具有较好的文字功底和文案策划能力，并有独立文案策划案例；<br/>
                                    5、语言表达能力强，文笔流畅，有文案撰写，编辑，策划，推广工作2年以上工作经验；<br/>
                                    6、良好的人际沟通能力；<br/>
                                    7、面试时可提供市场策划和文案作品优先。<br/>
                                    8、良好的逻辑思维能力，思路清晰，知识面较宽，学习新事物快，敏感性强，善于挖掘热点和敏感话题。<br/>
                                </p>
                                <p class="p2">请发送您的简历至hr@stlc.cn，标题请注明所申请职位。</p>
                            </div>
                        </div>
                        
                        <div class="b1Box">
                            <div class="b1 clear">
                                <div class="b1Left fLe clear">
                                    <h3>品牌策划经理</h3>
                                    <h4>Brand Planning Manager</h4>
                                    <p>招聘人数:<em>1</em>人</p>
                                </div>
                            </div>
                            <div class="rightInner fLe clear d1"  style="display:none;">
                                <p class="p1">
                                    岗位职责Job Description<br/>
                                    1、负责石头理财品牌推广计划策划案的制定；<br/>
                                    2、负责石头理财品牌事件营销的策划与传播；<br/>
                                    3、负责石头理财相关推广运营活动的管理;<br/>
                                    4、负责协调参与行业活动的行程以及外部相关媒体资源的联系。<br/>
                                    任职要求Qualifications<br/>
                                    1、3年以上互联网公司品牌营销工作经验；<br/>
                                    2、有很强的新闻、事件策划组织能力；<br/>
                                    3、熟悉各类媒体（尤其是移动互联网的生态）环境，自身有一定的核心媒体资源优先；<br/>
                                    4、具备精深的稿件能力和品牌包装能力，准确把控传播内容的有效性；<br/>
                                    5、擅长沟通和团队合作，有较强的文字功底。<br/>
                                </p>
                                <p class="p2">请发送您的简历至hr@stlc.cn，标题请注明所申请职位。</p>
                            </div>
                        </div>
                        
                        <div class="joinDetailsLink fLe clear">
                            <h2>我们在等你！</h2>
                        </div>
                        
                    </div>
                     
                </div>	
            </div>
        </div>
    </div>




    <!--footer begin-->
    <script>
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "//hm.baidu.com/hm.js?b4c8a1efe52094b6a6e6c9b6c12ebdeb";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
<div class="MCSecurityBox05Bg fLe clear">
    <div class="MCSecurityBox05 w1003 clear">
        <h5>票票喵APP</h5>
        <a href="<?php echo C('WEB_ROOT');?>/download/android-debug.apk" style="margin-left:254px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg07.png" /></a>
        <a href="#" style="margin-left:65px;"><img src="<?php echo C('STATIC_ROOT');?>/ppmiao/images/MCSecurityImg08.png" /></a>
    </div>
</div>
<div class="footerInnerBg fLe clear">
    <div class="footerInner w1003 clear">
        <ul>
            <a href="<?php echo C('WEB_ROOT');?>/about.html">关于我们&nbsp;&nbsp;|&nbsp;&nbsp;</a>
            <a href="<?php echo C('WEB_ROOT');?>/join.html">加入我们&nbsp;&nbsp;|&nbsp;&nbsp;</a>
            <a href="<?php echo C('WEB_ROOT');?>/contact.html">联系我们&nbsp;&nbsp;</a>
        </ul>
        <p>浙ICP备16003202号-1 |  Copyrights      2015 PiaoPiaoMiaopiaojulicaitouziguanli</p>
    </div>
</div>
    <!--footer end-->
    
    <div id="top"></div>
    
</body>
</html>