<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			O N L I N E &nbsp; U S E R S
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			<?php
				if ($onliners) {
					$guests = 0;
					print("<b>Users Currently Online:</b><br>\n");
					print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n");
					foreach ($onliners as $onliner) {
						if ($onliner["user_id"] > 0) {
							print("<tr>\n");
							print("<td width=\"170\" valign=\"top\">".($onliner["user_id"] ? "<a href=\"".$this->buildUrl(array("action" => "profile", "user_id" => $onliner["user_id"]))."\">".$onliner["user_name"]."</a>" : "Guest")."</td>\n");
							print("<td valign=\"top\" style=\"padding-left: 20px\">[<a href=\"".$onliner["url"]."\">".$onliner["title"]."</a>]</td>\n");
							print("</tr>\n");
						} else {
							$guests++;
						}
					}
					print("</table>\n");
					
					print("And ".$guests." guest".($guests == 1 ? "" : "s").".");
				} else {
					print("No users currently online. (Impossible.)");
				}
			?>		
		</td>
	</tr>
</table>
