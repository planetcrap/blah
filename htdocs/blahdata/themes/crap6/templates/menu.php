<table width="<?= $GLOBALS['pagewidth'] ?>" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td align="center" width="<?= $GLOBALS['pagewidth'] ?>" class="block" style="padding: 2px">

			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="center">
						<a href="/">Front Page</a>
						(<a href="/atom.xml">ATOM</a>)
						&bull;
						<a href="/submissions/">Submission Bin</a>
						(<?= $this->countSubmittedTopics() ?>)
						&bull;
						<a href="/archives/">Archives</a>
						&bull;
						<a href="/users/">Users</a>
						&bull;
												
						<?php
							if ($_SESSION[USER_SESSIONVAR]) {
								?>
								<a href="/users/<?= $_SESSION[USER_SESSIONVAR]['id'] ?>/">Profile</a>
								&bull;
								<a href="/pipeline/">Pipeline</a>
								&bull;
								<a href="<?= $GLOBALS['PHP_SELF'] ?>?action=logout&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Logout</a>
								<br>
								You are logged in as <b><?= $_SESSION[USER_SESSIONVAR]["name"] ?></b>.
								<?php
							} else {
								?>
								<a href="<?= $GLOBALS['PHP_SELF'] ?>?action=login&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Login</a>
								&bull;
								<a href="<?= $GLOBALS['PHP_SELF'] ?>?action=signup">Create Account</a>
								<br>
								You are currently not logged in.
								<?php					
							}
						?>
						<!-- <br><br> -->
						<?php /* $this->Template("textad_top_lite.php"); */ ?>

					</td>
						
				</tr>
			</table>

		</td>
	</tr>
</table>

<!--
<table width="<?= $GLOBALS['pagewidth'] ?>" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td align="center" width="<?= $GLOBALS['pagewidth'] ?>" class="block" style="padding: 2px">
			<div style="float: right">
			<a href="https://www.paypal.com/xclick/business=hendrik%40mans.de&item_name=PlanetCrap.com+One-Time+Donation&no_note=1&tax=0&currency_code=USD"><img src="/blahdata/themes/crap6/images/paypal-donate.gif" width="62" height="31" border="0" style="margin-bottom: 3px" /></a><br/>
			<a href="https://www.paypal.com/subscriptions/business=hendrik%40mans.de&item_name=PlanetCrap.com+Donation+Subscription&no_note=1&currency_code=USD&a3=3.00&p3=1&t3=M&src=1&sra=1"><img src="/blahdata/themes/crap6/images/paypal-donsub.gif" width="62" height="41" border="0" /></a>
			</div>
			<b>Return of the PayPal buttons from HELL!!!</b>
			Yes, you can now once again support your favorite webmaster's favorite site
			with tiny little donation payments through Paypal. I'm working on
			<a href="/topics/933/">PlanetCrap 7.0</a>, and these
			donations will make me work a little faster. You can either make a
			<a href="https://www.paypal.com/subscriptions/business=hendrik%40mans.de&item_name=PlanetCrap.com+Donation+Subscription&no_note=1&currency_code=USD&a3=3.00&p3=1&t3=M&src=1&sra=1">donation subscription ($3/month)</a> or
			<a href="https://www.paypal.com/xclick/business=hendrik%40mans.de&item_name=PlanetCrap.com+One-Time+Donation&no_note=1&tax=0&currency_code=USD">send me all your money at once</a>. Thanks, I love you all!
		</td>
	</tr>
</table>
-->