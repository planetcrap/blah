<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			D E B U G &nbsp; C R A P
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none; font-size: 10px">

			<?php
				print("Ignore this, fools. :)<br><br>\n");
				print("Total number of queries: ".count($GLOBALS[BLAH_GLOBALVAR]["queries"])."<br><br>\n");
				if ($GLOBALS[BLAH_GLOBALVAR]["queries"]) {
					foreach ($GLOBALS[BLAH_GLOBALVAR]["queries"] as $q) {
						print($q["query"]."<br>\n(".$q["time"].")<br><br>\n");
					}
				}
			?>
			
		</td>
	</tr>
</table>
