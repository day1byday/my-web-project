<!DOCTYPE html>
<html lang="en-US">
<title>斗地主 | madmic</title>
    <meta charset="UTF-8">
    <?php  session_start();  ?>  
<head>
<script type="application/javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="application/javascript" src="js/yinghua.js"></script>

</head>
        <body style="padding:0;margin:0;overflow:hidden;height: 100%;" >   
        <div style="position:absolute; width:100%; height:100%; z-index:-5; left:0; top:0;">
<img  src="img/bg/indoor.png" height="100%" width="100%" style="left:0; top:0;">
</div>

<!-- 返回-->
<div>
<img  src="img/ui/img_return1_bg.png"  style="height:80px;position: absolute;left:2%; top:2%;">
<a href="index.php?id=<?php echo $_SESSION['id'];  ?>"><img  src="img/ui/img_return1.png"  style="position: absolute;left:0; top:0;"></a>
<img  src="img/ui/txt_friendroom.png"  style="position: absolute;left:8%; top:3.7%; width:140px;">
</div>
<!-- 玩家位置-->  
<div>
    <!-- 房主位置--> 
<img  src="img/ui/frame_gold.png"  style="position: absolute;left:14%; top:15%;">  
<img  src="img/ui/character_bg.png"  style="position: absolute;left:14%; top:15%;">
<!-- 玩家2位置--> 
<img  src="img/ui/frame_gold.png"  style="position: absolute;left:35%; top:15%;">  
<img  src="img/ui/character_bg.png"  style="position: absolute;left:35%; top:15%;">
<!-- 玩家3位置--> 
<img  src="img/ui/frame_gold.png"  style="position: absolute;left:56%; top:15%;">  
<img  src="img/ui/character_bg.png"  style="position: absolute;left:56%; top:15%;">
</div>
<?php 
//获取玩家数据
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';

$config = require __DIR__ . '/config.php';
DB::init($config['db']);

$roomid = $_SESSION['roomid']; 

$room = DB::selectOne('SELECT player, type, reward, prepare FROM room WHERE id = ?', [$roomid]);
if (!$room) {
    echo json_encode(array("code" => "404"));
    exit();
}
$arr_player = $room['player'];
$type = $room['type'];
$reward = $room['reward'];
$arr_prepare = $room['prepare'];

$player = json_decode($arr_player);
$prepare = json_decode($arr_prepare);



?>
  <!-- 开始/准备按钮--> 
  <div id="kaishi">

  
  </div>
    <!-- 规则--> 
    <div>
  <img  src="img/ui/btn_choosen.png"  style="position: absolute;left:80%; top:22%;"> 
  <p style=" z-index:1;position: absolute; left:83%; top:21.2%; font-size:25px; color:black;"><?php echo $type; ?></p> 
  <img  src="img/ui/btn_choosen.png"  style="position: absolute;left:80%; top:32%;"> 
  <p style=" z-index:1;position: absolute; left:81.5%; top:31.2%; font-size:25px; color:black;"><?php echo $reward; ?></p> 
  <img  src="img/ui/btn_choosen.png"  style="position: absolute;left:80%; top:42%;"> 
  <p style=" z-index:1;position: absolute; left:80.8%; top:42.2%; font-size:18px; color:black;">房号：<?php echo $roomid; ?></p> 
  </div>
   <!-- 按钮--> 
   <div>
   <img  src="img/ui/btn_small.png"  style="position: absolute;left:80.5%; top:55%;width:180px;height:70px;"> 
  <p style=" z-index:1;position: absolute; left:82.6%; top:54.2%; font-size:25px; color:#ffae4c;">添加电脑</p>   
  <img  src="img/ui/btn_small.png"  style="position: absolute;left:80.5%; top:65%;width:180px;height:70px;"> 
  <p style=" z-index:1;position: absolute; left:82.6%; top:64.2%; font-size:25px; color:#ffae4c;">调整装扮</p>
   </div>



    <!-- 玩家1房主绘制--> 
    <div>
    <?php
   
if($player[0]!="0"){
   // echo "<script>alert('触发了');</script>";
    $playerid=$player[0];
    $prow = DB::selectOne('SELECT * FROM player WHERE id = ?', [$playerid]);
    if ($prow) {
?>
    <img  src="<?php echo $prow["lihui"];  ?>"  style="position: absolute;left:14.5%; top:16.5%; width:290px;"> 
    <img  src="img/ui/namebg.png"  style="position: absolute;left:13.5%; top:72%; height:80px;">
    <img  src="<?php echo $prow["duanwei"];  ?>"  style="position: absolute;left:13.5%; top:72%; height:80px;"> 
    <img  src="img/ui/star_dark.png"  style="position: absolute;left:12.7%; top:76%; height:25px;">
    <img  src="img/ui/star.png"  style="position: absolute;left:13.5%; top:78.1%; height:20px;">   
    <img  src="img/ui/star.png"  style="position: absolute;left:14.5%; top:79.2%; height:15px;">
    <img  src="img/ui/ready.png"  style="position: absolute;left:20%; top:58.5%; display:<?php if($prepare[0]=="1"){echo "block";}else{echo "none";} ?>" id="ready" >
    <img  src="<?php echo $prow["chengwei"];  ?>"  style="position: absolute;left:19%; top:70%; height:40px;">
    <p style=" z-index:1;position: absolute; left:18%; top:71.6%;font-size:25px; color:white;"><?php echo $prow["name"];  ?></p> 
    <img  src="img/ui/roomowner.png"  style="position: absolute;left:14.5%; top:16%; "> 
    <?php
}
} ?>
    </div>
     <!-- 玩家2绘制--> 
     <div id='player2'>
     <?php
   
   if($player[1]!="0"){
      // echo "<script>alert('触发了');</script>";
       $playerid=$player[1];
       $prow = DB::selectOne('SELECT * FROM player WHERE id = ?', [$playerid]);
       if ($prow) {
   ?>
     <img  src='<?php echo $prow["lihui"];  ?>'  style='position: absolute;left:35.5%; top:16.5%; width:290px;'> 
     <img  src='img/ui/namebg.png'  style='position: absolute;left:34.5%; top:72%; height:80px;'>
     <img  src='<?php echo $prow["duanwei"];  ?>'  style='position: absolute;left:34.5%; top:72%; height:80px;'> 
     <img  src='img/ui/star_dark.png' style='position: absolute;left:33.7%; top:76%; height:25px;'>
     <img  src='img/ui/star.png'  style='position: absolute;left:34.5%; top:78.1%; height:20px;'> 
     <img  src='img/ui/star.png'  style='position: absolute;left:35.5%; top:79.2%; height:15px;'>
     <img  src='img/ui/ready.png'  style='position: absolute;left:41%; top:58.5%;display:<?php if($prepare[1]=="1"){echo "block";}else{echo "none";} ?> ' id='ready'>
     <img  src='<?php echo $prow["chengwei"];  ?>'  style='position: absolute;left:40%; top:70%; height:40px;'>
     <p style=' z-index:1;position: absolute; left:39%; top:71.6%;font-size:25px; color:white;'><?php echo $prow["name"];  ?></p>
     <?php
}
} ?>
    </div>
     <!-- 玩家3绘制--> 
     <?php
   
   if($player[2]!="0"){
      // echo "<script>alert('触发了');</script>";
       $playerid=$player[2];
       $prow = DB::selectOne('SELECT * FROM player WHERE id = ?', [$playerid]);
       if ($prow) {
   ?>
     <div id='player3'>
     <img  src='<?php echo $prow["lihui"];  ?>'  style='position: absolute;left:56.5%; top:16.5%; width:290px;'>
     <img  src='img/ui/namebg.png'  style='position: absolute;left:55.5%; top:72%; height:80px;'>
     <img  src='<?php echo $prow["duanwei"];  ?>'  style='position: absolute;left:55.5%; top:72%; height:80px;'>
     <img  src='img/ui/star_dark.png' style='position: absolute;left:54.7%; top:76%; height:25px;'>
     <img  src='img/ui/star.png'  style='position: absolute;left:55.5%; top:78.1%; height:20px;'> 
     <img  src='img/ui/star.png'  style='position: absolute;left:56.5%; top:79.2%; height:15px;'>
     <img  src='img/ui/ready.png'  style='position: absolute;left:62%; top:58.5%;display:<?php if($prepare[2]=="1"){echo "block";}else{echo "none";} ?> ' id='ready'>
     <img  src='<?php echo $prow["chengwei"];  ?>'  style='position: absolute;left:61%; top:70%; height:40px;'>
     <p style=' z-index:1;position: absolute; left:60%; top:71.6%;font-size:25px; color:white;'><?php echo $prow["name"];  ?></p>
     <?php
}
} ?>
    </div>
<?php if($_SESSION["id"]==$player[0]){
    if($prepare[0]=="1" && $prepare[1]=="1" && $prepare[2]=="1"){
        $imgsrc="img/ui/btn_start.png";
    
        echo "<img src='".$imgsrc."'  style='position: absolute;left:40%; top:85%;' onclick='begin()'>";
    }else{
        $imgsrc="img/ui/btn_start_no.png";
        echo "<img src='".$imgsrc."'  style='position: absolute;left:40%; top:85%;'>";
    }


} else{
echo"<img  src='img/ui/btn_ready.png'  style='position: absolute;left:40%; top:85%;' onclick='ready()' >";

} 


?>
    


        </body>
	





<script>
  
 //退出房间
 window.onbeforeunload   = function(event) { 
    


    if (window.XMLHttpRequest)
{
    // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行的代码
    xmlhttp=new XMLHttpRequest();
}
else
{    
    //IE6, IE5 浏览器执行的代码
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}
xmlhttp.onreadystatechange=function()
{
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
        
        
    }
}
//xmlhttp.open("GET","roomout.php",true);
//xmlhttp.send();







}
function begin(){
    
    if (window.XMLHttpRequest)
    {
        // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行的代码
        xmlhttp4=new XMLHttpRequest();
    }
    else
    {    
        //IE6, IE5 浏览器执行的代码
        xmlhttp4=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp4.onreadystatechange=function()
    {
        if (xmlhttp4.readyState==4 && xmlhttp4.status==200)
        {
          location.reload();
        }
    }
    xmlhttp4.open("GET","./api/deal/index.php?id1=<?php echo $player[0]; ?>&id2=<?php echo $player[1]; ?>&id3=<?php echo $player[2]; ?>&roomid=<?php echo $roomid; ?>",true);
    xmlhttp4.send();
    
    

}




function ready(){
    if (window.XMLHttpRequest)
    {
        // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行的代码
        xmlhttp3=new XMLHttpRequest();
    }
    else
    {    
        //IE6, IE5 浏览器执行的代码
        xmlhttp3=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp3.onreadystatechange=function()
    {
        if (xmlhttp3.readyState==4 && xmlhttp3.status==200)
        {
            location.reload();
        }
    }
    xmlhttp3.open("GET","ready.php",true);
    xmlhttp3.send();

}


//刷新页面
setInterval(function(){
        if (window.XMLHttpRequest)
    {
        // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行的代码
        xmlhttp2=new XMLHttpRequest();
    }
    else
    {    
        //IE6, IE5 浏览器执行的代码
        xmlhttp2=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp2.onreadystatechange=function()
    {
        if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
        {
          //alert(xmlhttp2.responseText);
          if(xmlhttp2.responseText=="-1"){
            location.reload();


          }else if(xmlhttp2.responseText=="2"){
            alert("游戏开始了！");
            window.location.href='game.php';
          }
        }
    }
    xmlhttp2.open("GET","server.php",true);
    xmlhttp2.send();


    },1000);
	


    </script>
</html>