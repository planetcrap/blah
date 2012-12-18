<?php
	$this->template("menu.php");
//	$this->template("dealtime.php");
	$this->template("onlinerstats.php");
//	$this->template("spambait.php");
?>

<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="block">
	<tr>
		<td width="650" class="copyright">

			Powered by blah <?= BLAH_VERSION ?> &bull;
			PlanetCrap is &copy; 1997-2035 <a href="http://www.mans.de">Hendrik "Morn" Mans</a><br><br>
			<a href="/atom.xml"><img src="/atompixel.png" width="80" height="15" border="0" alt="" title="Syndication feed" /></a>
			
		</td>
	</tr>
</table>


<?php
	if (($this->userIsSuperuser()) && (DEBUG == 1)) {
		$this->template("querydebug.php");
	}
?>



<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<script type="text/javascript">
  var _gauges = _gauges || [];
  (function() {
    var t   = document.createElement('script');
    t.type  = 'text/javascript';
    t.async = true;
    t.id    = 'gauges-tracker';
    t.setAttribute('data-site-id', '4f05cbcd844d520959000009');
    t.src = '//secure.gaug.es/track.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(t, s);
  })();
</script>

</div>
</body>
</html>
