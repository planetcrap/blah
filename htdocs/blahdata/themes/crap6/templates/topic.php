<?php

// build links
$topicUrl = $this->buildTopicUrl($topic);
if ($this->topicHasNewComments($topic)) {
	$icon = "topic_new.gif";
	$newComments = $this->countNewTopicComments($topic);
	$iconUrl = $this->buildTopicUrl($topic, $topic["comment_count"] - $newComments + 1);
	$iconAlt = $newComments." unread";
} else {
	$icon = "topic.gif";
	$newComments = 0;
	$iconUrl = $topicUrl;
	$iconAlt = "";
}
?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle" colspan="2">
			T O P I C
		</td>
	</tr>
	<tr>
		<td width="25" valign="top" style="border-bottom: 1px solid #888888; border-left: 1px solid #FFFFFF; background-color: #DDDDDD; padding: 8px 5px">
			<a href="<?= $iconUrl ?>"><img src="<?= IMAGE_URL.$icon ?>" width="25" height="25" alt="<?= $iconAlt ?>" border="0"></a>
		</td>		
		<td valign="top" class="block" style="border-left: none; border-top: none; padding-right: 15px; text-align: justify">


			<?php
				// print topic title
				print("<b style=\"font-size: 18px\">\n");
				print("<a href=\"".$topicUrl."\">".$topic['title']."</a>\n");
				print("</b><br>\n");
				
				// topic information (author etc)
				print("<span style=\"font-size: 11px\">");
				switch ($topic["location"]) {
					case "pipeline":
						print("Created ".date("F jS Y, H:i T", $this->date2unix($topic["when_created"])));
						break;
					case "submission":
						print("Submitted ".date("F jS Y, H:i T", $this->date2unix($topic["when_submitted"])));
						break;
					case "frontpage":
						print(date("F jS Y, H:i T", $this->date2unix($topic["when_published"])));
						break;
					default:
						print("Written");
				}
				if ($topic["author_id"]) {
					$authorLink = $GLOBALS["PHP_SELF"]."?action=profile&user_id=".$topic["author_id"];
				} else {
					$authorLink = "mailto:".$topic["author_email"];
				}
				print(" by <a href=\"".$authorLink."\">".$topic["author_name"]."</a> ");
				print("</span>\n");

				// print topic intro
				if (($mode != "full") || ($topic["show_both"] == "Y") || (!$topic["body"])) {
					if ($topic["intro"]) {
						print("<br><br>\n");
						if ($topic["is_html"] == "Y") {
							print(trim($topic["intro"])."\n");
						} else {
							print($this->webify($topic["intro"])."\n");
						}
					}
				}
								
				// print topic body (if in full mode *or* no intro is set)
				if ((($mode == "full") || (!$topic["intro"])) && ($topic["body"])) {
					print("<br><br>\n");
					if ($topic["is_html"] == "Y") {
						print(trim($topic["body"])."\n");
					} else {
						print($this->webify($topic["body"]));
					}
				}
				
				// display comment counts
				if (($mode != "full") && ($topic["location"] == "frontpage")) {
					print("<br><br>\n&raquo;\n");

					switch ($topic["comments_mode"]) {
						case "flat":
							// total number of comments
							print("<a href=\"".$topicUrl."\">".$topic["comment_count"]." comment".($topic["comment_count"] == 1 ? "" : "s")."</a>\n");
							// new comments?
							if ($newComments) {
								print("(<a href=\"".$iconUrl."\">".$newComments." unread</a>)\n");
							}
							break;
							
						case "semithreaded":
							$url = $this->buildUrl(array("action" => "viewtopic", "topic_id" => $topic["id"]));
							if ($topic["comment_count"]) {
								print("<a href=\"".$topicUrl."\">");
								print($topic["comment_count"]." comment".($topic["comment_count"] == 1 ? "" : "s")." in\n");
								print($topic["thread_count"]." thread".($topic["thread_count"] == 1 ? "" : "s"));
								print("</a>\n");
							} else {
								print("<a href=\"".$topicUrl."\">No comments so far.</a>\n");
							}
					}
				}

				
				// this is the admin stuff.
				
				// is user allowed to edit topic?
				if ($this->userCanEditTopic($topic["id"])) {
					$adminlinks[] = "[<a class=\"admin\" href=\"".$GLOBALS["PHP_SELF"]."?action=edittopic&topic_id=".$topic["id"]."&return=".urlencode($_SERVER["REQUEST_URI"])."\">Edit Topic</a>]";
				}
				
				// is user allowed to submit topic?
				if ($this->userCanSubmitTopic($topic["id"])) {
					$adminlinks[] = "[<a class=\"admin\" href=\"".$GLOBALS["PHP_SELF"]."?action=submittopic&topic_id=".$topic["id"]."\" onclick=\"return confirm('This will put the topic into the submission bin where it can\'t be edited any further. Are you sure?');\">Submit Topic</a>]";
				}

				// is user allowed to publish topic?
				if ($this->userCanPublishTopic($topic["id"])) {
					$adminlinks[] = "[<a class=\"admin\" href=\"".$GLOBALS["PHP_SELF"]."?action=publishtopic&topic_id=".$topic["id"]."\" onClick=\"return confirm('This will move the topic to the front page. Are you sure?');\">Publish Topic</a>]";
				}

				// is user allowed to reject topic?
				if ($this->userCanRejectTopic($topic["id"])) {
					$adminlinks[] = "[<a class=\"admin\" href=\"".$GLOBALS["PHP_SELF"]."?action=rejecttopic&topic_id=".$topic["id"]."\" onClick=\"return confirm('This will put the topic back into the topic pipeline and\\ndelete all of its votes and comments. Are you sure?');\">Back to Pipeline</a>]";
				}

				// is user allowed to delete topic?
				if ($this->userCanDeleteTopic($topic["id"])) {
					$adminlinks[] = "[<a class=\"admin\" href=\"".$GLOBALS["PHP_SELF"]."?action=deletetopic&topic_id=".$topic["id"]."\" onClick=\"return confirm('This will completely delete the topic and all its associated data. Are you sure?');\">Delete</a>]";
				}
					
				// display admin links
				if ($adminlinks) {
					print("<br><br>\n<span style=\"font-size: 11px;\">".implode(" ",$adminlinks)."</span>");
				}
				
			?>
			
		</td>
	</tr>

	<?php
		// this is the voting stuff.
		
		if ($this->userCanVoteOnTopic($topic["id"])) {

			?>
			<tr>
				<td width="650" colspan="2" style="border-bottom: 1px solid #888888; border-right: 1px solid #888888; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; background-color: #CCCCCC; padding: 2px 8px">
					<?php
						if ($this->userHasVotedOnTopic($topic["id"])) {
							print("You have already voted on this topic!");
						} else {
							if ($mode == "full") {
								print("Do you like this topic? ");
								$return = urlencode($_SERVER["REQUEST_URI"]);
								print("[<a href=\"".$GLOBALS["PHP_SELF"]."?action=vote&publish=Y&topic_id=".$topic["id"]."\">YES, publish it on the front page!</a>] or ");
								print("[<a href=\"".$GLOBALS["PHP_SELF"]."?action=vote&publish=N&topic_id=".$topic["id"]."\">NO, it sucks. Remove it!</a>]\n");
							} else {
								print("Like this topic? Don't like it? You can vote on it. <a href=\"".$this->buildTopicUrl($topic)."\">View the full topic to vote</a>.");
							}
						}
					?>					
				</td>
			</tr>
			<?php

		}
	
	?>

</table>

