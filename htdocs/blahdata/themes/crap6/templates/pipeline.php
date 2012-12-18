<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			T O P I C &nbsp; P I P E L I N E
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			
			<p>
			PlanetCrap allows you to submit your own topics. When you enter a new topic, it will show up
			on this page, where you can preview and modify it as long as you want. When you're 100% happy
			with your topic, you can click on the "Submit" link to put it into the
			<a href="<?= $GLOBALS['PHP_SELF'] ?>?location=submission">submission bin</a>, where other registered Crappers will vote for or against publishing it
			on the <a href="<?= $GLOBALS['PHP_SELF'] ?>">front page</a>.
			</p>
			
			<?php
				print("<a href=\"".$GLOBALS["PHP_SELF"]."?action=edittopic&return=".urlencode($_SERVER["REQUEST_URI"])."\">Click here to create a new topic</a>!\n");
			?>
			
		</td>
	</tr>
</table>

<?php
// display pipeline topics
if ($topics) {
	foreach ($topics as $topic) {
		$this->template(TOPIC_TEMPLATE, array('topic' => $topic, 'mode' => 'light'));
	}
}
?>
