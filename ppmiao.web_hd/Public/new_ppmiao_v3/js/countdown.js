function getRTime(){ 
var EndTime= new Date('2016/3/26 0:00:00'); //截止时间 
var NowTime = new Date(); 
var t =EndTime.getTime() - NowTime.getTime(); 
/*var d=Math.floor(t/1000/60/60/24); 
t-=d*(1000*60*60*24); 
var h=Math.floor(t/1000/60/60); 
t-=h*60*60*1000; 
var m=Math.floor(t/1000/60); 
t-=m*60*1000; 
var s=Math.floor(t/1000);*/
 
var h=Math.floor(t/1000/60/60%24);
var m=Math.floor(t/1000/60%60); 
var s=Math.floor(t/1000%60); 

if(h<=9){h="0"+h} 
if(m<=9){m="0"+m}
if(s<=9){s="0"+s}
 
document.getElementById("t_h").innerHTML = h; 
document.getElementById("t_m").innerHTML = m; 
document.getElementById("t_s").innerHTML = s; 
} 
setInterval(getRTime,1000); 
