<?php
session_start();


if (!isset($_SESSION["name"])) {
  header("Location: login.php");
}

?>
<!DOCTYPE html>
<html lang="en-US">
<title>斗地主 | madmic</title>
<meta charset="UTF-8">

<head>
  <script type="application/javascript" src="js/jquery-1.8.3.min.js"></script>
  <script type="application/javascript" src="js/yinghua.js"></script>

  <style>
    body {
      background: url('img/bg/beijing_chunying.png')no-repeat;
      background-size: 100% 100%;
      height: 100%;
      background-attachment: fixed;
      overflow: hidden;

    }

    .right1 {
      background: url('img/ui/btn_yibanchang.png');
      background-size: 100% 100%;
      height: 20%;
      width: 23%;
      top: 15%;
      position: absolute;
      right: 13%;
      float: right;
    }

    .right2 {
      background: url('img/ui/btn_dajiangsai.png');
      background-size: 100% 100%;
      height: 20%;
      width: 25%;
      top: 35%;
      position: absolute;
      right: 11%;
      float: right;
    }

    .right3 {
      background: url('img/ui/btn_yourenchang.png');
      background-size: 100% 100%;
      height: 20%;
      width: 25%;
      top: 55%;
      position: absolute;
      right: 11%;
      float: right;
    }

    .yourenchang {
      height: 200px;
     
      top: 15%;
      left:60%;
      position: absolute;
      
   
      display: none;
    }

    .title_bg {
      height: 60px;
      width: 500px;
      margin-top: 60px;
    }

    .friendroom {
      z-index: 1;
      position: absolute;
      left: 40%;
      top: 35%;
      height: 40px;
    }

    .img_return2 {
      z-index: 1;
      position: absolute;
      left: 8%;
      top: 35%;
      height: 40px;
    }

    .btn_create_room {
      z-index: 1;
      position: absolute;
      left: 20%;
      top: 90%;
    }

    .tips {
      z-index: 1;
      position: absolute;
      left: 85%;
      top: 89%;
    }

    .free {
      z-index: 1;
      position: absolute;
      left: 39%;
      top: 122%;
      font-size: 25px;
      color: white;
    }
    .bg_abmatch1 {
      z-index: 1;
      position: absolute;
      left: 0%;
      top: 72%;
    }
    .bg_abmatch {
      z-index: 1;
      position: absolute;
      left: 0%;
      top: 152%;
    }

    .w_joinroom {
      z-index: 1;
      position: absolute;
      left: 12%;
      top: 158%;
    }

    .roominput {
      display: none;
      z-index: 1;
      position: absolute;
      left: 30%;
      top: 166%;
      height: 30px;
      width: 200px;
    }

    .roomsubmit {
      display: none;
      z-index: 1;
      position: absolute;
      left: 40%;
      top: 190%;
      height: 30px;
      width: 100px;
      background: red;
      color: white;
    }

    .tips2 {
      z-index: 1;
      position: absolute;
      left: 85%;
      top: 162%;
    }

    .free2 {
      z-index: 1;
      position: absolute;
      left: 39%;
      top: 200%;
      font-size: 25px;
      color: white;
    }
  </style>
</head>

<body>
  <div id="right">
    <div class="right1">

    </div>
    <div class="right2">

    </div>
    <div class="right3" onclick="right()">

    </div>

  </div>
  <div class="yourenchang" id="charubiaoqian1">
    <img class="title_bg" src='img/ui/title_bg.png'>
    <img class="friendroom" src='img/ui/txt_friendroom.png'>
    <a class="img_return2" href='index.php'><img src='img/ui/img_return2.png'></a>
    <br><br><a href='createroom.php?n=1'><img src='img/ui/bg_abmatch.png' class="bg_abmatch1"></a>
    <a href='createroom.php?n=1'><img src='img/ui/btn_create_room.png' class="btn_create_room"></a>
    <img class="tips" src='img/ui/tips.png'>
    <p class="free">限时免费</p>
    <a href='#'><img src='img/ui/bg_abmatch.png' class="bg_abmatch" onclick="inroom()"></a>
    <a href='#'><img src='img/ui/w_joinroom.png' class="w_joinroom" onclick="inroom()"></a>
    <form action="api/room/" method="get">
      <input type="text" placeholder="房间号" name="roomid" class="roominput" id="inroom">
      <input type="submit" value="确定" class="roomsubmit" id="btn_inroom">
    </form>
    <img src='img/ui/tips.png' class="tips2">
    <p class="free2">限时免费</p>
    </right>
  </div>

  <!--  底部按钮 -->
  <div style="display: flex; ">
    <a href="huanjuese.php"></a>
    <div style="background: url('img/ui/btn1.png'); background-size: 100% 100%;height: 9%;width:6%;position: absolute;left:55%;  bottom:1%;"></div> </a>
    <div style="background: url('img/ui/btn4.png'); background-size: 100% 100%;height: 9%;width:6%;position: absolute;left:61%;  bottom:1%;"></div>
    <div style="background: url('img/ui/btn0.png'); background-size: 100% 100%;height: 9%;width:6%;position: absolute;left:67%;  bottom:1%;"></div>
    <div style="background: url('img/ui/btn2.png'); background-size: 100% 100%;height: 9%;width:6%;position: absolute;left:73%;  bottom:1%;"></div>
    <div style="background: url('img/ui/btn5.png'); background-size: 100% 100%;height: 9%;width:6%;position: absolute;left:79%;  bottom:1%;"></div>
    <div style="background: url('img/ui/btn3.png'); background-size: 100% 100%;height: 9%;width:6%;position: absolute;left:85%;  bottom:1%;"></div>
    <div style="background: url('img/ui/btn6.png'); background-size: 100% 100%;height: 11%;width:7%;position: absolute;left:91%;  bottom:1%;"></div>
  </div>
  <!--  右边设置栏 -->
  <div>
    <div style="background: url('img/ui/btn_set.png'); background-size: 100% 100%;height: 10%;width:5.5%;position: absolute;right:2%;top:2%;">
    </div>
    <div style="background: url('img/ui/btn_xinshouyindao.png'); background-size: 100% 100%;height: 9%;width:5%;position: absolute;right:2%;top:12%;">
    </div>
    <div style="background: url('img/ui/btn_trophy.png'); background-size: 100% 100%;height: 9%;width:5%;position: absolute;right:2%;top:22%;">
    </div>

  </div>
  <!--  数据库连接 -->

  <!--  人物信息 -->
  <div>
    <div style="background: url('img/ui/bg_2.png'); background-size: 100% 100%;height: 17%;width:25%;position: absolute;left:2%;top:2%;z-index:-4;">

    </div>
    <div style="background: url('<?php echo "" . $_SESSION['duanwei'] . "" ?>'); background-size: 100% 100%;height: 13%;width:7%;position: absolute;left:3%;top:4%;">

    </div>
    <div style="background: url('<?php echo "" . $_SESSION['chengwei'] . "" ?>'); background-size: 100% 100%;height: 5%;width:15%;position: absolute;left:10%;top:5%;z-index:1;">

    </div>
    <div style="position: absolute;width:15%;height:5%;z-index:1;overflow:none;left:15%;top:8.5%;">
      <p style="  font-size:25px; color:white;font-size: 25px;"><?php echo "" . $_SESSION['name'] . "" ?></p>

    </div>
    <div style="background: url('img/ui/star_dark.png'); background-size: 100% 100%;height: 5%;width:3%;position: absolute;left:1%;top:10%;z-index:1;">

    </div>
    <div style="background: url('img/ui/star.png'); background-size: 100% 100%;height: 3.5%;width:2%;position: absolute;left:3%;top:14%;z-index:1;">

    </div>
    <div style="background: url('img/ui/star.png'); background-size: 100% 100%;height: 2.5%;width:1.5%;position: absolute;left:4.5%;top:17%;z-index:1;">

    </div>

  </div>
  <!--  立绘 -->
  <div style="background: url('<?php echo "" . $_SESSION['bglihui'] . "" ?>'); background-size: 100% 100%;height: 100%;width:40%;position: absolute;left:4.5%;top:16.5%;z-index:1;" id='lihui'>


  </div>
  <!--  金币/钻石 -->
  <div>
    <div style="background: url('img/ui/bg_0.png'); background-size: 100% 100%;height: 6.5%;width:11%;position: absolute;top:2%;left:50%;z-index:1;">

    </div>
    <div style="background: url('img/ui/gold0.png'); background-size: 100% 100%;height: 6%;width:4%;position: absolute;top:2%;left:48%;z-index:1;">

    </div>
    <div style="background: url('img/ui/bg_0.png'); background-size: 100% 100%;height: 6.5%;width:11%;position: absolute;top:2%;left:63%;z-index:1;">

    </div>
    <div style="background: url('img/ui/gold1.png'); background-size: 100% 100%;height: 6%;width:4%;position: absolute;top:2%;left:61%;z-index:1;">

    </div>
    <p style=" z-index:1;position: absolute; left:52.5%;top:1.3%; font-size:25px; color:white;"><?php echo "" . $_SESSION['jinbi'] . "" ?></p>


    <p style=" z-index:1;position: absolute; left:65.5%;top:1.3%; font-size:25px; color:white;"><?php echo "" . $_SESSION['hunyu'] . "" ?></p>
  </div>


  <script>
    function right() {
      document.getElementById("right").style.display = "none";
      document.getElementById("charubiaoqian1").style.display = "block";


    }

    function inroom() {
      document.getElementById("inroom").style.display = "block";
      document.getElementById("btn_inroom").style.display = "block";
    }
  </script>
 
</body>

</html>