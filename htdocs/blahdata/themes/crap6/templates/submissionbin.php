<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			S U B M I S S I O N &nbsp; B I N
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">

			<p>
			The following is a list of discussion topics submitted by users of this site.
			As a registered user, you can vote for or against a topic to be published on the front page.
			</p>
			
			<p>
			You can submit your own topics, too -- just go to
			<a href="<?= $GLOBALS['PHP_SELF'] ?>?location=pipeline">your topic pipeline</a>.
			</p>
			
		</td>
	</tr>
</table>

<?php
if ($topics) {
	foreach ($topics as $topic) {
		$this->template(TOPIC_TEMPLATE, array('topic' => $topic, 'mode' => 'light'));
	}
} else {
	?>
		<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
			<tr>
				<td width="650" class="block">
		
					Right now, there are no topics in the submission bin. :(
					
				</td>
			</tr>
		</table>
	<?php
}
?>
