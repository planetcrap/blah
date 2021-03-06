<?php
	// determine if comment is new
	if ($lastread) {
		$commentIsNew = ($comment["when_posted"] > $lastread);
	}
?>
<a name="<?= $comment['num'] ?>"></a>
<table width="750" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td align="left" valign="top" width="30%" style="border-bottom: 1px solid #777777; border-right: 1px solid #777777; border-left: 1px solid #DDDDDD; border-top: 1px solid #DDDDDD; background-color: #C0C0C0; padding: 8px; <?= $commentIsNew ? 'background-image: url(/blahdata/themes/crap6/images/newcorner.gif); background-repeat: no-repeat' : '' ?>">
			<?php
				// comment information

				// load user data?
				if ($comment["author_id"]) {
					// if there's an actual user account to go with this comment...
					if ($author = $this->loadUser($comment["author_id"])) {
						$comment["author_name"] = $author["name"];
						$comment["author_email"] = ($author["show_email"] == "Y" ? $author["email"] : "");
						$comment["author_url"] = $author["url"];
					}
				}

				// avatar?
				if ($author["avatar_filename"]) {
					$avatar = "<a href=\"/users/".$author["id"]."/\"><img src=\"".$this->config["custom_avatars_url"].$author["avatar_filename"]."\" border=\"0\" width=\"42\" height=\"42\" style=\"border: 1px solid #000000\"></a>";
				}


				// display everything
				print("<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>\n");
				if ($avatar) {
					print("<td valign=\"top\" style=\"padding-right: 5px\">$avatar</td>\n");
				}
				print("<td class=\"comment-info\" align=\"right\" valign=\"top\">\n");

				// post number
				// GAK - Added the host/ip as a title to the post number
				print("<a href=\"/topics/".$comment['topic_id']."/".($comment["num"]-1)."/#comments\" title=\"".(strlen($comment["author_host"]) > 0 ? $comment["author_host"] : $comment["author_ip"])."\">#".$comment["num"]."</a> by\n");
				
				// author name
				if ($comment["author_id"]) {
					print("<a href=\"/users/".$comment["author_id"]."/\"><b>".$comment["author_name"]."</b></a>");
				} else {
					print("\"<b>".$comment["author_name"]."</b>\"");
				}
				print("<br>\n");

				// datetime of comment
				print($comment["when_posted"]."<br>\n");
				
				// email
				if ($comment["author_email"]) {
					print("<a href=\"mailto:".$comment["author_email"]."\"><img src=\"".IMAGE_URL."email.gif\" border=\"0\" alt=\"".$comment["author_email"]."\"></a>\n");
				}
				
				// url
				if ($comment["author_url"]) {
					print("<a href=\"".$this->addhttp($comment["author_url"])."\"><img src=\"".IMAGE_URL."homepage.gif\" border=\"0\" alt=\"".$comment["author_url"]."\"></a>\n");
				}
				
				print("</td></tr></table>\n");
				
				
			?>
		</td>
		<td align="left" valign="top" width="70%" class="comment" style="border-bottom: 1px solid #888888; border-right: 1px solid #888888; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; background-color: #EEEEEE; padding: 8px">
			<?php
				
				// comment text

				// comment title
				if ($comment["title"]) {
					// prepare some stuff
					if (strlen($comment["title"]) > 40) {
						$comment["title"] = substr($comment["title"], 0, 37)."...";
					}
					print("<b>".$comment["title"]."</b><br>\n");
				}
				
				if ($comment["is_deleted"] == "Y") {
					print("<span style=\"color: #884444\"><b>BOOM!</b> Comment deleted by administrator.</span>");
				} else {
					// comment body
					if ($comment["body_r"]) {
						$body = $comment["body_r"];
					} else {
						// TODO: update comment with webified body!
						$body = $this->webify($comment["body"]);
						//$body = $comment["body"];
					}
					print($body);
					
					// signature
					if ($comment["signature"] && ($_SESSION[USER_SESSIONVAR]["view_signatures"] != "N")) {
						print("<br><br><div class=\"signature\">".$this->webify($comment["signature"])."</div>\n");
					}
				}

				// admin stuff
				// is user allowed to delete this comment?
				if ($this->userCanDeleteComment($comment["id"])) {
					$adminlinks[] = "[<a class=\"admin\" href=\"".$this->buildUrl(array("action" => "deletecomment", "comment_id" => $comment["id"], "return" => urlencode($_SERVER["REQUEST_URI"]."#".$comment["num"])))."\" onClick=\"return confirm('This will delete this comment. Are you sure?');\">Delete</a>]";
				}
				if ($adminlinks) {
					//print("<br>\n".implode(" ", $adminlinks));
				}


			?>
		</td>

		</td>
	</tr>
</table>
