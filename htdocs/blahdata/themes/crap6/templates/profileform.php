<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			E D I T &nbsp; P R O F I L E
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">
			
			<?php
				$this->formOpen($this->buildUrl(array("action" => "storeprofile", "user_id" => $user["id"])), "POST", "50000");
			?>

			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="top">
						<b>User Name:<b><br>
						<?= $this->formRenderInput(array("name" => "user[name]", "value" => $user["name"], "enabled" => 0, width => "250px")) ?>
					</td>
					<td valign="top">
						&nbsp;
					</td>
					<td valign="top">
						<b>Email Address:<b><br>
						<?= $this->formRenderInput(array("name" => "user[email]", "value" => $user["email"], "enabled" => 0, width => "250px")) ?>
					</td>
				</tr>
			</table>
			You can't change your name or email address here. If you want them changed for some reason, please email
			<a href="mailto:admins@planetcrap.com">admins@planetcrap.com</a>.<br>
			<br>
			
			<table border="0" cellspacing="0" cellpadding="0">
				<tr><td colspan="2"></td><td><b>Personal Information:</b></td></tr>
				<tr>
					<td align="right" width="200">Your Real Name:</td>
					<td>&nbsp;</td>
					<td><?= $this->formRenderInput(array("name" => "user[extra_realname]", "value" => $user["extra_realname"], "width" => "400px")) ?></td>
				</tr>
				<tr>
					<td align="right">Personal Homepage:</td>
					<td>&nbsp;</td>
					<td><?= $this->formRenderInput(array("name" => "user[url]", "value" => $user["url"], "width" => "400px")) ?></td>
				</tr>
				<tr>
					<td align="right">Company:</td>
					<td>&nbsp;</td>
					<td><?= $this->formRenderInput(array("name" => "user[extra_company]", "value" => $user["extra_company"], "width" => "400px")) ?></td>
				</tr>
				<tr>
					<td align="right">Company Homepage:</td>
					<td>&nbsp;</td>
					<td><?= $this->formRenderInput(array("name" => "user[extra_company_url]", "value" => $user["extra_company_url"], "width" => "400px")) ?></td>
				</tr>


				<tr><td colspan="2"></td><td><br><b>Preferences:</b></td></tr>
				<tr>
					<td></td>
					<td></td>
					<td>
						<table border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td><?= $this->formRenderCheckbox(array("name" => "user[show_online]", "value" => $user["show_online"])) ?></td>
								<td>&nbsp;Show your online status</td>
							</tr>
							<tr>
								<td><?= $this->formRenderCheckbox(array("name" => "user[show_email]", "value" => $user["show_email"])) ?></td>
								<td>&nbsp;Display email address in comments/profile</td>
							</tr>
							<tr>
								<td><?= $this->formRenderCheckbox(array("name" => "user[view_signatures]", "value" => $user["view_signatures"])) ?></td>
								<td>&nbsp;View other users' signatures</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top" style="padding-top: 3px">Comment Signature:</td>
					<td>&nbsp;</td>
					<td><?= $this->formRenderTextarea(array("name" => "user[signature]", "value" => $user["signature"], "width" => "400px", "rows" => "3")) ?></td>
				</tr>
				<tr>
					<td align="right" valign="top" style="padding-top: 3px">Comments per Page:</td>
					<td>&nbsp;</td>
					<td><?= $this->formRenderInput(array("name" => "user[pagelength]", "value" => $user["pagelength"], "width" => "60px")) ?></td>
				</tr>
				<tr>
					<td align="right" valign="top" style="padding-top: 3px">Page Width:</td>
					<td>&nbsp;</td>
					<td><?= $this->formRenderInput(array("name" => "user[extra_pagewidth]", "value" => $user["extra_pagewidth"], "width" => "60px")) ?></td>
				</tr>

				<tr><td colspan="2"></td><td><br><b>Avatar Icon:</b></td></tr>
				<tr>
					<td align="right" valign="top" style="padding-top: 3px">Upload New Avatar Icon:</td>
					<td>&nbsp;</td>
					<td>
						<?= $this->formRenderInput(array("name" => "avatar", "type" => "file")) ?><br>
						Your avatar icon will be displayed next to each of your comments. It will be resized 
						automatically, but for best results it is recommended that you upload an image with square dimensions,
						at least 100 pixels high and wide. (JPG, GIF, PNG or BMP.)
					</td>
				</tr>

			</table>			
			<br>
			<?php	
				// buttons
				print($this->formRenderSubmitButton(array("label" => "Store")));
				$this->formClose();
			?>
		</td>
	</tr>
</table>
