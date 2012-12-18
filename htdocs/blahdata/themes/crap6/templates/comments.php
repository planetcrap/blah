<a name="comments"></a>
<?php

if ($comments) {
	
	// fetch nums of first and last comment
	$count = count($comments);
	$firstNum = $comments[0]["num"];
	$lastNum = $comments[$count-1]["num"];

	// thread/topic titles
	$navigation = "<a href=\"/\">Home</a> &raquo;\n";
	$navigation .= "Topic: <a href=\"/topics/".$topic['id']."/\">".$topic["title"]."</a>\n";
	if ($topic["comments_mode"] == "semithreaded") {
		$navigation .= "&raquo; Thread: <b>".$thread["title"]."</b>\n";
	}
	$navigation .= "<br>\n";
	
	// is user allowed to renumber topic?
	if ($this->userIsSuperUser()) {
		//$navigation .= "[<a class=\"admin\" href=\"".$this->buildUrl(array("action" => "renumberthread", "thread_id" => $thread["id"]))."\" onClick=\"return confirm('This will renumber all comments in this thread. Are you sure?');\">Renumber Comments</a>]<br>\n";
	}

	// build previous page link
	if ($offset > 0) {
		if (($newOffset = $offset - $limit) < 0) {
				$newOffset = 0;
			}
		//$previousPageLink = "<a href=\"".$this->buildUrl(array("action" => "viewthread", "thread_id" => $thread["id"], "offset" => $newOffset, "limit" => $limit), "#comments")."\">Previous Page</a>";
		$previousPageLink = '<a href="/topics/'.$topic['id'].'/'.$newOffset.'/#comments">Previous Page</a>';
	} else {
		$previousPageLink = "Previous Page";
	}

	// build next page link
	if ($thread["comment_count"] > $lastNum) {
		$newOffset = $offset + $limit;
		//$nextPageLink = "<a href=\"".$this->buildUrl(array("action" => "viewthread", "thread_id" => $thread["id"], "offset" => $newOffset, "limit" => $limit), "#comments")."\">Next Page</a>";
		$nextPageLink = '<a href="/topics/'.$topic['id'].'/'.$newOffset.'/#comments">Next Page</a>';
	} else {
		$nextPageLink = "Next Page";
	}
	
	// start of thread link
	$startOfThreadLink = "|&laquo;&laquo;";
	if ($offset) {
		$startOfThreadLink = "<a title=\"Jump to start of thread\" href=\"/topics/".$topic['id']."/#comments\">".$startOfThreadLink."</a>";
	}
		
	// end of thread link
	$endOfThreadLink = "&raquo;&raquo;|";
	if ((count($comments) + $offset) < $thread["comment_count"]) {
		$newOffset = $this->calcPageOffset($thread["comment_count"], $limit);
		$endOfThreadLink = "<a title=\"Jump to end of thread\" href=\"/topics/".$topic['id']."/".$newOffset."/#comments\">".$endOfThreadLink."</a>";
	}

	// concatenate the two links
	if ($limit > 0) {
		$maxPage = ceil($thread["comment_count"] / $limit);
		$thisPage = floor($offset / $limit) + 1;
		$pageLinks = "Displaying page ".$thisPage." of ".$maxPage."<br>\n"; // ." with ".$limit." comment".($limit == 1 ? "" : "s")." each<br>\n";
	}
	
	// finalize navigation
	$navigation .= "<br><b>".$startOfThreadLink." - ".$previousPageLink." - ".$nextPageLink." - ".$endOfThreadLink."</b>";

	// display navigation bar
	?>
	<table width="650" align="center" cellspacing="0" cellpadding="0" border="0" class="block">
		<tr>
			<td class="blocktitle">
				C O M M E N T S
			</td>
		</tr>
		<tr>
			<td class="block" align="center" style="border-top: none">
				<?= $navigation ?>
			</td>
		</tr>
	</table>
	<?php



	// now display the comments
	foreach ($comments as $comment) {
		$this->template(COMMENT_TEMPLATE, compact("comment", "lastread"));
	}

	// display navigation bar
	?>
	<a name="comments"></a>
	<table width="650" align="center" cellspacing="0" cellpadding="0" border="0" class="block">
		<tr>
			<td class="blocktitle">
				C O M M E N T S
			</td>
		</tr>
		<tr>
			<td class="block" align="center" style="border-top: none">
				<?= $navigation ?>
			</td>
		</tr>
	</table>
	<?php
}
?>
