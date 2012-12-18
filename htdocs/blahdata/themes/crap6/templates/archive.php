<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			A R C H I V E
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			<?php
				if ($mode == "unread") {
					print("Displaying topics with unread comments. [<a href=\"".$this->buildUrl(array("action" => "archive"))."\">View all topics</a>]");
				} else {
					print("Displaying all topics. [<a href=\"".$this->buildUrl(array("action" => "archive", "mode" => "unread"))."\">View topics with unread comments only</a>]");
				}
			?>
		</td>
	</tr>
</table>


<?php
	if ($topics) {
		foreach ($topics as $topic) {
			// prepare data
			$extra = "";
			$topicUrl = $this->buildTopicUrl($topic);
			if ($this->topicHasNewComments($topic)) {
				$icon = "thread_new.gif";
				$newComments = $this->countNewTopicComments($topic);
				$iconUrl = $this->buildTopicUrl($topic, $topic["comment_count"] - $newComments + 1);
				$iconAlt = $newComments." unread";
			} else {
				$icon = "thread.gif";
				$newComments = 0;
				$iconUrl = $topicUrl;
				$iconAlt = "";
			}

			if (($mode != "unread") || ($newComments)) {
				// get month
				$unix = $this->date2unix($topic["when_published"]);
				$month = date("F Y", $unix);
				
				// new month?
				if ($month != $curMonth) {
					// close last month?
					if ($curMonth) {
						print("</table></td></tr></table>\n");
					}
					
					// open new month
					$monthText = strtoupper($month);
					$newText = "";
					for ($i = 0; $i < strlen($monthText); $i++) {
						$newText .= $monthText[$i]."&nbsp;";
					}
					$monthText = trim($newText);
					print("<table width=\"650\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" class=\"block\">\n");
					print("<tr><td class=\"blocktitle\">$monthText</td></tr>\n");
					print("<tr><td width=\"650\" class=\"block\" style=\"border-top: none\">\n");
					print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n");
					
					// remember
					$curMonth = $month;
				}
				
				// print topic
				print("<tr><td valign=\"top\" style=\"padding-top: 2px\"><a href=\"".$iconUrl."\"><img src=\"".IMAGE_URL.$icon."\" border=\"0\" width=\"14\" height=\"14\" alt=\"".$iconAlt."\"></a>&nbsp;</td><td width=\"580\">");
				print("<a href=\"".$topicUrl."\">".$topic["title"]."</a>\n");
				print("(".$topic["comment_count"]." comments".($newComments ? ", $newComments unread" : "").")");
				print("</td></tr>\n");
			}
		}
		// close table
		if ($curMonth) {
			print("</table></td></tr></table>\n");
		}
	}
?>

<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			S T A T I S T I C S
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			We now have <?= $this->countPublishedTopics() ?> topics with a total of
			<?= $this->countAllComments() ?> comments!<br>
		</td>
	</tr>
</table>

