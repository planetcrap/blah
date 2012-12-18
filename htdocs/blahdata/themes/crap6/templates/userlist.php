<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			U S E R &nbsp; L I S T
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">

			<?php
				print("There are ".count($users)." registered users.<br><br>\n");

				// loop through $users
				if ($users) {
					print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n");
					foreach ($users as $user) {
						// letter?
						$firstChar = strtoupper(substr($user["name"], 0, 1));
						if ($curChar != $firstChar) {
							print("<tr>\n");
							print("<td></td><td>".($curChar ? "<br>" : "")."<b>".$firstChar."</b></td>\n");
							print("</tr>\n");
							
							$curChar = $firstChar;
						}
						
						// prepare profile url
						$url = $this->buildUrl(array("action" => "profile", "user_id" => $user["id"]));
						
						// prepare avatar icon
						if ($user["avatar_filename"]) {
							$avatarIcon = "<a href=\"".$url."\"><img src=\"".$this->config["custom_avatars_url"]."small-".$user["avatar_filename"]."\" width=\"20\" height=\"20\" border=\"0\" style=\"border: 1px solid #000000\"></a>";
						} else {
							$avatarIcon = "<img src=\"".IMAGE_URL."blank.gif\" width=\"22\" height=\"22\" border=\"0\">";
						}
						
						// print user
						print("<tr>\n");
						print("<td style=\"padding-right: 8px\">".$avatarIcon."</td>\n");
						print("<td valign=\"middle\" width=\"200\"><a href=\"".$url."\">".$user["name"]."</a></td>\n");
						print("<td valign=\"middle\">".$user["extra_company"]."</td>\n");
						print("</tr>\n");
					}
					print("</table>\n");
				}
			?>
			
		</td>
	</tr>
</table>
<br>
