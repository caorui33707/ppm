var IsPopup = false;
function IsPass() {
	if (IsPopup) {
		document.getElementById("index_bj").className = "hidden";
		document.getElementById("index_window").className = "hidden";
		IsPopup = false;
	} else {
		document.getElementById("index_bj").className = "index_bj abs";
		document.getElementById("index_window").className = "index_window abs";
		IsPopup = true;
	}
}

function isNumeric(s) {
    var reg = /^[+-]?\d+[.]?\d*$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isInteger(s) {
    var reg = /^[+-]?\d{1,9}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isFloat(s) {
    var reg = /^[+-]?\d+\.\d+$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isMoney(s) {
    var reg = /^\d{1,8}(\.\d{1,2}){0,1}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isTariff(s) {
    var reg = /^0(\.\d+){1,1}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isEmpty(s) {
    if (s.length == 0) {
        return true
    } else {
        return false
    }
}
function isAllowChar(s) {
    var reg = /^\w+$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isNotAllowChar(s) {
    var reg = /^[^\\ab]+$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}


function isChineseStr(str){ 
	var reg = /^[\u4E00-\u9FA5]+$/; 
	if(!reg.test(str)){ 
		return false; 
	} 
	return true; 
}

function isTwoBytesStr(s) {
    var reg = /^[^x00-xff]+$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isMatchLen(s, minLen, maxLen) {
    var len = s.replace(/[^\x00-\xff]/g, "**").length;
    if (maxLen == 0) {
        return true
    } else {
        if (len > maxLen || len < minLen) {
            return false
        }
    }
    return true
}
function isMatchSize(s, minVal, maxVal) {
    if (s < minVal || s > maxVal) {
        return false
    }
    return true
}
function isUserName(s) {
    s = trim(s);
    var reg = /^[a-zA-Z][a-zA-Z0-9_]{4,15}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isMobileNo(s) {
    var reg = /^\d{3,5}(-)?\d{3,8}(-)?\d{4,7}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isBankAccountNo(s) {
    var reg = /^(\d{5}[\s\-]?){3}\d{0,8}$/g;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isMobileNo(s) {
    var reg = /^1[34578]\d{9}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isEmail(s) {
    var reg = /^[_a-zA-Z0-9\-]+(\.[_a-zA-Z0-9\-]*)*@[a-zA-Z0-9\-]+([\.][a-zA-Z])+([\.a-zA-Z]+)$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isIP(s) {
    var reg = /^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])(\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])){3}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function isQQ(s) {
    var reg = /^\d{5,9}$/;
    if (reg.exec(s)) {
        return true
    } else {
        return false
    }
}
function validateRealName(s) {
    var reg = /^[\u4e00-\u9fa5]+$/;
    if (calLength(s) < 4 || calLength(s) > 20) {
        return false
    } else {
        if (!reg.exec(s)) {
            return false
        } else {
            return true
        }
    }
}
function calLength(leng) {
    var len = 0;
    for (var i = 0; i < leng.length; i++) {
        if (leng.charCodeAt(i) > 127 || leng.charCodeAt(i) == 94) {
            len += 2
        } else {
            len++
        }
    }
    return len
}
function validateNickname(val) {
    var patn = new RegExp('/s+|^c:\\con\\con|[%,*"s<>&]|\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8/is');
    if (val.length < 5 || val.length > 16) {
        return 1
    }
    if (patn.test(val)) {
        return 2
    }
    return 0
}
function checkDenyWords(content) {
    if (content == null || content.trim() == "") {
        return ""
    }
    var wordsArrays = new Array();
    wordsArrays = new Array("≡V≡", "≡v≡", "admin", "AIDS", "aids", "Aids", "asshole", "bitch", "bitch", "BITCH", "Dick", "fuck", "fuck", "FUCK", "G.M", "g.m", "G。M", "g。m", "GM", "GM", "gm", "Gm", "gM", "GT", "j8", "J8", "LB", "lb", "SARS", "SB", "sb", "SG", "suck", "阿扁", "阿拉法特", "艾滋", "艾滋病", "白鸟敏夫", "板垣征四郎", "包皮", "本·拉登", "本拉登", "笨屄", "笨逼", "屄", "逼", "逼毛", "冰毒", "布莱尔", "布什", "操顺网", "操妳", "操妳老妈", "操妳老母", "操妳妈", "操妳娘", "操妳祖宗", "操你", "操你大爷", "操你老妈", "操你老母", "操你妈", "操你妈个B", "操你妈个屄", "操你娘", "操你全家", "操你祖宗", "操死顺网", "曹刚川", "肏", "插妳", "插你", "朝鲜", "陈伯达", "陈良宇", "陈水扁", "陈云", "处女膜", "春药", "达赖", "达子", "打倒", "大花B", "大麻", "大卫教", "大血B", "代练", "代言", "带练", "党主席", "道教", "邓小平", "帝国主义", "屌", "东条英机", "东突暴动和独立", "东乡茂德", "东亚病夫", "毒贩", "独立", "二屄", "二逼", "法高", "法轮", "法轮功", "法西斯", "佛教", "佛祖", "干妳", "干妳老妈", "干妳老母", "干妳妈", "干妳娘", "干你", "干你老妈", "干你老母", "干你妈", "干你娘", "肛交", "高丽棒子", "膏药旗", "共产党", "共产主义", "共匪", "挂机", "观世音", "广田宏毅", "龟头", "郭伯雄", "国民党", "国务院", "海洛因", "贺国强", "黑社会", "黑手党", "胡锦涛", "胡启立", "胡耀邦", "花柳", "华国锋", "黄菊", "回回", "回教", "回良玉", "鸡八", "鸡巴", "鸡奸", "基督", "基督教", "激情图片", "激情写真", "妓女", "贾庆林", "奸", "江core", "江泽民", "蒋介石", "蒋经国", "蒋中正", "金日成", "金正日", "巨乳", "康生", "尻", "克林顿", "口交", "寇晓伟", "狂操", "拉登", "拉姆斯菲尔德", "烂B", "烂逼", "烂人", "老卵", "李长春", "李登辉", "李富春", "李洪志", "李岚清", "李鹏", "李瑞环", "李先念", "连战", "林彪", "淋病", "刘华清", "刘淇", "刘少奇", "刘云山", "六四", "吕秀莲", "乱伦", "罗干", "裸体写真", "裸照", "妈的顺网", "妈祖", "马晓轶", "毛泽东", "梅毒", "梅津美治郎", "美国", "蒙古", "密宗", "民进党", "摩门教", "木村兵太郎", "穆罕默德", "穆斯林", "内挂", "妳老母的", "妳妈的", "南蛮子", "你姥", "你姥姥的", "你妈的", "捻", "娘", "女干", "女马", "屁眼", "嫖", "平沼骐一郎", "仆街", "普京", "强奸", "强奸犯", "乔石", "亲民党", "屈江涛", "去死", "人妻", "日", "日GT", "日本", "日本鬼子", "日你妈", "日你娘", "日死顺网", "乳房", "乳头", "萨达姆", "塞你爸", "塞你公", "塞你老母", "塞你母", "塞你娘", "傻B", "傻屄", "傻逼", "傻比", "山本五十六", "上妳", "上你", "社会主义", "圣母", "圣战", "兽交", "死顺网", "松冈洋右", "松井石根", "宋楚瑜", "宋平", "素人", "孙文", "孙逸仙", "孙中山", "他妈的", "台独", "台联", "台湾", "台湾独立", "陶铸", "天安门", "天皇", "土肥原贤二", "外挂", "汪东兴", "王刚", "王乐泉", "王迁唐", "王兆国", "尉健行", "温家宝", "我操", "我考", "我靠", "吴邦国", "吴官正", "吴俊青", "吴仪", "武藤章", "西藏独立", "西藏喇嘛", "希拉克", "希拉里", "希特勒", "系统", "鲜族", "萧汉华", "小矶国昭", "小泉纯一郎", "小日本", "新党", "新闻出版总署", "性交", "性欲", "鸦片", "阳痿", "姚依林", "摇头丸", "耶和华", "耶稣", "耶稣", "叶剑英", "一贯道", "一丝不挂", "伊斯兰", "阴道", "阴蒂", "阴茎", "阴毛", "淫", "永野修身", "幼齿", "俞正声", "早泄", "曾培炎", "曾庆红", "张春桥", "张德江", "张立昌", "赵紫阳", "真理教", "真世界", "中共", "中共中央", "中国共产党", "中华民国", "中南海", "中宣部", "中央电视台", "周恩来", "周永康", "朱德", "朱金容基", "朱容基", "朱镕基", "转法轮", "装屄", "装逼", "总理", "作爱", "做爱", "扣逼", "抠逼", "客服");
    for (var k = 0; k < wordsArrays.length; k++) {
        if (content.indexOf(wordsArrays[k]) != -1) {
            return wordsArrays[k]
        }
    }
    return ""
}
function validateCheckCode(val) {
    var patn = /^[0-9]{4}$/;
    if (patn.test(val)) {
        return true
    }
    return false
}
function validatePassword(obj) {
    var str = obj.value;
    var patn = /^[0-9a-zA-Z]\w{3,13}[0-9a-zA-Z]$/;
    if (patn.test(str)) {
        return 0
    }
    return 1
}
function ltrim(s) {
    return s.replace(/^\s*/, "")
}
function rtrim(s) {
    return s.replace(/\s*$/, "")
}
function trim(s) {
    return ltrim(rtrim(s))
}
function validateIdCard(obj) {
    var iSum = 0;
    var strIDno = obj.value;
    var idCardLength = strIDno.length;
    if (!/^\d{17}(\d|x)$/i.test(strIDno)) {
        return 1
    }
    var year = strIDno.substring(6, 10);
    if (year < 1900 || year > 2078) {
        return 3
    }
    strIDno = strIDno.replace(/x$/i, "a");
    sBirthday = strIDno.substr(6, 4) + "-" + Number(strIDno.substr(10, 2)) + "-" + Number(strIDno.substr(12, 2));
    var d = new Date(sBirthday.replace(/-/g, "/"));
    if (sBirthday != (d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate())) {
        return 3
    }
    var now = new Date();
    var age = getAge(d.getFullYear(), d.getMonth() + 1, d.getDate());
    if (age < 18) {
        return 4
    }
    return 0
}
function validateIdCardAge(obj) {
    var iSum = 0;
    var strIDno = obj.value;
    var idCardLength = strIDno.length;
    if (!/^\d{17}(\d|x)$/i.test(strIDno)) {
        return 1
    }
    var year = strIDno.substring(6, 10);
    if (year < 1900 || year > 2078) {
        return 3
    }
    strIDno = strIDno.replace(/x$/i, "a");
    sBirthday = strIDno.substr(6, 4) + "-" + Number(strIDno.substr(10, 2)) + "-" + Number(strIDno.substr(12, 2));
    var d = new Date(sBirthday.replace(/-/g, "/"));
    if (sBirthday != (d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate())) {
        return 3
    }
    var now = new Date();
    var age = getAge(d.getFullYear(), d.getMonth() + 1, d.getDate());
    if (age < 18) {
        return 4
    }
    return 0
}
function getAge(year, month, day) {
    var now = new Date();
    nowMonth = now.getMonth() + 1;
    nowYear = now.getFullYear();
    result = nowYear - year;
    if (month > nowMonth) {
        result--
    } else {
        if (month == nowMonth) {
            nowDay = now.getDate();
            if (day > nowDay) {
                result--
            }
        }
    }
    return result
}
String.prototype.len = function() {
    var str = this;
    return str.replace(/[^\x00-\xff]/g, "**").length
};
function validateIdCard(obj) {
    var aCity = {
        11 : "北京",
        12 : "天津",
        13 : "河北",
        14 : "山西",
        15 : "内蒙古",
        21 : "辽宁",
        22 : "吉林",
        23 : "黑龙江",
        31 : "上海",
        32 : "江苏",
        33 : "浙江",
        34 : "安徽",
        35 : "福建",
        36 : "江西",
        37 : "山东",
        41 : "河南",
        42 : "湖北",
        43 : "湖南",
        44 : "广东",
        45 : "广西",
        46 : "海南",
        50 : "重庆",
        51 : "四川",
        52 : "贵州",
        53 : "云南",
        54 : "西藏",
        61 : "陕西",
        62 : "甘肃",
        63 : "青海",
        64 : "宁夏",
        65 : "新疆",
        71 : "台湾",
        81 : "香港",
        82 : "澳门",
        91 : "国外"
    };
    var iSum = 0;
    var strIDno = obj.value;
    var idCardLength = strIDno.length;
    if (!/^\d{17}(\d|x)$/i.test(strIDno)) {
        return 1
    }
    if (aCity[parseInt(strIDno.substr(0, 2))] == null) {
        return 2
    }
    var year = strIDno.substring(6, 10);
    if (year < 1900 || year > 2078) {
        return 3
    }
    strIDno = strIDno.replace(/x$/i, "a");
    sBirthday = strIDno.substr(6, 4) + "-" + Number(strIDno.substr(10, 2)) + "-" + Number(strIDno.substr(12, 2));
    var d = new Date(sBirthday.replace(/-/g, "/"));
    if (sBirthday != (d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate())) {
        return 3
    }
    for (var i = 17; i >= 0; i--) {
        iSum += (Math.pow(2, i) % 11) * parseInt(strIDno.charAt(17 - i), 11)
    }
    if (iSum % 11 != 1) {
        return 1
    }
    var words = new Array();
    words = new Array("11111119111111111", "12121219121212121");
    for (var k = 0; k < words.length; k++) {
        if (strIDno.indexOf(words[k]) != -1) {
            return 1
        }
    }
    return 0
}
function GetVerifyBit(id) {
    var result;
    var nNum = eval(id.charAt(0) * 7 + id.charAt(1) * 9 + id.charAt(2) * 10 + id.charAt(3) * 5 + id.charAt(4) * 8 + id.charAt(5) * 4 + id.charAt(6) * 2 + id.charAt(7) * 1 + id.charAt(8) * 6 + id.charAt(9) * 3 + id.charAt(10) * 7 + id.charAt(11) * 9 + id.charAt(12) * 10 + id.charAt(13) * 5 + id.charAt(14) * 8 + id.charAt(15) * 4 + id.charAt(16) * 2);
    nNum = nNum % 11;
    switch (nNum) {
    case 0:
        result = "1";
        break;
    case 1:
        result = "0";
        break;
    case 2:
        result = "X";
        break;
    case 3:
        result = "9";
        break;
    case 4:
        result = "8";
        break;
    case 5:
        result = "7";
        break;
    case 6:
        result = "6";
        break;
    case 7:
        result = "5";
        break;
    case 8:
        result = "4";
        break;
    case 9:
        result = "3";
        break;
    case 10:
        result = "2";
        break
    }
    return result
}
function f_check_zhornumorlett(str) {
    var regu = /^[0-9a-zA-Z\u4e00-\u9fa5]+$/;
    if (regu.test(str)) {
        return true
    }
    return false
}
function f_check_zhornumorlett_(str) {
    var regu = /^(([0-9a-zA-Z\u4e00-\u9fa5])|(-))+$/;
    if (regu.test(str)) {
        return true
    }
    return false
}

function getByteLen(val) {
    var len = 0;
    for (var i = 0; i < val.length; i++) {
         var a = val.charAt(i);
         if (a.match(/[^\x00-\xff]/ig) != null) 
        {
            len += 2;
        }
        else
        {
            len += 1;
        }
    }
    return len;
}