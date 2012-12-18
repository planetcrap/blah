<?php
if ($topics) {
	$i = 0;
	foreach ($topics as $topic) {
		$this->template(TOPIC_TEMPLATE, array('topic' => $topic, 'mode' => 'light'));
		if ($i++ == 0) {
			$this->template("adsense_homepage.php");
		}
	}
}
?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td width="650" class="block" style="padding: 4px" align="center">

			Check out more topics in the
			<a href="/archives/">PlanetCrap Archives</a>!
			
		</td>
	</tr>
</table>
