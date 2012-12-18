<?php

	// prepare avatar
	if ($user["avatar_filename"]) {
		$avatar = "<img src=\"".$this->config["custom_avatars_url"]."large-".$user["avatar_filename"]."\" width=\"100\" height=\"100\" border=\"0\" style=\"border: 1px solid #000000\">";
	}

?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td class="blocktitle">
			U S E R &nbsp; P R O F I L E
		</td>
	</tr>
	<tr>
		<td width="650" class="block" style="border-top: none">

			<table border="0" cellspacing="0" cellpadding="0">
				
				<?php
					if ($avatar) {
						?>
							<tr>
								<td valign="top" class="profiletitle"></td>
								<td>&nbsp;</td>
								<td valign="top" class="profiledata"><?= $avatar ?></b></td>
							</tr>
						<?php
					}
				?>
				
				<tr>
					<td valign="top" class="profiletitle">User Name:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><b><?= $user["name"] ?></b></td>
				</tr>

				<tr>
					<td valign="top" class="profiletitle">Real Name:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><?= $user["extra_realname"] ? $user["extra_realname"] : "N/A" ?></td>
				</tr>

				<tr>
					<td valign="top" class="profiletitle">User ID:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><?= $user["id"] ?></td>
				</tr>

				<tr>
					<td valign="top" class="profiletitle">Member Since:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><?= $user["when_created"] ?></td>
				</tr>

				<tr>
					<td valign="top" class="profiletitle">Last Login:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><?= $user["when_last_login"] ? $user["when_last_login"] : "Never" ?></td>
				</tr>

				<tr><td colspan="3">&nbsp;</td></tr>

				<tr>
					<td valign="top" class="profiletitle">Email Address:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><?= $user["show_email"] == "Y" ? $this->linkify($user["email"], 0, "mailto:") : "Hidden" ?></td>
				</tr>

				<tr>
					<td valign="top" class="profiletitle">Homepage:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><?= $user["url"] ? $this->linkify($user["url"]) : "N/A" ?></td>
				</tr>

				<tr><td colspan="3">&nbsp;</td></tr>

				<tr>
					<td valign="top" class="profiletitle">Company:</td>
					<td>&nbsp;</td>
					<td valign="top" class="profiledata"><?= $user["extra_company"] ? ($user["extra_company_url"] ? "<a href=\"".$this->addHttp($user["extra_company_url"])."\" target=\"_blank\">".$user["extra_company"]."</a>" : $user["extra_company"]) : "N/A" ?></td>
				</tr>


			</table>
			
			<?php
				if ($this->userCanEditProfile($user["id"])) {
					print("<br>\n");
					print($this->formRenderButton(array("label" => "Edit Profile", "url" => $this->buildUrl(array("action" => "editprofile", "user_id" => $user["id"])))));
				}
			?>			
			
		</td>
	</tr>
</table>
