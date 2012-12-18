<?php

$currentProducts = array(
	"Warcraft 3",
	"Neverwinter Nights",
	"Soldier of Fortune 2",
	"iPod",
	"Gamecube",
	"Playstation 2",
	"Final Fantasy X",
	"Rez",
	"Dungeon Siege");

$count = count($currentProducts);
$random = rand() % $count;
$choice = $currentProducts[$random];

?>
<table width="<?= $GLOBALS['pagewidth'] ?>" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle" colspan="2">
			B U Y &nbsp; N O N - C R A P &nbsp; S T U F F
		</td>
	</tr>
	<tr>
		<td align="center" width="<?= $GLOBALS['pagewidth'] ?>" class="block" style="padding: 2px">
			<form action="http://www.dealtime.com/GS2/GS2StatReDirect/" method="get" style="margin: 0px; padding: 0px;">
			<INPUT type="hidden" name="bParent" value="on">
			<INPUT type="hidden" name="nFormID" value="0">
			<INPUT type="hidden" name="nParentFormID" value="0">
			<INPUT type="hidden" name="linkin_id" value="2062673">
			<INPUT type="hidden" name="uid" value="1738108048"> 
			
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td>Compare Products & Prices:</td>
					<td>&nbsp;<INPUT size="20" maxLength="50" name="keyword" class="text" value="<?=$choice?>"> <INPUT type=submit value=Search name=submit></td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
</table>
