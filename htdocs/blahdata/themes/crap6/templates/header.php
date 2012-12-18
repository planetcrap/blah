<?php
// figure out page width
if (!$pageWidth = $_SESSION[USER_SESSIONVAR]["extra_pagewidth"]) {
	$pageWidth = 750;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $title ?></title>
  <meta name="keywords" content="Games, Discussion, Rants, Chat">
  <meta name="description" content="The Crappiest Discussion Site On The Web! (Pseudo-TM)">
  <meta name="language" content="en">
  <meta name="content-type" content="text/html; charset=iso-8859-1">
  <meta name="robots" content="index, follow">
  <meta name="author" content="Hendrik Mans">
  <meta name="copyright" content="Hendrik Mans, 1998-2002">
  <link rel="shortcut icon" href="http://www.planetcrap.com/favicon.ico" >
  <link rel="stylesheet" href="<?= IMAGE_URL ?>default.css" type="text/css">
  <style>
  	TABLE.block {
  		width: <?=$pageWidth?>
  	}
  </style>


</head>
<body bgcolor="#666666" text="#000000"
	topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" style="padding: 0px; margin: 0px">


<script language="JavaScript" src="http://crapnet.planetcrap.com/crapbar.php"></script>

<div style="padding: 8px 20px">


<?php
	//$this->template("marketbanker_banner.php");
?>

<?php
/*
<div align="center" style="font-size: 11px; color: #FFFFFF">
<?
  $rand = rand() % 10000;
?>
<IFRAME HEIGHT=60 WIDTH=468 FRAMEBORDER=0 SCROLLING=NO MARGINHEIGHT=0 MARGINWIDTH=0 SRC="http://adproject.net/servlet/ServeServlet?space=125&random=<?=$rand?>&code=iframe"><a href="http://adproject.net/servlet/RedirectServlet?space=125&random=<?=$rand?>" target=_top><img border=0 src="http://adproject.net/servlet/ServeServlet?space=125&ret=img&random=<?=$rand?>"></a></IFRAME><br>
<a href="http://www.adproject.net/adspaces.jsp?site=89" style="color: #CCCCCC">OMG!!$@ Advertise on PlanetCrap! Click here!</a>
</div>
<br>
*/
?>

<div align="center">
<a href="<?= $this->buildUrl() ?>"><img src="<?= IMAGE_URL ?>logo.gif" border="0" width="479" height="249" alt="PlanetCrap 6.0!"></a>
</div>
<!--
<span style="color: #666666"><a href="http://www.schardt.de" style="color: #666666; font-size: 8px">Coaching Wiesbaden</a><a href="http://www.schardt.de" style="color: #666666; font-size: 8px">Systemische Supervision Wiesbaden</a></span>
-->
<?php
	$this->template("menu.php");
	//$this->template("textads.php");
	//$this->template("marketbanker.php");
	$this->template("motd.php");
?>
