<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			S I G N U P
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">

			<p><b>Create Account</b></p>
			
			<p>
			Welcome to the PlanetCrap signup page!
			</p>
			
			<p>
			While PlanetCrap allows you to post comments to topics as a guest,
			signing up as a new member will also allow you to submit your own
			topics, vote on topics written by others and set your personal site
			preferences, including a personal signature, ignore and highlight
			lists as well as a custom avatar image. Also, if you're registered,
			the site will alert you if new comments have been posted to a thread.
			</p>
			
			<p>
			Please enter your name, your email address and a new password. Your account will have to be
			activated before you can use it; you will be emailed an activation link, so please make sure
			the email address you enter is correct.
			</p>

			<?php
				if ($error) {
					print("<p class=\"error\">\n$error\n</p>\n\n");
				}

				$this->formOpen($GLOBALS["PHP_SELF"]."?action=signup", "post");
				print("Your user name:<br>\n".$this->formRenderInput(array("name" => "user[name]", "value" => $_COOKIE[AUTHOR_NAME_COOKIE], "width" => "250px"))."<br>\n");
				print("Your email address:<br>\n".$this->formRenderInput(array("name" => "user[email]", "value" => $_COOKIE[AUTHOR_EMAIL_COOKIE], "width" => "250px"))."<br>\n");
				print("Pick a password:<br>\n".$this->formRenderInput(array("name" => "password", "type" => "password", "width" => "250px"))."<br>\n");
				print("Confirm password:<br>\n".$this->formRenderInput(array("name" => "confirmpassword", "type" => "password", "width" => "250px"))."<br>\n");
				print("<br>\n");
				
				print($this->formRenderSubmitbutton(array("label" => "Create Account"))." ".$this->formRenderButton(array("label" => "No thanks", "onClick" => "history.back();")));
				$this->formClose();	
			?>
		</td>
	</tr>
</table>
