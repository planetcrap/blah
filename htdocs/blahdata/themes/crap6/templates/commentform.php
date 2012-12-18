<script language="JavaScript">
function previewButton(form) {
	form.previewmode.value = '1';
	form.submit.click();
}
</script>
<?php
//	$this->template("textads_commentform.php");
?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			P O S T &nbsp; A &nbsp; C O M M E N T
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			<br>
			<?php

				// open form
				$this->formOpen($GLOBALS["PHP_SELF"]."?action=postcomment", "post");
				$this->formHidden("topic_id", $topic_id);
				$this->formHidden("thread_id", $thread_id);
				$this->formHidden("offset", $offset);
				$this->formHidden("limit", $limit);
				$this->formHidden("previewmode", "0");

				// is user logged in?
				if ($_SESSION[USER_SESSIONVAR]) {
					//print("You are logged in as <a href=\"".$GLOBALS["PHP_SELF"]."?action=profile&user_id=".$_SESSION[USER_SESSIONVAR]["id"]."\">".$_SESSION[USER_SESSIONVAR]["name"]."</a>.<br>\n");
					$showForm = 1;
				} else {
					// user isn't logged in. now we should check if this topic allows guest comments.
					if ($this->topicAllowsGuestComments($topic_id)) {
						// author information
						print("Your Name:<br>\n".$this->formRenderInput(array("name" => "comment[author_name]", "value" => $_COOKIE[AUTHOR_NAME_COOKIE]))."<br>\n");
						print("Your Email:<br>\n".$this->formRenderInput(array("name" => "comment[author_email]", "value" => $_COOKIE[AUTHOR_EMAIL_COOKIE]))."<br>\n");
						print("Your Homepage:<br>\n".$this->formRenderInput(array("name" => "comment[author_url]", "value" => $_COOKIE[AUTHOR_URL_COOKIE]))."<br>\n");
						print("<br>\n");
						
						// yes, we want the form
						$showForm = 1;
					} else {
						print("You need to be <a href=\"".$GLOBALS["PHP_SELF"]."?action=login&return=".urlencode($_SERVER["REQUEST_URI"])."\">logged in</a> to post a comment here. If you don't have an account yet, you can create one <a href=\"".$GLOBALS["PHP_SELF"]."?action=signup\">here</a>. Registration is free.");
						print("<br>\n");
					}
				}
				
				// do we want to show the form?
				if ($showForm) {
					// comment/thread title
					if ($thread_id == 0) {
						print("Thread Title:<br>\n".$this->formRenderInput(array("name" => "comment[title]", "value" => $comment["title"]))."<br>\n");
					} else if (ALLOW_COMMENT_TITLES) {
						print("Comment Title:<br>\n".$this->formRenderInput(array("name" => "comment[title]", "value" => $comment["title"]))."<br>\n");
					}
					
					// the comment body
					print("Comment Text:<br>\n".$this->formRenderTextarea(array("name" => "comment[body]", "value" => $comment["body"]))."<br>\n");
					print("<br>\n");
					print($this->formRenderButton(array("label" => "Preview", "onClick" => "previewButton(this.form)"))." ".$this->formRenderSubmitButton(array("label" => "Post")));
					print('<p>Do Morn a favor and check out his new toy: <a href="http://www.25peeps.com">25peeps.com</a>. Cheers!<br/>And yes, PlanetCrap 7.0 is coming. Now with extra poop!</p>');
				}
				
				$this->formClose();	
			?>
		</td>
	</tr>
</table>
<?php
	$this->template("craptags.php");
?>
