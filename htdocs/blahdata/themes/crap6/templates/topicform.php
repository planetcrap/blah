<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			T O P I C &nbsp; E D I T O R
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			<?php

				// display form
				$this->formOpen($GLOBALS["PHP_SELF"]."?action=storetopic", "post");
				$this->formHidden("return", urlencode($return));
				$this->formHidden("topic_id", $topic_id);
				
				print("<b>Topic Title:</b><br>\n".$this->formRenderInput(array("name" => "topic[title]", value => $topic["title"]))."<br><br>\n");
				print("<b>Intro:</b><br>\n");
				print("The Intro is displayed on the front page. It should include a short summary of your topic.<br>\n");
				print($this->formRenderTextarea(array("name" => "topic[intro]", value => $topic["intro"], rows => 6))."<br><br>\n");
				print("<b>Topic Body:</b><br>\n");
				print("This is the main text of your topic. It is only displayed on the full topic page (which also contains the comments posted by other users).<br>\n");
				print($this->formRenderTextarea(array("name" => "topic[body]", value => $topic["body"]))."<br><br>\n");

				print($this->formRenderCheckbox(array("name" => "topic[show_both]", value => $topic["show_both"]))." Show Intro <i>and</i> Body on full topic page<br><br>\n");
				
				// superuser can choose comments format
				if ($this->userIsSuperuser()) {
					print("<b>Thread format:</b><br>\n");
					print($this->formRenderSelect(array("name" => "topic[comments_mode]", value => $topic["comments_mode"], options => array("flat" => "Flat Mode", "semithreaded" => "Semi-Threaded")))."<br>\n");
				}
				print("<br>\n");
				print($this->formRenderSubmitbutton(array("label" => "Store")));
				$this->formClose();	

			?>
		</td>
	</tr>
</table>

<?php
	$this->template("craptags.php");
?>
