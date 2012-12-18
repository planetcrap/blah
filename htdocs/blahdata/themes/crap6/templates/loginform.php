<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			M E M B E R &nbsp; L O G I N
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			<p>
			Please enter your user name or email address and your password. If you don't have an
			account yet, you can sign up <a href="<?= $GLOBALS['PHP_SELF'] ?>?action=signup">right here</a>.
			</p>

			<?php
				if ($error) {
					print("<p class=\"error\">\n$error\n</p>\n\n");
				}

				$this->formOpen($GLOBALS["PHP_SELF"]."?action=login", "post");
				$this->formHidden("return", urlencode($return));
				print("Your User Name or Email Address:<br>\n".$this->formRenderInput(array("name" => "email", "value" => $_COOKIE[AUTHOR_EMAIL_COOKIE], "width" => "250px"))."<br>\n");
				print("Your Password:<br>\n".$this->formRenderInput(array("name" => "password", "type" => "password", "width" => "250px"))."<br>\n");
				print($this->formRenderCheckbox(array("name" => "remember"))." Remember me<br>\n");
				
				print("<br>\n");
				print($this->formRenderSubmitbutton(array("label" => "Login")));
				$this->formClose();	
			?>
		</td>
	</tr>
</table>
