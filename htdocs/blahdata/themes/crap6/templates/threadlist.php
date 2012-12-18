<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle" colspan="3">
			T H R E A D S
		</td>
	</tr>
	<?php
		if ($threads) {
			foreach ($threads as $thread) {
		
				// which icon?
				$threadUrl = $this->buildThreadUrl($thread);
				if ($this->threadHasNewComments($thread)) {
					$icon = "thread_new.gif";
					$newComments = $this->countNewThreadComments($thread);
					$iconUrl = $this->buildThreadUrl($thread, $thread["comment_count"] - $newComments + 1);
					$iconAlt = $newComments." unread";
				} else {
					$icon = "thread.gif";
					$newComments = 0;
					$iconUrl = $threadUrl;
					$iconAlt = "";
				}
				
				// print it
				print("<tr>\n");
				print('<td width="25" align="center" valign=\"top\" style="border-bottom: 1px solid #888888; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; background-color: #DDDDDD; padding: 4px 5px">');
				print("<a href=\"".$iconUrl."\"><img src=\"".IMAGE_URL.$icon."\" border=\"0\" width=\"14\" height=\"14\" alt=\"".$iconAlt."\"></a></td>");
				
				print("<td class=\"block\" style=\"border-left: none; border-right: none; padding: 2px 8px; \">\n");
				print("<a href=\"".$threadUrl."\"><b>".$thread[title]."</b></a>".($newComments ? " ($newComments new)" : ""));
				print("</td>\n");

				print("<td align=\"center\" valign=\"top\" class=\"block\" style=\"border-left: none; border-right: none; padding: 4px 8px; font-size: 11px; color: #555555\">\n");
				print($thread["comment_count"]);
				print("</td>\n");

				print("</tr>\n");
			}
		}
	?>
</table>
