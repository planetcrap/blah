<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td width="650" class="block" align="center" style="padding: 4px">

			There are currently <?= $this->countOnlines() ?> people browsing this site.
			[<a href="<?= $this->buildUrl(array("action" => "viewonliners")) ?>">Details</a>]
			
		</td>
	</tr>
</table>
