<?php

// CONFIGURATION

// first, put the location of the data directory here:
// (and don't forget the trailing slash!)
define(DATA_DIR, "/home/hmans/sites/planetcrap.com/htdocs/blahdata/");

// and now the URL where the data dir can be accessed:
// (and don't forget the trailing slash!)
define(DATA_URL, "/blahdata/");

// now change the values in config.php located within the
// data directory.

// END OF CONFIGURATION






// read the config file
require(DATA_DIR."config.php");

// read the class file
require(DATA_DIR."blah2.class.php");

// rrrrrrandomize!
srand((double)microtime()*1000000);

// start session
session_start();

// fire up gzip compression?
if (USE_GZIP) {
//	ob_start("ob_gzhandler");
}

// you know, just in case. :P
set_magic_quotes_runtime(0);

// create the amazing blah!
$blah = new blah(DATA_DIR, $CONFIG);

// run the amazing blah!
$blah->run();

?>
