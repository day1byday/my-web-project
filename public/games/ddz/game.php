<?php session_start();
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en-US">
<title>斗地主 | madmic</title>
<meta charset="UTF-8">

<head>
    <style>
        body {
            background: url('img/bg/Table_Dif12324.png')no-repeat;
            background-size: 100% 100%;
            height: 100%;
            background-attachment: fixed;


        }
       
    </style>
</head>

<body>
    <!--地主底牌 -->
      <?php
    $arr_player=$_SESSION["player"];//当局游戏的三个人的玩家id
    $id=$_SESSION["id"];//当前登录的玩家id
    $roomid=$_SESSION["roomid"];
   
     //$arr_player=$_GET["player"];
     //$id=$_GET["id"];
    // $roomid=$_GET["roomid"];

    //API获取当前玩家的信息
 $url = $base_url . '/api/findplayer/?id='.$id;

 $json = file_get_contents($url);
 //print_r($json);
 $re = json_decode($json,true);
  $player1_name=$re["name"];
  $player1_touxiang=$re["touxiang"];
  $player1_identity=$re["identity"];
 
 $re2=ltrim(rtrim($re["card"],"]"),"[");
 $player1_card= explode(',',$re2);
 //print_r($player1_card);
$re3=ltrim(rtrim($re["overcard"],"]"),"[");
 $overcard= explode(',',$re3);
//print_r($overcard);



//API获取牌局的信息
$url = $base_url . '/api/findroom/?roomid='.$roomid;

 $json = file_get_contents($url);
   //print_r($json);
 $re = json_decode($json,true);
 $player_arr=$re["player"];
 $begin=$re["begin"];
 if($re["discard"]==""){
  $discard=[];   
 }else{
       $re2=ltrim(rtrim($re["discard"],"]"),"[");
   $discard=explode(',',$re2);
 }

   
   $prepare=$re["prepare"];
  //API获取下家信息
   $url = $base_url . '/api/findplayer/next/?id='.$id."&player=".$arr_player;
 $json = file_get_contents($url);
 //print_r($json);
 $re = json_decode($json,true);
  $player2_name=$re["name"];
  $player2_touxiang=$re["touxiang"];
  $player2_identity=$re["identity"];
 $re2=ltrim(rtrim($re["card"],"]"),"[");
 $player2_card= explode(',',$re2);
 $player2_id=$re["id"];
 //print_r($arr_card);
//API获取上家信息
   $url = $base_url . '/api/findplayer/up/?id='.$id."&player=".$arr_player;
 $json = file_get_contents($url);
 //print_r($json);
 $re = json_decode($json,true);
  $player3_name=$re["name"];
  $player3_touxiang=$re["touxiang"];
  $player3_identity=$re["identity"];

 $re2=ltrim(rtrim($re["card"],"]"),"[");
 $player3_card= explode(',',$re2);
 $player3_id=$re["id"];
 //print_r($arr_card);
 
 //API获取当前我的状态
  $url = $base_url . '/api/state/?id='.$id."&roomid=".$roomid;
 $json = file_get_contents($url);
 $re = json_decode($json,true);
 $state=$re["state"];

 
?>
    <div>
        <img src="img/ui/tab_choose.png" style="position: absolute;z-index:-1;">
        <img src='img/<?php echo $overcard[0]; ?>.png' width='60px' height='80px' style="margin-top: 20px;margin-left:40px;">
        <img src='img/<?php echo $overcard[1]; ?>.png' width='60px' height='80px' style="margin-top: 20px;margin-left:5px;">
        <img src='img/<?php echo $overcard[2]; ?>.png' width='60px' height='80px' style="margin-top: 20px;margin-left:5px;">
    </div>
    <!--地主底牌结束 -->

     <!--弃牌堆 -->
    <div >
        <?php 
        for($i=0;$i<count($discard);$i++){
        ?>
    <img id="<?php if(is_numeric($discard[$i])) {echo $discard[$i]; } else{ $discard[$i]=substr($discard[$i], 0, -1);  echo substr($discard[$i],1); }  ?>" src='img/<?php if(is_numeric($discard[$i])) {echo $discard[$i]; } else{ echo substr($discard[$i],1); }  ?>.png' width='120px' height='180px' style='margin-left:-55px;position: absolute; top: 28%;left:<?php echo 27+($i*3); ?>%;'>
    <?php } ?>
    
    </div>
     <!--弃牌堆结束 -->
    <!--player1 牌 -->    
    <div class="player1_card">
        <?php 
        for($i=0;$i<count($player1_card);$i++){
        ?>
    <img id="<?php if(is_numeric($player1_card[$i])) {echo $player1_card[$i]; } else{ $player1_card[$i]=substr($player1_card[$i], 0, -1);  echo substr($player1_card[$i],1);}  ?>"
    src='img/<?php if(is_numeric($player1_card[$i])) {echo $player1_card[$i]; } 
    else{  echo substr($player1_card[$i],1);}  ?>.png' width='120px' height='180px' style='margin-left:-55px;position: absolute; bottom: 8%;left:<?php echo 17+($i*3); ?>%;' onclick="select(this,<?php echo $i; ?>)">
    <?php } ?>
    
    </div>
    <!--player1 牌结束 --> 

    <!--player1 信息 -->   
    
    <div style="position: absolute;bottom:5%;left:3%;">
    <img src="img/ui/chatlog.png" width="140px" >
    <img src="<?php echo $player1_touxiang;  ?>" width="137px" style="position: absolute;bottom:31.2%;left:3.1%;border-radius: 25px;">
    <div style="width:140px;height:40px;text-align:center;">
    <p style="z-index:1;font-size:16px; color:white;"><?php echo $player1_name;  ?></p> 
    </div>
    
    </div>

    <!--player1 信息结束 -->  

 <!--player2 信息 -->    
 <div style="position: absolute;top:20%;right:5%;">
    <img src="img/ui/chatlog.png" width="140px" >
    <img src="<?php echo $player2_touxiang;  ?>" width="137px" style="position: absolute;bottom:31.2%;left:3.1%;border-radius: 25px;">
    <div style="width:140px;height:40px;text-align:center;">
    <p style="z-index:1;font-size:16px; color:white;"><?php echo $player2_name;  ?></p> 
    </div>
   <img src='img/54.png' width='100px'style='position: absolute;z-index:1;left:13%;top:95%'>
   <p style="z-index:1;font-size:16px; color:white;position: absolute;top:150%;left:30%;">剩<?php echo count($player2_card);  ?>张</p> 
    </div>

    <!--player2 信息结束 -->  

  <!--player3 信息 -->    
  <div style="position: absolute;top:20%;left:3%;">
    <img src="img/ui/chatlog.png" width="140px" >
    <img src="<?php echo $player3_touxiang;  ?>" width="137px" style="position: absolute;bottom:31.2%;left:3.1%;border-radius: 25px;">
    <div style="width:140px;height:40px;text-align:center;">
    <p style="z-index:1;font-size:16px; color:white;"><?php echo $player3_name;  ?></p> 
    </div>
   <img src='img/54.png' width='100px'style='position: absolute;z-index:1;left:13%;top:95%'>
   <p style="z-index:1;font-size:16px; color:white;position: absolute;top:150%;left:30%;">剩<?php echo count($player3_card);  ?>张</p> 
    </div>

    <!--player3 信息结束 -->  
    
    <!--玩家身份信息 -->  
<div style="width:140px;height:40px;text-align:center;position: absolute;bottom:27%;left:3%;">
    <p style="z-index:1;font-size:20px; color:white;"><?php echo $player1_identity;  ?></p> 
    </div>
  <div style="width:140px;height:40px;text-align:center;position: absolute;top:14%;right:5%;">
    <p style="z-index:1;font-size:20px; color:white;"><?php echo $player2_identity;  ?></p> 
    </div>  
     <div style="width:140px;height:40px;text-align:center;position: absolute;top:14%;left:3%;">
    <p style="z-index:1;font-size:20px; color:white;"><?php echo $player3_identity;  ?></p> 
    </div>      
    <!--玩家身份信息结束 -->  
    
<!--功能区 -->

<?php 


if($state=="1"){
echo"
<!-- 叫地主时的功能 !-->  
<img src='img/btn_jiaodizhu_up.png' style='position: absolute;top:60%;left:35%' onclick='jiaodizhu()'>
<img src='img/btn_bujiao2.png' style='position: absolute;top:60%;left: 48%'  onclick='bujiao()'>
<!-- 叫地主时的功能 !--> 

";
}
else if($state=="2"){
    echo "
    <!-- 抢地主时的功能 !-->  
<img src='img/btn_qiangdizhu_up.png' style='position: absolute;top:60%;left:35%' onclick='jiaodizhu()'>
<img src='img/btn_bujiao2.png' style='position: absolute;top:60%;left: 48%'  onclick='bujiao()' >
<!-- 抢地主时的功能 !-->  
    ";

}
else if($state=="3"){
    echo "
    <!-- 先手出牌时的功能 !-->  
    <img src='img/btn_chupai.png' style='position: absolute;top:60%;left:35%' onclick='chupai()'>
    <!-- 先手出牌时的功能 !--> 
    ";

}
else if($state=="4"){
    echo "
    <!-- 后手出牌时的功能 !-->  
    <img src='img/btn_chupai.png' style='position: absolute;top:60%;left:35%' onclick='chupai()'>
    <img src='img/btn_bujiao2.png' style='position: absolute;top:60%;left: 48%'  onclick='buyao()'>
    <!-- 后手出牌时的功能 !-->  
    ";

}
else if($state=="5"){
    echo "

    <!-- 要不起不能出牌时的功能 !-->  
    <img src='img/btn_chupai_hui.png' style='position: absolute;top:60%;left:35%' >
    <img src='img/btn_bujiao2.png' style='position: absolute;top:60%;left: 48%'  onclick='buyao()' >
    <!-- 要不起不能出牌时的功能 !-->  
    ";

}
?>
 



 





</body>


<script type="application/javascript" src="js/jquery-1.8.3.min.js"></script>

<script>
    var base_url = '<?php echo $base_url; ?>';
    //全局变量 选择的牌
    select_card=[];
    select_cardnum=[];
//选择出牌
function select(e,v){
    var id=e.id;
    if(e.style.bottom=="8%"){
        e.style.bottom="10%";
        select_card.push(v);
        select_cardnum.push(id);
    }else{
        e.style.bottom="8%";
        select_card.splice(jQuery.inArray(v,select_card),1);
        select_cardnum.splice(jQuery.inArray(id,select_cardnum),1);
    }
    
   
   

  //  console.log(select_card);
}


//叫地主操作
function jiaodizhu(){
    
          $.ajax({
          method: 'GET',
          url: base_url + '/api/up/?id=<?php echo $id; ?>&id2=<?php echo $player2_id; ?>&id3=<?php echo $player3_id; ?>&roomid=<?php echo $roomid; ?>&prepare=<?php echo $prepare; ?>',
          success: function (res) {
            var obj = JSON.parse(res);
            if(JSON.parse(obj.status)==200){
              
                location.reload();
                console.log(obj.status);
               
                }
              
            },

        });
 



}
//不叫操作
function bujiao(){
    $.ajax({
          method: 'GET',
          url: base_url + '/api/bujiao/?roomid=<?php echo $roomid; ?>&prepare=<?php echo $prepare; ?>&id=<?php echo $id; ?>',
          success: function (res) {
            var obj = JSON.parse(res);
            if(JSON.parse(obj.status)==200){
               
                location.reload();
                console.log(obj.status);
               
                }
              
            },

        });



}



//出牌操作
function chupai(){
    var weight=[17,16,15,15,15,15,14,14,14,14,13,13,13,13,12,12,12,12,11,11,11,11,10,10,10,10,9,9,9,9,8,8,8,8,7,7,7,7,6,6,6,6,5,5,5,5,4,4,4,4,3,3,3,3];
    var arr= select_cardnum;
    var type=-1;
    var w=-1;
    var compare=function(x,y){
        if(Number(x)<Number(y)){
            return -1;
        }else if(Number(x)>Number(y)){
            return 1;
        }else{
            return 0;
        }
    }
    var card_out=arr.sort(compare);
    console.log(card_out);
   if(card_out.length==1){
    //alert("单张,权值为："+weight[card_out[0]]);
    type=1;
    w=weight[card_out[0]];
   }else if(card_out.length==2){
    if(weight[card_out[0]]==weight[card_out[1]]){
        //alert("对子,权值为："+weight[card_out[0]]);
        type=2;
        w=weight[card_out[0]];
    }else if((weight[card_out[0]]==17 && weight[card_out[1]]==16) || (weight[card_out[0]]==17 && weight[card_out[1]]==16)){
       // alert("王炸,权值为：99,weight[card_out[0]]="+weight[card_out[0]]+",weight[card_out[1]]="+weight[card_out[1]]);
        type=0;
        w=99;
    }else{
        alert("错误牌型！"+weight[card_out[0]]);
    }
   }else if(card_out.length==3){
    if(weight[card_out[0]]==weight[card_out[1]] && weight[card_out[0]]==weight[card_out[2]]){
       // alert("三不带,权值为："+weight[card_out[0]]);
        type=3;
        w=weight[card_out[0]];
    }else{
        alert("错误牌型！");
    }
   }else if(card_out.length==4){
    if(weight[card_out[0]]==weight[card_out[1]] && weight[card_out[0]]==weight[card_out[2]] && weight[card_out[0]]==weight[card_out[3]]){
       // alert("炸弹,权值为："+weight[card_out[0]]);
        type=4;
        w=weight[card_out[0]];
    }else if((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[0]]==weight[card_out[2]]) || (weight[card_out[1]]==weight[card_out[2]] && weight[card_out[1]]==weight[card_out[3]]) ){
        //alert("三带一,权值为："+weight[card_out[2]]);
        type=5;
        w=weight[card_out[2]];
    }else{
        alert("错误牌型！");
    }
   }else if(card_out.length==5){
    
    if(Number(card_out[0])>5 && Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] ){
        //alert("顺子,权值为："+weight[card_out[0]]);
        type=6;
        w=weight[card_out[0]];
    }else if(((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[0]]==weight[card_out[2]]) && (weight[card_out[3]]==weight[card_out[4]])) || ((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[2]]==weight[card_out[4]]))){
       // alert("三带一对,权值为："+weight[card_out[2]]);
       type=7;
        w=weight[card_out[2]];
    }else{
        alert("错误牌型！");
    }
   }else if(card_out.length==6){
    
    if(Number(card_out[0])>5 && Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[4]]-1)==weight[card_out[5]]){
       // alert("顺子,权值为："+weight[card_out[0]]);
       type=8;
        w=weight[card_out[0]];
    }else if( Number(card_out[0])>5 && Number(card_out[1])>5 && ((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]])) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]]) )){
       // alert("连对,权值为："+weight[card_out[0]]);
        type=9;
        w=weight[card_out[0]];
    }else  if(weight[card_out[0]]==weight[card_out[1]] && weight[card_out[0]]==weight[card_out[2]] && weight[card_out[3]]==weight[card_out[4]] && weight[card_out[3]]==weight[card_out[5]]){
        //alert("飞机不带牌,权值为："+weight[card_out[0]]);
        type=10;
        w=weight[card_out[0]];
    }else if((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[1]]==weight[card_out[2]] && weight[card_out[2]]==weight[card_out[3]] && weight[card_out[4]]==weight[card_out[5]])  || (  weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]] && weight[card_out[3]]==weight[card_out[4]] && weight[card_out[4]]==weight[card_out[5]])  ){
      //  alert("四带一对,权值为："+weight[card_out[3]]);
      type=11;
        w=weight[card_out[3]];
    }else{
        alert("错误牌型！");
    }
   }else if(card_out.length==7){
    
    if(Number(card_out[0])>5 &&  Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[4]]-1)==weight[card_out[5]] && Number(weight[card_out[5]]-1)==weight[card_out[6]]){
        //alert("顺子,权值为："+weight[card_out[0]]);
        type=12;
        w=weight[card_out[0]];
    }else{
        alert("错误牌型！");
    }
   }else if(card_out.length==8){
    
    if( Number(card_out[0])>5 && Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[4]]-1)==weight[card_out[5]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[6]]-1)==weight[card_out[7]]){
        //alert("顺子,权值为："+weight[card_out[0]]);
        type=13;
        w=weight[card_out[0]];
    }else if((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[1]]==weight[card_out[2]] && weight[card_out[3]]==weight[card_out[4]] && weight[card_out[4]]==weight[card_out[5]] && weight[card_out[6]]==weight[card_out[7]]) || (weight[card_out[2]]==weight[card_out[3]]&&weight[card_out[3]]==weight[card_out[4]]&&weight[card_out[5]]==weight[card_out[6]]&&weight[card_out[6]]==weight[card_out[7]]) || (weight[card_out[1]]==weight[card_out[2]]&&weight[card_out[2]]==weight[card_out[3]]&&weight[card_out[4]]==weight[card_out[5]]&&weight[card_out[5]]==weight[card_out[6]]) ){
        //alert("飞机,权值为："+weight[card_out[2]]);
        type=14;
        w=weight[card_out[2]];
       }else if(Number(card_out[0])>5 && Number(card_out[1])>5 && ((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]] ) && (weight[card_out[6]]==weight[card_out[7]] ) ) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[5]]-1)==weight[card_out[6]]) )){
           //alert("连对,权值为："+weight[card_out[0]]);
           type=15;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==9){
    
    if(Number(card_out[0])>5 && Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[4]]-1)==weight[card_out[5]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[6]]-1)==weight[card_out[7]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]){
        //alert("顺子,权值为："+weight[card_out[0]]);
        type=16;
        w=weight[card_out[0]];
    }else if((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[1]]==weight[card_out[2]] && weight[card_out[3]]==weight[card_out[4]] && weight[card_out[4]]==weight[card_out[5]] && weight[card_out[6]]==weight[card_out[7]] && weight[card_out[7]]==weight[card_out[8]]) ){
        //alert("飞机三连不带牌,权值为："+weight[card_out[0]]);
        type=17;
        w=weight[card_out[0]];
       }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==10){
    
    if(Number(card_out[0])>5 && Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[4]]-1)==weight[card_out[5]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[6]]-1)==weight[card_out[7]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[8]]-1)==weight[card_out[9]]){
        //alert("顺子,权值为："+weight[card_out[0]]);
        type=18;
        w=weight[card_out[0]];
    }else if(Number(card_out[0])>5 && Number(card_out[1])>5 &&((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]]) && (weight[card_out[6]]==weight[card_out[7]])  && (weight[card_out[8]]==weight[card_out[9]])) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[7]]-1)==weight[card_out[8]] ) )){
           //alert("连对,权值为："+weight[card_out[0]]);
           type=19;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==11){
    
    if(Number(card_out[0])>5 && Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[4]]-1)==weight[card_out[5]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[6]]-1)==weight[card_out[7]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[8]]-1)==weight[card_out[9]]  && Number(weight[card_out[9]]-1)==weight[card_out[10]]){
        //alert("顺子,权值为："+weight[card_out[0]]);
        type=20;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==12){
    
    if(Number(card_out[0])>5 && Number(weight[card_out[0]]-1)==weight[card_out[1]] && Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[2]]-1)==weight[card_out[3]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[4]]-1)==weight[card_out[5]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[6]]-1)==weight[card_out[7]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[8]]-1)==weight[card_out[9]]  && Number(weight[card_out[9]]-1)==weight[card_out[10]] && Number(weight[card_out[10]]-1)==weight[card_out[11]]){
        //alert("顺子,权值为："+weight[card_out[0]]);
        type=21;
        w=weight[card_out[0]];
    }else if(Number(card_out[0])>5 && Number(card_out[1])>5 &&((weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]]) && (weight[card_out[6]]==weight[card_out[7]]) && (weight[card_out[8]]==weight[card_out[9]])  && (weight[card_out[10]]==weight[card_out[11]])) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[9]]-1)==weight[card_out[10]] ) )){
           //alert("连对,权值为："+weight[card_out[0]]);
           type=22;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==13){  
        alert("错误牌型！");
   }else if(card_out.length==14){
    
    if( Number(card_out[0])>5 && Number(card_out[1])>5 && (   (weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]]) && (weight[card_out[6]]==weight[card_out[7]]) && (weight[card_out[8]]==weight[card_out[9]])  && (weight[card_out[10]]==weight[card_out[11]]) && (weight[card_out[12]]==weight[card_out[13]])) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[9]]-1)==weight[card_out[10]] && Number(weight[card_out[11]]-1)==weight[card_out[12]] ) )){
          // alert("连对,权值为："+weight[card_out[0]]);
           type=23;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==15){  
        alert("错误牌型！");
   }else if(card_out.length==16){
    
    if( Number(card_out[0])>5 && Number(card_out[1])>5 && (   (weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]]) && (weight[card_out[6]]==weight[card_out[7]]) && (weight[card_out[8]]==weight[card_out[9]])  && (weight[card_out[10]]==weight[card_out[11]]) && (weight[card_out[12]]==weight[card_out[13]]) && (weight[card_out[14]]==weight[card_out[15]]) ) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[9]]-1)==weight[card_out[10]] && Number(weight[card_out[11]]-1)==weight[card_out[12]] && Number(weight[card_out[13]]-1)==weight[card_out[14]]) )){
           //alert("连对,权值为："+weight[card_out[0]]);
           type=24;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==17){  
        alert("错误牌型！");
   }else if(card_out.length==18){
    
    if( Number(card_out[0])>5 && Number(card_out[1])>5 && (   (weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]]) && (weight[card_out[6]]==weight[card_out[7]]) && (weight[card_out[8]]==weight[card_out[9]])  && (weight[card_out[10]]==weight[card_out[11]]) && (weight[card_out[12]]==weight[card_out[13]]) && (weight[card_out[14]]==weight[card_out[15]]) && (weight[card_out[16]]==weight[card_out[17]]) ) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[9]]-1)==weight[card_out[10]] && Number(weight[card_out[11]]-1)==weight[card_out[12]] && Number(weight[card_out[13]]-1)==weight[card_out[14]]  && Number(weight[card_out[15]]-1)==weight[card_out[16]]) )){
           //alert("连对,权值为："+weight[card_out[0]]);
           type=25;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }else if(card_out.length==19){  
        alert("错误牌型！");
   }else if(card_out.length==20){
    if( Number(card_out[0])>5 && Number(card_out[1])>5 && (   (weight[card_out[0]]==weight[card_out[1]] && weight[card_out[2]]==weight[card_out[3]]) && (weight[card_out[4]]==weight[card_out[5]]) && (weight[card_out[6]]==weight[card_out[7]]) && (weight[card_out[8]]==weight[card_out[9]])  && (weight[card_out[10]]==weight[card_out[11]]) && (weight[card_out[12]]==weight[card_out[13]]) && (weight[card_out[14]]==weight[card_out[15]]) && (weight[card_out[16]]==weight[card_out[17]]) && (weight[card_out[18]]==weight[card_out[19]]) ) && ((Number(weight[card_out[1]]-1)==weight[card_out[2]] && Number(weight[card_out[3]]-1)==weight[card_out[4]] && Number(weight[card_out[5]]-1)==weight[card_out[6]] && Number(weight[card_out[7]]-1)==weight[card_out[8]]  && Number(weight[card_out[9]]-1)==weight[card_out[10]] && Number(weight[card_out[11]]-1)==weight[card_out[12]] && Number(weight[card_out[13]]-1)==weight[card_out[14]]  && Number(weight[card_out[15]]-1)==weight[card_out[16]] && Number(weight[card_out[17]]-1)==weight[card_out[18]]) )){
           //alert("连对,权值为："+weight[card_out[0]]);
           type=26;
        w=weight[card_out[0]];
    }else{
        
        alert("错误牌型！");
    }
   }

   if(type!=-1){

    $arr_select_card=select_card.join(",");
    $arr_select_card="["+$arr_select_card+"]";
    
      $.ajax({
          method: 'GET',
          url: base_url + '/api/play/?roomid=<?php echo $roomid; ?>&prepare=<?php echo $prepare; ?>&id=<?php echo $id; ?>&num='+$arr_select_card+'&weight='+w+'&type='+type,
          success: function (res) {
            var obj = JSON.parse(res);
            if(JSON.parse(obj.status)==200){
               
                location.reload();
                console.log(obj.status);
               
                }
              
            },

        });
    
   }



}




//不出操作
function buyao(){
   $.ajax({
          method: 'GET',
          url: base_url + '/api/noplay/?roomid=<?php echo $roomid; ?>&prepare=<?php echo $prepare; ?>',
          success: function (res) {
            var obj = JSON.parse(res);
            if(JSON.parse(obj.status)==200){
               
                location.reload();
                console.log(obj.status);
               
                }
              
            },

        });
}


//与服务器进行数据交互
setInterval(function(){
    
     $.ajax({
          method: 'GET',
          url: base_url + '/gameserver.php?player=<?php echo $arr_player; ?>&id=<?php echo $id; ?>&prepare=<?php echo $prepare; ?>&roomid=<?php echo $roomid; ?>',
          success: function (res) {
           console.log(res);
            if(JSON.parse(res)==-1){
                location.reload();
                }else if(JSON.parse(res)==-2){
                    alert("游戏结束！");
            window.location.href='room.php';
                }
              
            },

        });
        


    },100);



</script>

</html>