<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			S E A R C H
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			<?php
				$this->formOpen($this->buildUrl(), "GET");
				$this->formHidden("action", "search");
			?>

			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="right">Search Query:</td>
					<td>&nbsp;&nbsp;</td>
					<td colspan="5"><?= $this->formRenderInput(array("name" => "q", "value" => $q, "width" => "350px")) ?></td>
				</tr>
				<tr>
					<td align="right">Search what:</td>
					<td>&nbsp;&nbsp;</td>
					<td><?= $this->formRenderSelect(array("name" => "l", "value" => $l, "options" => array("t" => "Topics", "c" => "Comments", "www" => "Web"))) ?></td>
					<td>&nbsp;&nbsp;</td>
					<td align="right">Results per page:</td>
					<td>&nbsp;&nbsp;</td>
					<td><?= $this->formRenderSelect(array("name" => "num", "value" => $num, "options" => array("10" => "10", "25" => "25", "50" => "50"))) ?></td>
				</tr>
			</table>
			<br>
			<?= $this->formRenderSubmitbutton(array("label" => "Search", "name" => "")) ?>
<?php $this->formClose(); ?>
		</td>
	</tr>
</table>
