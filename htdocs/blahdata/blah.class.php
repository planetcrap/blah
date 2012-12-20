<?php

// don't change these.
define(BLAH_VERSION, "0.9.1-dev");


// The amazing blah class.

class blah {


	// constructor
	function blah($dataDir, &$CONFIG) {
		// remember data dir
		$this->dataDir = $dataDir;
		
		// copy config
		$this->config = $CONFIG;

		// set up session var
		$this->session = &$_SESSION["BLAH"];
		
	}

	// this is the "main" function. It looks at the $action variable
	// and calls the corresponding method.
	function run() {
		
		// connect to database
		$this->dbConnect();

		// remove stale data
		$this->purgeStaleUsers();
		$this->purgeOnlines();

		// check if user has auto-login enabled
		if (!$_SESSION[USER_SESSIONVAR] && $_COOKIE[LOGIN_EMAIL_COOKIE]) {
			// try to login user; if it fails, delete cookies
			if (!$this->loginUser($_COOKIE[LOGIN_EMAIL_COOKIE], $_COOKIE[LOGIN_PASSWORD_COOKIE], 1)) {
				$this->deleteCookie(LOGIN_EMAIL_COOKIE);
				$this->deleteCookie(LOGIN_PASSWORD_COOKIE);
			}
		}
		
		// prepare some variables we'll need later.
		if ($this->userIsLoggedIn()) {
			$this->session["global_lastread"] = $_SESSION[USER_SESSIONVAR]["global_lastread"];
			$this->pageLength = $_SESSION[USER_SESSIONVAR]["pagelength"];
		} else {
			$this->session["global_lastread"] = $this->unix2datetime(time() - 60 * 60); // for testing purposes
			$this->pageLength = PAGE_LENGTH;
		}

		
		// fetch action
		if (!$action = trim($_REQUEST["action"])) {
			$action = "home";
		}

		// call action handler
		$functionName = "on".ucfirst($action);		
		if (method_exists($this, $functionName)) {
			call_user_method($functionName, $this);
		} else {
			$this->onUnknown();
		}
	}

	// CONFIGURATION FUNCTIONS
	
	// this is probably the stupidest thing I have ever written.
	function define($defineName, $defineValue) {
		if (!defined($defineName)) {
			define($defineName, $defineValue);
		}
	}

	
	// DATABASE FUNCTIONS

	function dbConnect() {
		// try to establish mysql connection
		if (!$this->dbLink = @mysql_connect(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD)) {
			die("Couldn't connect to database server. :(");
		}
		
		// try to select our database
		if (!@mysql_select_db(MYSQL_DATABASE, $this->dbLink)) {
			die("Couldn't open database. :(");
		}
	}
	
	function dbQuery($query) {
		
		// run and benchmark query
		$startTime = $this->getMicroTime();
		$result = mysql_query($query, $this->dbLink);
		$duration = $this->getMicroTime() - $startTime;
	
		// return data 			
		if ($result) {	
			if (DEBUG == 1) {
				$GLOBALS[BLAH_GLOBALVAR]["queries"][] = array("time" => $duration, "query" => "$query");
			}
			return $result;
		} else {
			die("SQL error: ".mysql_error());
		}
	}
	
	function dbBuildSet($data) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$strings[] = "$k = '".addSlashes($v)."'";
			}
			$set = implode(", ", $strings);
			return $set;
		}
	}
	
	function dbFetch($result) {
		return mysql_fetch_assoc($result);
	}
	
	function dbFree($result) {
		mysql_free_result($result);
	}
	
	function dbLoad($query) {
		if ($result = $this->dbQuery($query)) {
			while ($row = $this->dbFetch($result)) {
				$data[] = $row;
			}
			$this->dbFree($result);
			return $data;
		}
	}
	
	function dbLoadSingle($query) {
		if ($data = $this->dbLoad($query)) {
			return $data[0];
		}
	}
	
	function dbInsert($table, $data) {
		$query = "insert into ".addSlashes($table)." set ".$this->dbBuildSet($data);
		if ($this->dbQuery($query)) {
			return mysql_insert_id($this->dbLink);
		}
	}
	
	function dbUpdate($table, $data, $condition) {
		$query = "update ".addSlashes($table)." set ".$this->dbBuildSet($data)." ".$condition;
		$this->dbQuery($query);
	}
	
	
	// FORMS FUNCTIONS
	
	function formOpen($action = "", $method = "POST", $maxFileSize = 0) {
		print("<form enctype=\"multipart/form-data\" action=\"$action\" method=\"$method\" style=\"margin: 0px\">\n");
		if ($maxFileSize) {
			print("<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$maxFileSize\">\n");
		}
	}

	function formClose() {
		print("</form>");
	}
	
	function formHidden($name, $value = "") {
		print("<input type=\"hidden\" name=\"$name\" value=\"".htmlentities($value)."\">\n");
	}
	
	function formRenderInput($params) {
		extract($params);
		if (!$size) { $size = FORM_INPUT_DEFAULT_SIZE; }
		if (!$width) { $width = FORM_INPUT_DEFAULT_WIDTH; }
		if (!$type) { $type = "text"; }
		if (!isset($enabled)) { $enabled = 1; }
		return "<input type=\"$type\" class=\"text\" name=\"$name\" value=\"".htmlentities($value)."\" size=\"$size\" ".($width ? "style=\"width: $width\"" : "")." ".($enabled ? "" : "disabled").">";
	}

	function formRenderTextarea($params) {
		extract($params);
		if (!$cols) { $cols = FORM_TEXTAREA_DEFAULT_COLS; }
		if (!$rows) { $rows = FORM_TEXTAREA_DEFAULT_ROWS; }
		if (!$width) { $width = FORM_TEXTAREA_DEFAULT_WIDTH; }
		return "<textarea class=\"text\" name=\"$name\" cols=\"$cols\" rows=\"$rows\" wrap=\"virtual\" ".($width ? "style=\"width: $width\"" : "").">".htmlentities($value)."</textarea>";
	}
	
	function formRenderButton($params) {
		extract($params);
		if (!$width) { $width = FORM_BUTTON_DEFAULT_WIDTH; }
		
		// if $url is specified, build our own $onClick
		if ($url) {
			$onClick = "document.location = '".$url."';";
		}
		
		return "<input type=\"button\" class=\"button\" value=\"".htmlentities($label)."\" ".($width ? "style=\"width: $width\"" : "")." ".($onClick ? "onClick=\"$onClick\"" : "").">";
	}

	function formRenderSubmitButton($params) {
		extract($params);
		if (!$width) { $width = FORM_BUTTON_DEFAULT_WIDTH; }
		if (!$class) { $class = "button"; }
		if (!isset($name)) { $name = "submit"; }
		return "<input type=\"submit\" class=\"$class\" name=\"$name\" value=\"".htmlentities($label)."\" ".($width ? "style=\"width: $width\"" : "")." ".($height ? "style=\"height: $height\"" : "").">";
	}
	
	function formRenderCheckbox($params) {
		extract($params);
		if (!$onValue) { $onValue = "Y"; }
		if (!$offValue) { $offValue = "N"; }
		return "<input type=\"hidden\" name=\"$name\" value=\"$offValue\"><input type=\"checkbox\" class=\"checkbox\" name=\"$name\" value=\"$onValue\" ".($value == $onValue ? "checked" : "").">";
	}
	
	function formRenderSelect($params) {
		extract($params);
		$output = "<select name=\"$name\" class=\"select\">\n";
		if (is_array($options)) {
			foreach($options as $v => $text) {
				$output .= "<option value=\"".htmlentities($v)."\" ".($value == $v ? "selected" : "").">$text\n";
			}
		}
		$output .= "</select>";
		return $output;
	}
	
	// ARRAY FUNCTIONS
	
	// trims all values of an array
	function arrayTrim($array) {
		if (is_array($array)) {
			foreach ($array as $k => $v) {
				$newArray[$k] = trim($v);
			}
			return $newArray;
		}
	}

	// eradicates HTML tags from all values of an array. no mercy!
	function arrayStripTags($array) {
		if (is_array($array)) {
			foreach ($array as $k => $v) {
				$newArray[$k] = strip_tags($v);
			}
			return $newArray;
		}
	}

	// strips all items from an array that have keys not included in $keys
	function arrayForceKeys($array, $keys) {	
		if (is_array($array)) {
			foreach ($array as $k => $v) {
				if (in_array($k, $keys)) {
					$newArray[$k] = trim($v);
				}
			}
			return $newArray;
		}
	}


	// TIME/DATE FUNCTIONS
	
	function unix2datetime($unix = 0) {
		if (!$unix) $unix = time();
		return date("Y-m-d H:i:s", $unix);
	}
	
	function unix2date($unix = 0) {
		if (!$unix) $unix = time();
		return date("Y-m-d", $unix);
	}
	
	function unix2time($unix = 0) {
		if (!$unix) $unix = time();
		return date("H:i:s", $unix);
	}
	
	function date2unix($date) {
		return strtotime($date);
	}
	
	function getmicrotime() { 
	    list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
    }	
	
	
	// COOKIE FUNCTIONS
	
	function setCookie($name, $value) {
		setCookie($name, $value, time() + COOKIE_LIFE, COOKIE_URL);
	}

	function setSessionCookie($name, $value) {
		setCookie($name, $value, 0, COOKIE_URL);
	}
	
	function deleteCookie($name) {
		setCookie($name, "");
	}
		
	
	// OUTPUT FUNCTIONS
	
	function template($template, $vars = "") {
		// check if template exists
		$filename = DATA_DIR."themes/".THEME."/templates/".$template;
		if (file_exists($filename)) {
			// extract $vars
			if (is_array($vars)) {
				extract($vars);
			}
			
			// include template
			include($filename);
		}
	}
	
	function pageOpen($title = "", $onlineTitle = "") {
		// remember online state
		if (!$onlineTitle) {
			if (!$onlineTitle = $title) {
				$onlineTitle = "Untitled Page";
			}
		}
		$this->setOnline($_SERVER["REQUEST_URI"], $onlineTitle);
		
		// print page
		if ($title) {
			$title = $title." &middot; ".SITE_TITLE;
		} else {
			$title = "PlanetCrap 6.0 &middot; \"THIS IS WHAT DEMOCRACY LOOKS LIKE!\"";
		}
		$this->template(HEADER_TEMPLATE, compact("title"));
	}
	
	function pageClose() {
		$this->template(FOOTER_TEMPLATE);
	}
	
	function simplePage($title, $text) {
		$this->pageOpen($title);
		$this->template(SIMPLEPAGE_TEMPLATE, compact("title", "text"));
		$this->pageClose();
	}
	
	function abort($title = "Error", $text = "Some stupid error occured.") {
		$this->simplePage($title, $text);
		exit;
	}


	// URL BUILDING FUNCTIONS
	
	function buildUrl($fields = "", $extra = "") {
		// base url
		$url = $_SERVER["PHP_SELF"]."?";  // I'm always adding the "?" so the "main" page doesn't get cached by the browser.
		
		// add GET parameters
		if (is_array($fields)) {
			foreach ($fields as $k => $v) {
				$add[] = $k."=".urlencode($v);
			}
			$url .= implode("&", $add);
		}
		
		// add extra
		$url .= $extra;
		
		// done!
		return $url;
	}
	
	// returns the url to a topic. Takes topic data as a parameter.
	// if $commentNum is specified, the link will point to the specified
	// comment.
	function buildTopicUrl(&$topic, $commentNum = 0) {
		
		switch ($topic["comments_mode"]) {
			
			case "flat":
				if ($commentNum) {
					// calc offset
					if ($this->pageLength) {
						$offset = floor(($commentNum - 1) / $this->pageLength) * $this->pageLength;
					} else {
						$offset = $commentNum - 1;
					}
					//return $this->buildUrl(array("action" => "viewtopic", "topic_id" => $topic["id"], "limit" => $this->pageLength, "offset" => $offset), "#".$commentNum);
					return "/topics/".$topic['id']."/".$offset."-".$this->pageLength."/#".$commentNum;
				} else {
					//return $this->buildUrl(array("action" => "viewtopic", "topic_id" => $topic["id"], "limit" => $this->pageLength));
					return "/topics/".$topic['id']."/";
				}
				
			case "semithreaded":
			default:
				// semithreaded can only link to the full topic, no matter what.
				return $this->buildUrl(array("action" => "viewtopic", "topic_id" => $topic["id"]));
		}
	}	

	// returns the url to a thread. Takes thread data as a parameter.
	// if $commentNum is specified, the link will point to the specified
	// comment.
	function buildThreadUrl(&$thread, $commentNum = 0) {
		if ($commentNum) {
			// calc offset
			if ($this->pageLength) {
				$offset = floor(($commentNum - 1) / $this->pageLength) * $this->pageLength;
			} else {
				$offset = $commentNum - 1;
			}
			return $this->buildUrl(array("action" => "viewthread", "thread_id" => $thread["id"], "limit" => $this->pageLength, "offset" => $offset), "#".$commentNum);
		} else {
			return $this->buildUrl(array("action" => "viewthread", "thread_id" => $thread["id"], "limit" => $this->pageLength));
		}
	}	

	function calcPageOffset($commentNum, $pageLength = -1) {
		// set $pageLength to $this->pageLength if not specified
		if ($pageLength < 0) {
			$pageLength = $this->pageLength;
		}
		
		// calculate new offset
		if ($pageLength < 1) {
			$offset = $commentNum - 1;
		} else {
			$offset = floor(($commentNum - 1) / $pageLength) * $pageLength;
		}
		
		return $offset;
	}
	
	// STRING MANIPULATION FUNCTIONS
	
	// checks a URL and adds $prefix if no protocol prefix is present.
	function addHttp($url, $prefix = "http://") {
		// meh
		$url = htmlentities($url);
		
		if (!preg_match("/^.+[:]/", $url)) {
			$url = $prefix.$url;
		}
		
		return $url;
	}
	
	// accepts a potentially incomplete url as a parameter and returns a 
	// full link. if $newWindow is set to 1, the link will have 
	// target="_blank". $prefix is the default prefix passed to
	// addHttp(). $extra is a string inserted into the link. It
	// can contain style/class definitions and other fun stuff.
	function linkify($url, $newWindow = 1, $prefix = "http://", $extra = "") {
		$link = "<a href=\"".$this->addHttp($url, $prefix)."\" ".($newWindow ? "target=\"_blank\"" : "")." $extra>".$url."</a>";
		return $link;
	}
	
	// prepares a text for printing on the page. kills HTML,
	// converts [UBB] style macros, and so on.
	function webify($text, $crapTags = 1) {
		// un-evil all HTML tags
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);

		// remove evil \r
		$text = str_replace("\r", "", $text);
		
		// trim text!
		$text = trim($text);

		// replace double spaces
		$text = preg_replace("/  /", "&nbsp;&nbsp;", $text);

		// replace leading spaces
		$text = preg_replace("/^ /m", "&nbsp;", $text);
		
		// replace them tags, yo!
		if ($crapTags) {
			$replacers = array(
				"/\[tt\]/i"						=> "<span style=\"font-family: Courier New\">",
				"/\[\/tt\]/i"					=> "</span>",
				"/\[fixed\]/i"					=> "<span style=\"font-family: Courier New\">",
				"/\[\/fixed\]/i"				=> "</span>",
				"/\[pre\]/i"					=> "<pre>",
				"/\[\/pre\]/i"					=> "</pre>",
				"/\[i\]/i"						=> "<i>",
				"/\[\/i\]/i"					=> "</i>",
				"/\[u\]/i"						=> "<u>",
				"/\[\/u\]/i"					=> "</u>",
				"/\[s\]/i"						=> "<s>",
				"/\[\/s\]/i"					=> "</s>",
				"/\[b\]/i"						=> "<b>",
				"/\[\/b\]/i"					=> "</b>",
				"/\[quote\]/i"						=> "<div class=\"quote\">",
				"/\[\/quote\]/i"					=> "</div>",
				"/\[url=(.*?)\](.*?)\[\/url\]/ies"	=> "'<a href=\"'.\$this->addHttp('\\1').'\" target=\"_blank\">\\2</a>'",
				"/\[url\](.*?)\[\/url\]/ie"		=> "'<a href=\"'.\$this->addHttp('\\1').'\" target=\"_blank\">\\1</a>'",
				"/\[email=(.*?)\](.*?)\[\/email\]/ies"	=> "'<a href=\"'.\$this->addHttp('\\1', 'mailto:').'\">\\2</a>'",
				"/\[email\](.*?)\[\/email\]/ie"		=> "'<a href=\"'.\$this->addHttp('\\1', 'mailto:').'\">\\1</a>'",
				"/\[openblah=(.*?)\](.*?)\[\/openblah\]/ies"	=> "'<a href=\"'.\$this->addHttp('\\1', 'openblah:').'\">\\2</a>'",
				"/\[openblah\](.*?)\[\/openblah\]/ie"		=> "'<a href=\"'.\$this->addHttp('\\1', 'openblah:').'\">\\1</a>'",
				"/\[\[\]/"						=> "[",
				"/\[\]\]/"						=> "]");

				
			foreach ($replacers as $from => $to) {
				$text = preg_replace($from, $to, $text);
			}
		}
				
		
		
		// replace newlines with <br>
		$text = nl2br($text);
		
		// done!
		return $text;
	}
	
	// TOPIC FUNCTIONS
	
	// loads a single topic from the database
	function loadTopic($id, $includeExtraData = 1) {
		// in cache?
		if ($topic = $GLOBALS[BLAH_GLOBALVAR]["topicCache"][$id]) {
			return $topic;
		} else {
			if ($topics = $this->loadTopics("where ".TOPICS_TABLE.".id = '".addSlashes($id)."' limit 1", $includeExtraData)) {
				return $topics[0];
			}
		}
	}

	// loads a set of topics
	function loadTopics($extraSql = "", $includeExtraData = 1, $extraFields = "") {
		// also add through some clever left joins:
		//   lastread     - datetime of last visit to first (and only?) thread
		$query = "select ".TOPICS_TABLE.".*, ".LASTREAD_TABLE.".lastread".($extraFields ? ", $extraFields" : "")." from ".TOPICS_TABLE
			.($includeExtraData ? " left join ".LASTREAD_TABLE." on ".TOPICS_TABLE.".first_thread_id = ".LASTREAD_TABLE.".thread_id and ".LASTREAD_TABLE.".user_id = '".$_SESSION[USER_SESSIONVAR]["id"]."' " : "")
			." ".$extraSql;
		
		if ($topics = $this->dbLoad($query)) {
			for ($i = 0; $i < count($topics); $i++) {
				// if no lastread is set, set to global lastread
				if (!$topics[$i]["lastread"]) {
					$topics[$i]["lastread"] = $this->session["global_lastread"];
				}
				
				// put into cache
				$GLOBALS[BLAH_GLOBALVAR]["topicCache"][$topics[$i]["id"]] = $topics[$i];
			}
			
			// return topic data
			return $topics;
		}
	}

	// returns true if a guest is allowed to post a comment to topic with $topic_id
	function topicAllowsGuestComments($topic_id) {
		// due to excessive spam bot spammage botting
		return false;
	
	
		// load topic
		if ($topic = $this->loadTopic($topic_id)) {
			// does it allow? is the global switch set, too?
			if (($topic["allow_guest_comments"] == "Y") && (ALLOW_GUEST_COMMENTS)) {
				return true;
			}
		}
	}

	// inserts topic into database and returns its new id
	function insertTopic($topic) {
		return $this->dbInsert(TOPICS_TABLE, $topic);
	}
	
	// updates topic
	function updateTopic($topic_id, $topic) {
		$this->dbUpdate(TOPICS_TABLE, $topic, "where id = '".addSlashes($topic_id)."' limit 1");
	}

	// deletes all votes for a topic
	function deleteVotes($topic_id) {
		$this->dbQuery("delete from ".VOTES_TABLE."  where topic_id = '".addSlashes($topic_id)."'");
	}		
	
	// submits topic (moves topic from "pipeline" to "submission")
	function submitTopic($topic_id) {
		// prepare update
		$update["location"] = "submission";
		$update["when_submitted"] = $this->unix2datetime();
		
		// store update
		$this->updateTopic($topic_id, $update);
	}
	
	// reject topic (moves topic back into pipeline and deletes all threads/comments)
	function rejectTopic($topic_id) {
		// load topic
		if (!$topic = $this->loadTopic($topic_id)) {
			return false;
		}

		// delete all comments
		$this->dbQuery("delete from ".COMMENTS_TABLE." where topic_id = '".addSlashes($topic_id)."'");
		
		// delete all threads
		$this->dbQuery("delete from ".COMMENTS_TABLE." where topic_id = '".addSlashes($topic_id)."'");

		// delete all votes
		$this->deleteVotes($topic_id);

		// prepare update
		$update["location"] = "pipeline";
		$update["comment_count"] = 0;
		$update["thread_count"] = 0;
		$update["locked"] = "N";
		
		// store update
		$this->updateTopic($topic_id, $update);

		// award points to topic owner
		if ($topic["author_id"]) {
			$this->addPoints($this->config["reject_points"], $topic["author_id"]);			
		}

	}
	
	// publishes topic (moves topic to "frontpage") and returns true on success
	function publishTopic($topic_id) {
		// load topic
		if (!$topic = $this->loadTopic($topic_id)) {
			return false;
		}

		// prepare update
		$update["location"] = "frontpage";
		$update["when_published"] = $this->unix2datetime();
		
		// create a thread if topic is "flat" and no thread exists yet
		// (a thread may exist if the topic has previously been published)
		if ($topic["comments_mode"] == "flat") {
			if (!$this->loadFirstThread($topic["id"])) {
				$thread["topic_id"] = $topic["id"];
				$thread["title"] = $topic["title"];
				$thread["when_opened"] = $topic["when_published"];
				$thread["author_name"] = $topic["author_name"];
				$thread["author_id"] = $topic["author_id"];
				$topicUpdate["first_thread_id"] = $this->insertThread($thread);
				
				// update topic
				$this->updateTopic($topic["id"], $topicUpdate);
			}
		}
		
		// store update
		$this->updateTopic($topic_id, $update);
		
		// award points to topic owner
		if ($topic["author_id"]) {
			$this->addPoints($this->config["publish_points"], $topic["author_id"]);			
		}

		// delete all votes
		$this->deleteVotes($topic_id);
		
		// success!
		return true;
	}

	// locks a topic
	function lockTopic($topic_id) {
		$this->dbQuery("update ".TOPICS_TABLE." set locked = 'Y' where id = '".addSlashes($topic_id)."' limit 1");
	}

	// deletes a topic and all data associated with it (votes, comments etc)
	function deleteTopic($topic_id) {
		// check if topic exists
		if (!$topic = $this->loadTopic($topic_id)) {
			$this->abort();	
		}
		
		// delete topic itself
		$this->dbQuery("delete from ".TOPICS_TABLE." where id = '".$topic_id."' limit 1");

		// delete all comments
		$this->dbQuery("delete from ".COMMENTS_TABLE." where topic_id = '".$topic_id."' limit 1");

		// delete all threads
		$this->dbQuery("delete from ".THREADS_TABLE." where topic_id = '".$topic_id."' limit 1");

		// TODO: delete all votes
	}

	// updates topic data with last comment time, etc
	function touchTopic($topic_id) {
		// count threads
		$update["thread_count"] = $this->countTopicThreads($topic_id);

		// count comments
		$update["comment_count"] = $this->countTopicComments($topic_id);
		
		// fetch latest comment
		$lastComment = $this->dbLoadSingle("select * from ".COMMENTS_TABLE." where topic_id = '".addSlashes($topic_id)."' order by when_posted desc limit 1");
		
		// get time of last comment
		$update["when_last_comment"] = $lastComment["when_posted"];
		
		// update topic
		$this->updateTopic($topic_id, $update);
	}
	
	// returns the number of threads for a specific topic
	function countTopicThreads($topic_id) {
		if ($data = $this->dbLoadSingle("select count(id) as c from ".THREADS_TABLE." where topic_id = '".addSlashes($topic_id)."'")) {
			return $data["c"];
		}
	}

	// returns the number of comments for a specific topic
	function countTopicComments($topic_id, $since = "") {
		if ($data = $this->dbLoadSingle("select count(id) as c from ".COMMENTS_TABLE." where topic_id = '".addSlashes($topic_id)."'".($since ? " and when_posted > '".addSlashes($since)."'" : ""))) {
			return $data["c"];
		}
	}

	// returns the number of all published topics
	function countPublishedTopics() {
		$data = $this->dbLoadSingle("select count(id) as c from ".TOPICS_TABLE." where location = 'frontpage'");
		return $data["c"];
	}

	// returns the number of all topics in the submission bin
	function countSubmittedTopics() {
		if (!isset($GLOBALS[BLAH_GLOBALVAR]["countSubmittedTopics"])) {
			$data = $this->dbLoadSingle("select count(id) as c from ".TOPICS_TABLE." where location = 'submission'");
			$GLOBALS[BLAH_GLOBALVAR]["countSubmittedTopics"] = $data["c"];
		}
		return $GLOBALS[BLAH_GLOBALVAR]["countSubmittedTopics"];
	}
	
	
	// THREAD FUNCTIONS

	// loads threads from the database
	function loadThreads($extraSql = "") {
		$query = "select ".THREADS_TABLE.".*, ".LASTREAD_TABLE.".lastread from ".THREADS_TABLE
			." left join ".LASTREAD_TABLE." on ".LASTREAD_TABLE.".thread_id = ".THREADS_TABLE.".id and ".LASTREAD_TABLE.".user_id = '".addSlashes($_SESSION[USER_SESSIONVAR]["id"])."' "
			.$extraSql;
			
		if ($threads = $this->dbLoad($query)) {
			for ($i = 0; $i < count($threads); $i++) {
				// if no lastread is set, set to global lastread
				if (!$threads[$i]["lastread"]) {
					$threads[$i]["lastread"] = $this->session["global_lastread"];
				}
			}
			
			// return topic data
			return $threads;
		}
	}
	
	// loads a single thread from the database
	function loadThread($id) {
		// check cache
		if (!isset($GLOBALS[BLAH_GLOBALVAR]["threadCache"][$id])) {
			// not present -- load from database
			$threads = $this->loadThreads("where ".THREADS_TABLE.".id = '".addSlashes($id)."' limit 1");
			$GLOBALS[BLAH_GLOBALVAR]["threadCache"][$id] = $threads[0];
		}
		
		return $GLOBALS[BLAH_GLOBALVAR]["threadCache"][$id];		
	}

	// loads the first thread of a the specified topic
	function loadFirstThread($topic_id) {
		$threads = $this->loadThreads("where ".THREADS_TABLE.".topic_id = '".addSlashes($topic_id)."' limit 1");
		return $threads[0];
	}

	// displays comments in a thread
	function displayThread($id, $offset = 0, $limit = 0) {
		// load thread
		if (!$thread = $this->loadThread($id)) {
			return false;
		}
		
		// load topic
		if (!$topic = $this->loadTopic($thread["topic_id"])) {
			return false;
		}
		
		// if no limit is specified, set it to default limit
		/*
		if (!$limit) {
			$limit = PAGE_LENGTH;
		}
		*/
		
		// load comments
		$comments = $this->loadCommentsInThread($id, $offset, $limit);

		// use global lastread as default
		$lastread = $this->session["global_lastread"];

		// set lastread if user is logged in
		if ($this->userIsLoggedIn()) {
			// get the datetime of the last comment displayed; if no comments are available,
			// set it to the currente date time
			if ($comments) {
				$count = count($comments);
				$lastComment = $comments[$count - 1];
				$newLastRead = $lastComment["when_posted"];
			} else {
				$newLastRead = $this->unix2datetime();
			}
			
			// load previous last read
			if ($oldLastRead = $this->loadLastRead($id)) {
				// if the last comment's datetime is higher than the previously stored lastread datetime, set a new one
				if ($newLastRead > $oldLastRead["lastread"]) {
					$this->setLastRead($id, $newLastRead);
				}
				
				// remember lastread for comments
				$lastread = $oldLastRead["lastread"];
			} else {
				// no lastread present, set lastread!
				$this->setLastRead($id, $newLastRead);
			}
		}

		// display thread
		$this->template(COMMENTS_TEMPLATE, compact("thread", "topic", "comments", "offset", "limit", "lastread"));
		
	}
		
	// creates a new thread with $title under topic $topic_id
	function insertThread($thread) {
		return $this->dbInsert(THREADS_TABLE, $thread);
	}

	// updates thread data with last comment time, etc
	function touchThread($thread_id) {
		// count comments
		$update["comment_count"] = $this->countThreadComments($thread_id);

		// fetch latest comment
		$lastComment = $this->dbLoadSingle("select * from ".COMMENTS_TABLE." where thread_id = '".addSlashes($thread_id)."' order by when_posted desc limit 1");
		// get time
		$update["when_last_comment"] = $lastComment["when_posted"];
		
		// update thread
		$this->dbUpdate(THREADS_TABLE, $update, "where id = '".addSlashes($thread_id)."' limit 1");
	}

	
	// LASTREAD STATE TRACKING
	
	// sets the last read marker for the given thread to $to. If $to is not specified,
	// it uses the current date and time. $to is supposed to be a mysql datetime, not a unix timestamp!
	// if $user_id isn't specified, use logged in user
	function setLastRead($thread_id, $to = 0, $user_id = 0) {
		// default $to?
		if (!$to) {
			$to = $this->unix2datetime();
		}
		
		// load/check user
		if ($user_id) {
			if (!$user = $this->loadUser($user_id)) {
				return false;
			}
		} else {
			if (!$this->userIsLoggedIn()) {
				return false;
			}
			$user_id = $_SESSION[USER_SESSIONVAR]["id"];
		}
		
		// only store if $to is higher than the global lastread
		if ($to > $this->session["global_lastread"]) {
			// thread must exist
			if ($thread = $this->loadThread($thread_id)) {
				// build array
				$lastread["user_id"] = $user_id;
				$lastread["thread_id"] = $thread_id;
				$lastread["lastread"] = $to;
				// delete old lastread pointer
				$this->dbQuery("delete from ".LASTREAD_TABLE." where user_id = '".$lastread["user_id"]."' and thread_id = '".$lastread["thread_id"]."' limit 1");
				// write new lastread pointer
				$this->dbInsert(LASTREAD_TABLE, $lastread);
			}
		}
	}

	// loads the lastread entry for the specified thread
	function loadLastRead($thread_id, $user_id = 0) {
		if ($user_id) {
			if (!$user = $this->loadUser($user_id)) {
				return false;
			}
		} else {
			if (!$this->userIsLoggedIn()) {
				return false;
			}
			$user_id = $_SESSION[USER_SESSIONVAR]["id"];
		}
		
		return $this->dbLoadSingle("select * from ".LASTREAD_TABLE." where user_id = '".addslashes($user_id)."' and thread_id = '".addSlashes($thread_id)."' limit 1");
	}
	
	
	// returns true if a topic has new comments since the last time the user read it.
	// $topic is an actual topic record set, not an id!
	function topicHasNewComments(&$topic) {
	
		switch ($topic["comments_mode"]) {
			
			case "flat":
				// flat mode is easy. just check if the datetime of the last
				// comment is higher than the lastread.
				return ($topic["when_last_comment"] > $topic["lastread"]);
				
			case "semithreaded":
				// this, unfortunately, is a bit more complex, since we need
				// to check the lastreads of all threads within this topic.
				if ($threads = $this->loadThreads("where ".THREADS_TABLE.".topic_id = '".addSlashes($topic["id"])."'")) {
					foreach ($threads as $thread) {
						if ($this->threadHasNewComments($thread)) {
							return true;
						}
					}
				}
				break;
		}
	}

	// returns true if a thread has new comments since the last time the user read it.
	// $thread is an actual thread record set, not an id!
	function threadHasNewComments(&$thread) {
		return ($thread["when_last_comment"] > $thread["lastread"]);
	}

	// returns the number of new comments posted to a topic. should be used
	// with "flat" topics only.
	function countNewTopicComments(&$topic, $since = 0) {
		// use topic's lastread by default
		if (!$since) {
			$since = $topic["lastread"];
		}
		
		// count!
		if ($topic["comments_mode"] == "flat") {
			$query = "select count(id) as c from ".COMMENTS_TABLE." where topic_id = '".addSlashes($topic["id"])."' and when_posted > '".addSlashes($since)."'";
			$data = $this->dbLoadSingle($query);
			$count = $data["c"];
		} else {
			// count new comments in all threads
			if ($threads = $this->loadThreads("where topic_id = '".addSlashes($topic["id"])."' and when_last_comment > lastread")) {
				foreach ($threads as $thread) {
					$count += $this->countNewThreadComments($thread);
				}
			}
		}
		
		return $count;
	}

	// returns the number of new comments posted to a thread.
	function countNewThreadComments(&$thread, $since = 0) {
		// use thread's lastread by default
		if (!$since) {
			$since = $thread["lastread"];
		}
		
		// count!
		$query = "select count(id) as c from ".COMMENTS_TABLE." where thread_id = '".addSlashes($thread["id"])."' and when_posted > '".addSlashes($since)."'";
		$data = $this->dbLoadSingle($query);
		return $data["c"];
	}

	
	

	// COMMENTS FUNCTIONS
	
	// loads all comments of the specified thread
	function loadCommentsInThread($thread_id, $offset = 0, $limit = 0) {
		// for security
		if (!$offset = intval($offset)) {
			$offset = 0;
		}
		if (!$limit = intval($limit)) {
			$limit = 0;
		}
		
		// if limit is 0, user wants everything, so change it to -1
		if (!$limit) {
			$limit = -1;
		}
		
		return $this->dbLoad("select * from ".COMMENTS_TABLE." where thread_id = '".addSlashes($thread_id)."' order by num limit $offset,$limit");

		/*
		return $this->dbLoad(
			"select ".COMMENTS_TABLE.".*, ".USERS_TABLE.".show_email from ".COMMENTS_TABLE.",".USERS_TABLE." where ".COMMENTS_TABLE.".thread_id = '".addSlashes($thread_id)."' and (".COMMENTS_TABLE.".author_id = 0 or ".COMMENTS_TABLE.".author_id = ".USERS_TABLE.".id) order by ".COMMENTS_TABLE.".num limit $ofFset,$limit");
		*/
	}
	
	// displays the form that allows users to post comments
	// $topic_id is the id of the topic the comment should be assigned to.
	// $thread_id is the id of the thread the comment should go into.
	// If $thread_id is 0, a new thread will be opened.
	function displayCommentForm($topic_id, $thread_id = 0, $comment = "", $offset, $limit) {
		// signature?
		$this->template(COMMENTFORM_TEMPLATE, compact("topic_id", "thread_id", "comment", "offset", "limit"));
	}

	// inserts a comment into the database and returns its ID.
	function insertComment($comment) {
		// GAK - Shouldn't locking the table fix dupe numbers?
		$this->dbQuery("lock tables ".COMMENTS_TABLE." write");

		// just in case... set comment number to next highest
		$data = $this->dbLoadSingle("select max(num) as maxnum from ".COMMENTS_TABLE." where thread_id = '".addSlashes($comment["thread_id"])."'");
		$comment["num"] = $data["maxnum"] + 1;
	
		// GAK - Insert the comment and unlock	
		$result = $this->dbInsert(COMMENTS_TABLE, $comment);
		$this->dbQuery("unlock tables");
		return $result;
		// go!
		//return $this->dbInsert(COMMENTS_TABLE, $comment);
	}

	// updates a comment
	function updateComment($comment_id, $comment) {
		$this->dbUpdate(COMMENTS_TABLE, $comment, "where id = '".addSlashes($comment_id)."' limit 1");
	}

	// returns the number of comments for a specific thread
	function countThreadComments($thread_id, $since = "") {
		if ($data = $this->dbLoadSingle("select count(id) as c from ".COMMENTS_TABLE." where thread_id = '".addSlashes($thread_id)."'".($since ? " and when_posted > '".addSlashes($since)."'" : ""))) {
			return $data["c"];
		}
	}

	// loads a comment by id
	function loadComment($id) {
		return $this->dbLoadSingle("select * from ".COMMENTS_TABLE." where id = '".addSlashes($id)."' limit 1");
	}

	// count ALL comments
	function countAllComments() {
		$data = $this->dbLoadSingle("select count(id) as c from ".COMMENTS_TABLE);
		return $data["c"];
	}
		

	// USER FUNCTIONS

	// inserts a new user into the database and returns his id.
	function insertUser($user) {
		return $this->dbInsert(USERS_TABLE, $user);
	}

	// updates user data
	function updateUser($user_id, $user) {
		$this->dbUpdate(USERS_TABLE, $user, "where id = '".addSlashes($user_id)."' limit 1");
	}
	
	// adds points to a user's score
	function addPoints($points, $user_id = 0) {
		if (!$user_id) {
			if ($this->userIsLoggedIn()) {
				$user_id = $_SESSION[USER_SESSIONVAR]["id"];
			} else {
				return false;
			}
		}
		
		$this->dbQuery("update ".USERS_TABLE." set score = score + '".addSlashes($points)."' where id = '".addSlashes($user_id)."' limit 1");
		return true;
	}
	
	// sets a user's password
	function setPassword($user_id, $password) {
		// just in case...
		if ($password) {
			$this->dbQuery("update ".USERS_TABLE." set password = old_password('".addSlashes($password)."') where id = '".addSlashes($user_id)."' limit 1");
		}
	}

	// loads a user by id
	function loadUser($id, $force = 0) {
		// check cache
		if ((!$user = $GLOBALS[BLAH_GLOBALVAR]["userCache"][$id]) || $force) {
			// not present -- load from database
			if ($user = $this->dbLoadSingle("select * from ".USERS_TABLE." where id = '".addSlashes($id)."' limit 1")) {
				$GLOBALS[BLAH_GLOBALVAR]["userCache"][$id] = $user;
			}
		}
		
		// "uncompress" extra data
		$user["extradata"] = unserialize($user["extradata"]);
		
		return $user;		
	}

	// re-loads current user
	function refreshUser() {
		if ($this->userIsLoggedIn()) {
			$_SESSION[USER_SESSIONVAR] = $this->loadUser($_SESSION[USER_SESSIONVAR]["id"], 1);
			return true;
		}
	}

	// delete a user and all data associated with him.
	function deleteUser($id) {
		// check if user exists
		if (!$user = $this->loadUser($id)) {
			return false;
		}
		
		// delete all lastread entries for this user
		$this->dbQuery("delete from ".LASTREAD_TABLE." where user_id = '".addSlashes($id)."' limit 1");

		// delete all votes from this user
		$this->dbQuery("delete from ".VOTES_TABLE." where user_id = '".addSlashes($id)."' limit 1");
		
		// remove user id from all comments
		$this->dbQuery("update ".COMMENTS_TABLE." set author_id = 0 where author_id = '".addSlashes($id)."'");

		// remove user id from all topics
		$this->dbQuery("update ".TOPICS_TABLE." set author_id = 0 where author_id = '".addSlashes($id)."'");

		// remove user id from all threads
		$this->dbQuery("update ".TOPICS_TABLE." set author_id = 0 where author_id = '".addSlashes($id)."'");
		
		// delete actual user data
		$this->dbQuery("delete from ".USERS_TABLE." where id = '".addSlashes($id)."' limit 1");
		
		// success!
		return true;
	}

	// delete all stale users (unverified and old)
	function purgeStaleUsers() {
		$this->dbQuery("delete from ".USERS_TABLE." where is_verified = 'N' and to_days(now()) - to_days(when_created) >= 1");
	}
	
	// loads a user by email
	function loadUserByEmail($email) {
		return $this->dbLoadSingle("select * from ".USERS_TABLE." where email = '".addSlashes($email)."' limit 1");
	}

	// loads a user by name
	function loadUserByName($name) {
		return $this->dbLoadSingle("select * from ".USERS_TABLE." where name = '".addSlashes($name)."' limit 1");
	}
	
	// activates user account
	function activateUser($user_id) {
		// does user exist?
		if (!$user = $this->loadUser($user_id)) {
			return false;
		}
		
		// is user already activated?
		if ($user["is_verified"] == "Y") {
			return false;
		}
		
		// set account to activated
		$update["is_verified"] = "Y";
		
		// mark all as read, if configured to do so
		if (MARK_ALL_READ_ON_SIGNUP == 1) {
			// set global last read
			$update["global_lastread"] = $this->unix2datetime();
		}

		// store update
		$this->dbUpdate(USERS_TABLE, array("is_verified" => "Y"), "where id = '".addSlashes($user_id)."' limit 1");
		
		// success!
		return true;
	}

	// tries to login user; returns true on success
	function loginUser($email, $password, $remember = 0) {
		// load user
		if ($user = $this->dbLoadSingle("select * from ".USERS_TABLE." where (email = '".addSlashes($email)."' or name = '".addSlashes($email)."') and password = old_password('".addSlashes($password)."') and is_verified = 'Y' limit 1")) {
			// store user in session data
			$_SESSION[USER_SESSIONVAR] = $user;
			
			// remember?
			if ($remember) {
				// set auto-login cookies
				$this->setCookie(LOGIN_EMAIL_COOKIE, $email);
				$this->setCookie(LOGIN_PASSWORD_COOKIE, $password);
			}

			// update user account with login time
			$this->updateUser($user["id"], array("when_last_login" => $this->unix2datetime()));
			
			// success!
			return true;
		}
	}

	// returns true if a user is currently logged in
	function userIsLoggedIn() {
		if ($_SESSION[USER_SESSIONVAR]) {
			return true;
		}
	}
	
	// checks if user is logged in; if not, displays an error page
	function userMustBeLoggedIn() {
		if (!$this->userIsLoggedIn()) {
			$this->simplePage("Access Denied", "You must be <a href=\"".$_SERVER["PHP_SELF"]."?action=login&return=".urlencode($_SERVER["REQUEST_URI"])."\">logged in</a> to access this page!");
			exit;
		}
	}

	// reutnrs ture if current user is superuser
	function userIsSuperuser() {
		if ($_SESSION[USER_SESSIONVAR]["is_superuser"] == "Y") {
			return true;
		}
	}

	// returns true if current user is allowed to delete a comment
	function userCanDeleteComment($comment_id) {
		// superusers can delete anything!
		return $this->userIsSuperuser();
	}
	
	// returns true if current user is allowed to modify the specified topic
	function userCanEditTopic($topic_id) {
		// guests can't.
		if (!$this->userIsLoggedIn()) {
			return false;
		}
		
		if ($topic = $this->loadTopic($topic_id)) {
			// superusers can edit any topic
			if ($this->userIsSuperuser()) {
				return true;
			}
			
			// registered users can edit their own topics as long as
			// they're in "pipeline"
			if (($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"]) && ($topic["location"] == "pipeline")) {
				return true;
			}
		}
	}
	
	// returns true if current user can view topic
	function userCanViewTopic($topic_id) {
		// load topic
		if ($topic = $this->loadTopic($topic_id)) {
			// superusers can view all topics at any time
			if ($this->userIsSuperuser()) {
				return true;
			}
			
			// anyone can view topics in "frontpage" or "submission"
			if (($topic["location"] == "frontpage") || ($topic["location"] == "submission")) {
				return true;
			}
			
			// topic owners can view their own "pipeline" topics
			if (($topic["location"] == "pipeline") && ($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"])) {
				return true;
			}
		}
	}

	// returns true if current user is allowed to post a comment to specified topic
	function userCanPostComment($topic_id) {
		if ($topic = $this->loadTopic($topic_id)) {
			// people can post comments if the topic is in "frontpage"
			if ($topic["location"] == "frontpage") {
				return true;
			}
		}
	}

	// returns true if current user can submit ("pipeline" -> "submission") topic
	function userCanSubmitTopic($topic_id) {
		// guests can't.
		if (!$this->userIsLoggedIn()) {
			return false;
		}

		if ($topic = $this->loadTopic($topic_id)) {
			// topic must be in pipeline
			if ($topic["location"] == "pipeline") {
				// superusers can always submit anything and everything if they want to! yeah!
				if ($this->userIsSuperuser()) {
					return true;
				}
				
				// if user is owner of this topic, he can, too.
				if ($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"]) {
					return true;
				}
			}
		}
	}
	
	// returns true if current user can push topic to front page
	function userCanPublishTopic($topic_id) {
		// guests can't.
		if (!$this->userIsLoggedIn()) {
			return false;
		}

		if ($topic = $this->loadTopic($topic_id)) {
			// topic must not be published yet
			if ($topic["location"] != "frontpage") {
				// only superusers can do so.
				if ($this->userIsSuperuser()) {
					return true;
				}
			}
		}
	}
	
	// returns true if user can reject (move back to "pipeline") a topic
	function userCanRejectTopic($topic_id) {
		// guests can't.
		if (!$this->userIsLoggedIn()) {
			return false;
		}

		if ($topic = $this->loadTopic($topic_id)) {
			// topic must not be in pipeline
			if ($topic["location"] != "pipeline") {
				// superusers can always reject topic
				if ($this->userIsSuperuser()) {
					return true;
				}
				
				// topic owner can only reject topic if no comments have been posted yet
				if ($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"]) {
					if (!$this->countTopicComments($topic_id)) {
						return true;
					}
				}
			}
		}
	}

	// returns true if user can completely delete a topic
	function userCanDeleteTopic($topic_id) {
		// guests can't.
		if (!$this->userIsLoggedIn()) {
			return false;
		}

		if ($topic = $this->loadTopic($topic_id)) {
			// superuser can delety anything
			if ($this->userIsSuperuser()) {
				return true;
			}

			// topic owner can only delete his own topic if in "pipeline"
			if ($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"]) {
				if ($topic["location"] == "pipeline") {
					return true;
				}
			}
		}
	}
	
	// returns true if user is allowed to vote on topic (but it doesn't
	// check if the user has already voted on it!)
	function userCanVoteOnTopic($topic_id) {
		// guests can't.
		if (!$this->userIsLoggedIn()) {
			return false;
		}

		if ($topic = $this->loadTopic($topic_id)) {
			// topic owners can't vote on their own topics!
			if ($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"]) {
				return false;
			}
			
			// everyone else can vote *if* the topic is not not locked and
			// *not* in the pipeline.
			if (($topic["location"] != "pipeline") && ($topic["locked"] == "N")) {
				return true;
			}
		}
	}
	
	// returns true if user has already voted on topic
	function userHasVotedOnTopic($topic_id) {
		// guests have never voted, because they can't!
		if (!$this->userIsLoggedIn()) {
			return false;
		}

		if ($this->dbLoadSingle("select * from ".VOTES_TABLE." where user_id = '".addSlashes($_SESSION[USER_SESSIONVAR]["id"])."' and topic_id = '".addSlashes($topic_id)."' limit 1")) {
			return true;
		}
	}

	// returns true if current user is allowed to modify the profile of
	// the user with $user_id.
	function userCanEditProfile($user_id) {
		// guests can never edit profiles.
		if (!$this->userIsLoggedIn()) {
			return false;
		}
		
		// does user exist?
		if (!$user = $this->loadUser($user_id)) {
			return false;
		}
		
		// user can always edit his own profile.
		if ($_SESSION[USER_SESSIONVAR]["id"] == $user["id"]) {
			return true;
		}
		
		// superusers can edit other users' profiles.
		if ($_SESSION[USER_SESSIONVAR]["is_superuser"] == "Y") {
			return true;
		}
	}
		
	
	// ONLINE TRACKING
	
	function setOnline($url, $title) {
		// build data
		$online = array(
					"session_id"	=> session_id(),
					"when_visited"	=> $this->unix2datetime(),
					"url"			=> $url,
					"title"			=> $title);

		// delete old online state
		$this->dbQuery("delete from ".ONLINE_TABLE." where session_id = '".addSlashes(session_id())."'");

		// if user is logged in, also delete entries for his user_id
		if ($this->userIsLoggedIn()) {
			$this->dbQuery("delete from ".ONLINE_TABLE." where user_id = '".addslashes($_SESSION[USER_SESSIONVAR]["id"])."'");

			// add user id?
			if ($_SESSION[USER_SESSIONVAR]["show_online"] == "Y") {
				$online["user_id"] = $_SESSION[USER_SESSIONVAR]["id"];
			}
		}
				
		// insert new online state				
		$this->dbInsert(ONLINE_TABLE, $online);
	}
	
	// returns the number of users online
	function countOnlines() {
		// cache
		if (!isset($GLOBALS[BLAH_GLOBALVAR]["countOnlines"])) {
			if ($data = $this->dbLoadSingle("select count(session_id) as c from ".ONLINE_TABLE)) {
				$GLOBALS[BLAH_GLOBALVAR]["countOnlines"] = $data["c"];
			}
		}
		
		// return count
		return $GLOBALS[BLAH_GLOBALVAR]["countOnlines"];
	}
	
	// purges expired online entries
	function purgeOnlines() {
		$this->dbQuery("delete from ".ONLINE_TABLE." where (when_visited + interval '".addSlashes($this->config["expire_online"])."' minute) < now()");
	}
	
	// returns an array with all current online entries
	function loadOnlines() {
		return $this->dbLoad("select ".ONLINE_TABLE.".*, ".USERS_TABLE.".name as user_name from ".ONLINE_TABLE." "
			."left join ".USERS_TABLE." on ".ONLINE_TABLE.".user_id = ".USERS_TABLE.".id order by user_name");
	}
	
	
	// ACTION HANDLERS
	
	// default method for all undefined actions.
	function onUnknown() {
		$this->simplePage("Unknown Action", "Whatever you're trying to access isn't implemented (yet).");
	}

	// test function for debugging
	function onTest() {
		phpinfo();
	}

	// PHP Info
	function onPHPInfo() {
		phpinfo();
	}

	// alias for ListTopics
	function onHome() {
		$this->onListTopics();
	}
	
	// alias listtopics to home
	function onListTopics() {
		
		/*
		Couple o'notes on modes. Yes yes y'all. Dig?

		We have three modes determined by $location:
		
		"frontpage" will display the topics published to the front page
		as configured in config.php.
		
		"submission" will list *all* topics currently waiting in the
		submissino bin.
		
		"pipeline" will list the topics belonging to the current user.

		Only if a topic is still in "pipeline", it can be edited.
		Only if a topic is still in "submission", it can be put back into "pipeline".
		Once a topic is in "frontpage", only superusers can put it back into pipeline.
		*/
		
		// do whatcha hafta do!
		switch ($_GET["location"]) {

			// the current user's pipeline
			case "pipeline":
				// check if user is logged in
				$this->userMustBeLoggedIn();
				
				// load all topics in this user's pipeline
				$topics = $this->dbLoad("select * from ".TOPICS_TABLE." where location = 'pipeline' and author_id = '".addSlashes($_SESSION[USER_SESSIONVAR]["id"])."' order by when_created desc");
				
				// display front page
				$this->pageOpen("Your Topic Pipeline", "Looking at his/her topic pipeline");
				$this->template(PIPELINE_TEMPLATE, compact("topics"));
				$this->pageClose();

				break;
			
			// the submission bin
			case "submission":
				// load all topics current in submission bin
				$topics = $this->loadTopics("where location = 'submission' order by when_submitted");
				
				// display front page
				$this->pageOpen("Submission Bin");
				$this->template(SUBMISSIONBIN_TEMPLATE, compact("topics"));
				$this->pageClose();
				break;		
		
			// default is "frontpage" behaviour
			default:
				// load all topics on front page
				$topics = $this->loadTopics("where location = 'frontpage' order by when_published desc limit ".TOPICS_ON_FRONTPAGE);
				
				// display front page
				$this->pageOpen("", "Front Page");
				$this->template(FRONTPAGE_TEMPLATE, compact("topics"));
				$this->pageClose();
		}
		
	}
	
	// displays a topic and its comments
	function onViewTopic() {
		// fetch data
		$topic_id = $_GET["topic_id"];
		$offset = $_GET["offset"];
		$limit = $_GET["limit"];
		
		if (!isset($limit)) {
			$limit = $this->pageLength;
		}
		
		
		// check if user is allowed to view topic
		if (!$this->userCanViewTopic($topic_id)) {
			$this->abort();
		}
		
		// load topic
		$topic = $this->loadTopic($topic_id) or die('Topic not found');
		
		// display topic
		$this->pageOpen($topic['title'], "Reading Topic: ".$topic["title"]);
		$this->template(TOPIC_TEMPLATE, array('topic' => $topic, 'mode' => 'full'));

		// display comments/threads
		switch ($topic['comments_mode']) {
		
			// flat comments format
			case "flat":
				// load topic's only thread
				$thread = $this->loadFirstThread($topic["id"]);
				
				// display it
				$this->displayThread($thread["id"], $offset, $limit);

				if ($this->userCanPostComment($topic["id"])) {
					$this->displayCommentForm($topic["id"], $thread["id"], "", $offset, $limit);
				}
				break;
				
			
			// semi-threaded format
			case "semithreaded":
				
				// load threads for this topic
				$threads = $this->loadThreads("where topic_id = '".addSlashes($topic['id'])."' order by ".SORT_THREADLIST_BY);
				
				// display threads
				$this->template(THREADLIST_TEMPLATE, compact("threads"));
				
				// display comments form for people to start new threads
				if ($this->userCanPostComment($topic["id"])) {
					$this->displayCommentForm($topic["id"], 0, "", $offset, $limit);
				}
				
				break;
			
		}
		
		// close page
		$this->pageClose();

	}
	
	// displays a comments thread
	function onViewThread() {
		// load thread
		$thread = $this->loadThread($_GET["thread_id"]) or die ("Thread not found! :(");
		
		// fetch some more data
		$offset = $_GET["offset"];
		$limit = $_GET["limit"];
		
		// display thread
		$this->pageOpen($thread["title"], "Reading Thread: ".$thread["title"]);
		$this->template(THREAD_TEMPLATE, compact("thread"));
		$this->displayThread($thread["id"], $offset, $limit);
		if ($this->userCanPostComment($thread["topic_id"])) {
			$this->displayCommentForm($thread["topic_id"], $thread["id"], "", $offset, $limit);
		}
		$this->pageClose();
	}
	
	// posts/previews a comment
	function onPostComment() {
		// fetch data
		$topic_id = $_POST["topic_id"];
		$thread_id = $_POST["thread_id"];
		$comment = $_POST["comment"];
		$offset = $_REQUEST["offset"];
		$limit = $_REQUEST["limit"];
		
		// does topic exist?
		$topic = $this->loadTopic($topic_id) or die("Unknown topic! :(");

		// does topic allow comments?
		if (!$this->userCanPostComment($topic["id"])) {
			$this->abort();
		}
		
		// is a thread id given?
		if ($thread_id) {
			// does thread exist?
			$thread = $this->loadThread($_POST["thread_id"]) or die("Unknown thread! :(");
		} else {
			// no thread id is given; check if topic is "semithreaded"
			if ($topic["comments_mode"] != "semithreaded") {
				die("You can't create new threads! :(");
			}
		}
		
		// prepare comment data
		$comment = $this->arrayTrim($this->arrayForceKeys($comment,
			array("author_name", "author_email", "author_url", "title", "body")));
			
		// is user logged in?
		if ($_SESSION[USER_SESSIONVAR]) {
			// yes -- replace author information with user information
			$comment["author_name"] = $_SESSION[USER_SESSIONVAR]["name"];
			$comment["author_email"] = $_SESSION[USER_SESSIONVAR]["email"];
			$comment["author_url"] = $_SESSION[USER_SESSIONVAR]["url"];
			
			// also assign user id
			$comment["author_id"] = $_SESSION[USER_SESSIONVAR]["id"];
			
			// copy signature
			$comment["signature"] = $_SESSION[USER_SESSIONVAR]["signature"];
		} else {
			// okay, he's not logged in. just in case, check if this topic
			// actually allows comments posted by guests.
			if (!$this->topicAllowsGuestComments($topic["id"])) {
				// stupid h4x0rs!
				$this->simplePage("Posting Not Allowed", "You're not allowed to post a comment as a guest.");
				exit;
			}
		}
		
		// strip HTML from author fields and title
		$comment["author_name"]  = strip_tags($comment["author_name"]);
		$comment["author_email"] = strip_tags($comment["author_email"]);
		$comment["author_url"]   = strip_tags($comment["author_url"]);
		$comment["title"]        = strip_tags($comment["title"]);
		
		// set date/time of comment
		$comment["when_posted"] = $this->unix2datetime();
		
		// remember remote host/ip
		$comment["author_ip"] = $_SERVER["REMOTE_ADDR"];
		$comment["author_host"] = $_SERVER["REMOTE_HOST"];
		
		// set the famous default body. It's tradition, d00ds4qz!
		if (!$comment["body"]) {
			$comment["body"] = "[i]Thinking...[/i]";
		}
		
		// also, just in case, check if the author name has a value.
		if (!$comment["author_name"]) {
			$comment["author_name"] = "Anonymous";
		}
		
		// if we're not making a thread and comment titles are disabled, clear title!
		if ($thread && !ALLOW_COMMENT_TITLES) {
			$comment["title"] = "";
		}
		
		// now webify comment body
		//$comment["body_r"] = $this->webify($comment["body"]);

		// set comment number to next highest
		$data = $this->dbLoadSingle("select max(num) as maxnum from ".COMMENTS_TABLE." where thread_id = '".addSlashes($thread["id"])."'");
		$comment["num"] = $data["maxnum"] + 1;
	
		// post or preview?
		if ($_POST["previewmode"]) {
			$this->pageOpen("Comment Preview");
			$this->template(PREVIEW_TEMPLATE);
			$this->template(COMMENT_TEMPLATE, compact("comment"));
			$this->displayCommentForm($topic_id, $thread_id, $comment, $offset, $limit);
			$this->pageClose();
		} else {

			// when we reach this point, the comment is ripe for posting. :P

			// assign topic to comment
			$comment["topic_id"] = $topic["id"];

			// create new thread if needed
			if (!$thread) {
				
				// create a fancy title for the new thread
				if ($comment["title"]) {
					$thread["title"] = $comment["title"];
				} else {
					$thread["title"] = "Untitled Thread";
				}
				
				// set topic id
				$thread["topic_id"] = $topic["id"];
				
				// set author name / id
				$thread["author_name"] = $comment["author_name"];
				if ($_SESSION[USER_SESSIONVAR]) {
					$thread["author_id"] = $_SESSION[USER_SESSIONVAR]["id"];
				}
				
				// set time of creation
				$thread["when_opened"] = $this->unix2datetime();
				
				// create new thread
				$thread_id = $this->insertThread($thread);
				$thread = $this->loadThread($thread_id);
				
				// update topic with first thread id
				$topicUpdate["first_thread_id"] = $thread_id;
				$this->updateTopic($topic["id"], $topicUpdate);
			}
			
			// assign thread
			$comment["thread_id"] = $thread["id"];

			// insert comment into database
			$id = $this->insertComment($comment);

			// load the comment to retrieve its number within the thread
			$comment = $this->loadComment($id);

			// if user is logged in, add some points!
			if ($this->userIsLoggedIn()) {
				$this->addPoints($this->config["comment_points"]);
			}

			// update thread with date/time of comment
			$this->touchThread($comment["thread_id"]);

			// update topic
			$this->touchTopic($comment["topic_id"]);

			// set a bunch of cookies
			$this->setCookie(AUTHOR_NAME_COOKIE, $comment["author_name"]);
			$this->setCookie(AUTHOR_EMAIL_COOKIE, $comment["author_email"]);
			$this->setCookie(AUTHOR_URL_COOKIE, $comment["author_url"]);
			
			// calc new offset via limit
			if (!isset($limit)) {
				$limit = $this->pageLength;
			}
			$offset = $this->calcPageOffset($comment["num"], $limit);
			
			// forward
			switch ($topic["comments_mode"]) {
				case "semithreaded":
					$url = $this->buildUrl(array("action" => "viewthread", "thread_id" => $thread["id"], "offset" => $offset, "limit" => $limit));
					break;
					
				default:
					//$url = $this->buildUrl(array("action" => "viewtopic", "topic_id" => $topic["id"], "offset" => $offset, "limit" => $limit));
					$url = '/topics/'.$topic['id'].'/'.$offset.'/';
			}
			// add anchor to url
			$url .= "#".$comment["num"];
			header("Location: $url");
			exit;
		}
	}
	
	// lets users create new accounts
	function onSignup() {
		if ($_POST["submit"]) {
			// prepare data
			$user = $this->arrayTrim($this->arrayStripTags($this->arrayForceKeys($_POST["user"], array("name", "surname", "captcha", "email" ))));
			$error = '';

			// fetch password
			$password = trim($_POST["password"]);
			$confirmpassword = trim($_POST["confirmpassword"]);

			// user name given?
			if (!$user["name"]) {
				$error .= "You didn't specify your name!<br/>";
			}

			// check my fave captcha
			if ($user["name"] == $user["surname"]) {
				$error .= "Please fuck off.<br/>";
			}

			// check my second fave captcha
			if (strtolower($user["captcha"]) != 'fuck') {
				$error = "Seriously, please fuck off.<br/>";
			}

			// email address given?
			if (!$user["email"]) {
				$error = "You didn't specify your email address!<br/>";
			}
			
			// TODO: check email for format and "evil" free email providers
			
			// check if the two passwords are the same
			if ($password != $confirmpassword) {
				$error = "Passwords don't match!<br/>";
			}
			
			// password too short?
			if (strlen($password) < MINIMUM_PASSWORD_LENGTH) {
				$error .= "Password must be at least ".MINIMUM_PASSWORD_LENGTH." characters long!<br/>";
			}
			
			// check if there already is a user with the same email address
			if ($this->loadUserByEmail($user["email"])) {
				$error .= "Another account is already registered for that email address.<br/>";
			}
		
			// check if the user name is taken
			if ($someonePotentiallyImportant = $this->loadUserByName($user["name"])) {
				// check if we're allowing duplicate names *or* if the name is marked as unique
				if (($someonePotentiallyImportant["unique_name"] == "Y") || (!ALLOW_DUPLICATE_USER_NAMES)) {
					$error .= "You can't use that name! Please pick another.<br/>";
				}
			}
			
		
			// okay... if no error, let's add the account!
			if ($error != '') {
				// make a funky activation key
				$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";  // Lame, I know.
				$charCount = strlen($chars);
				for ($i = 0; $i < 10; $i++) {
					$random = rand() % $charCount;
					$user["activation_key"] .= $chars[$random];
				}

				// set date/time of creation
				$user["when_created"] = $this->unix2datetime();

				// create account
				$user_id = $this->insertUser($user);
				
				// set password
				$this->setPassword($user_id, $password);
				
				// send email
				$parts = preg_split("/\n/", $user["email"]);
				$good = $parts[0];
				mail($good, SITE_TITLE." Account Activation Link", 
					"Hi ".$user["name"]."!\n\n".
					"Please click on the following link to activate your account:\n".
					"http://".$_SERVER["SERVER_NAME"].$_SERVER["PHP_SELF"]."?action=activate&u=".$user_id."&k=".$user["activation_key"]."\n\n".
					"Thank you!\n",
					"From: ".SITE_OWNER_NAME." <".SITE_OWNER_EMAIL.">");
							
				// forward user
				header("Location: ".$_SERVER["PHP_SELF"]."?action=signupsuccess");
				exit;
			}
		}
		
		// display page with form
		$this->pageOpen("New Account");
		$this->template(SIGNUPFORM_TEMPLATE, compact("error"));
		$this->pageClose();
	}
	
	function onSignupSuccess() {
		// display page with form
		$this->pageOpen("New Account Created");
		$this->template(SIGNUPSUCCESS_TEMPLATE);
		$this->pageClose();
	}		
	
	function onActivate() {
		// grab data
		$user_id = $_GET["u"];
		$activation_key = $_GET["k"];
		
		// load and check user
		if ($user = $this->loadUser($user_id)) {
			if ($user["is_verified"] == "N") {
				if ($user["activation_key"] == $activation_key) {
					// activate user
					$this->activateUser($user_id);
					
					// display a nice page
					$this->simplePage("Account Activated", "Your account has been activated. You can now proceed to the <a href=\"".$_SERVER["PHP_SELF"]."?action=login\">login page</a>.");
				} else {
					$this->simplePage("Wrong Activation Key", "Hmm. Not good.");
				}
			} else {
				$this->simplePage("Account Already Activated", "Your account has already been activated! No need to activate it again, fool!");
			}
		} else {
			$this->simplePage("Account Not Found", "The account you are trying to activate could not be found in the database.");
		}
	}

	// lists all registered users
	function onListUsers() {
		// load users
		$users = $this->dbLoad("select * from ".USERS_TABLE." where is_verified = 'Y' order by name");
		
		// display user list
		$this->pageOpen("User List");
		$this->template(USERLIST_TEMPLATE, compact("users"));
		$this->pageClose();
	}
	
	// user login
	function onLogin() {
	
		// fetch some data
		$return = urldecode($_REQUEST["return"]);   // the url to return to after logging in
		
		// trying to login?
		if ($_POST["submit"]) {
		
			// fetch more data
			$email = $_POST["email"];
			$password = $_POST["password"];
			$remember = $_POST["remember"];
		
			// try to login user
			if (!$this->loginUser($email, $password, ($remember == "Y" ? 1 : 0))) {
				$error = "Invalid email/password combination.";
			}
		
			// on success, forward user to where he came from
			if (!$error) {
				// if this is the first login, display "first login" page
				if (!$_SESSION[USER_SESSIONVAR]["when_last_login"]) {
					$this->pageOpen("Welcome!");
					$this->template(FIRSTLOGIN_TEMPLATE);
					$this->pageClose();
				} else {
					// forward to original url
					if (!$return) {
						$return = $_SERVER["PHP_SELF"];
					}
					header("Location: $return");
				}
				exit;
			}
		}
		
		// display login form
		$this->pageOpen("Member Login");
		$this->template(LOGINFORM_TEMPLATE, compact("error", "return"));
		$this->pageClose();
	}

	// logout user
	function onLogout() {
		// remove user from session data
		$_SESSION[USER_SESSIONVAR] = "";  // unset() doesn't work here anymore. :P
		
		// delete cookies
		$this->deleteCookie(LOGIN_EMAIL_COOKIE);
		$this->deleteCookie(LOGIN_PASSWORD_COOKIE);
		
		// forward to return url
		if (!$return = urldecode($_REQUEST["return"])) {
			$return = $_SERVER["PHP_SELF"];
		}
		header("Location: ".$return);
		exit;
	}

	// topic editor. big, ugly chunk of code. beware.
	function onEditTopic() {

		// fetch data
		$return = urldecode($_REQUEST["return"]);

		// if $topic_id is given, we're editing an existing topic.
		// if not, we're creating a new topic.
		if ($topic_id = $_REQUEST["topic_id"]) {
			
			// okay, we're editing an existing topic. let's check
			// if the currently logged in user is allowed to do so.
			if (!$this->userCanEditTopic($topic_id)) {
				$this->abort();
			}
			
			// load topic data
			$topic = $this->loadTopic($topic_id) or $this->abort();
				
		} else {

			// hurray, we're adding a new topic! first of all,
			// check if the user is logged in.
			$this->userMustBeLoggedIn();

			// now fill $topic with some default texts. yo.
			$topic["title"] = "New Topic";
			$topic["location"] = "pipeline";
			$topic["author_name"] = $_SESSION[USER_SESSIONVAR]["name"];
			$topic["author_email"] = $_SESSION[USER_SESSIONVAR]["email"];
			
			// set page title
			$pageTitle = "New Topic";

		}
		
		
		// fine... now let's display the form.
		$this->pageOpen($pageTitle);
		$this->template(TOPICFORM_TEMPLATE, compact("topic", "topic_id", "return"));
		$this->pageClose();
	}
	
	
	// writes topic data to the database (if it's okay to do so)
	function onStoreTopic() {
		// user must be logged in to continue.
		$this->userMustBeLoggedIn();
		
		// fetch interesting data
		$topic_id = $_REQUEST["topic_id"];
		$topic = $_POST["topic"];
		$return = urldecode($_REQUEST["return"]);

		// let's strip $topic of all fields the user is not allowed to modify
		// and set some "forced" fields if necessary.
		if ($this->userIsSuperuser()) {
			$topic = $this->arrayForceKeys($topic, array("title", "author_email", "author_name", "author_id",
				"intro", "body", "locked", "comments_mode", "allow_guest_comments", "show_both"));
		} else {
			$topic = $this->arrayForceKeys($topic, array("title", "intro", "body", "comments_mode", "show_both"));
		}

		// remove evil HTML from fields
		$topic["title"] = $this->webify($topic["title"], 0);
		
		// trim all fields of whitespace crap.
		$topic = $this->arrayTrim($topic);
	
		// set default title if one is missing
		if (!$topic["title"]) {
			$topic["title"] = "Untitled Topic";
		}
		
		
		// are we storing a new topic, or updating an existing one?
		if ($topic_id) {
			// we're updating an existing topic.
			
			// remember date and time of modification
			$topic["when_modified"] = $this->unix2datetime();
			
			// store update
			$this->updateTopic($topic_id, $topic);
			
		} else {
			// we're storing a new topic. cool.
			
			// set author information
			$topic["author_name"] = $_SESSION[USER_SESSIONVAR]["name"];
			$topic["author_email"] = $_SESSION[USER_SESSIONVAR]["email"];
			$topic["author_id"] = $_SESSION[USER_SESSIONVAR]["id"];
			
			// set date and time of creation
			$topic["when_created"] = $this->unix2datetime();
		
			// new topics go into "pipeline" and are not locked
			$topic["location"] = "pipeline";
			$topic["locked"] = "N";
			
			// insert topic into database
			$this->insertTopic($topic);
		}
		
		// forward user
		if (!$return) {
			$return = $_SERVER["PHP_SELF"];
		}
		header("Location: ".$return);
		exit;
	}
	
	// moves a topic from "pipeline" to "submission"
	function onSubmitTopic() {
		// fetch topic id
		$topic_id = $_GET["topic_id"];

		// check
		if (!$this->userCanSubmitTopic($topic_id)) {
			$this->abort();
		}
		
		// submit topic
		$this->submitTopic($topic_id);
		
		// forward user to submission bin
		header("Location: ".$_SERVER["PHP_SELF"]."?location=submission");
		exit;
	}
	
	// moved a topic back into "pipeline"
	function onRejectTopic() {
		// fetch data
		$topic_id = $_GET["topic_id"];
		
		// load topic
		if (!$topic = $this->loadTopic($topic_id)) {
			$this->abort();
		}
		
		// check
		if (!$this->userCanRejectTopic($topic_id)) {
			$this->abort();
		}
		
		// (try to) submit topic
		$this->rejectTopic($topic_id);
		
		// if user rejected his own topic, forward to submission pipeline
		if ($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"]) {
			$return = $GLOBALS["PHP_SEFL"]."?location=pipeline";
		} else {
			$return = $GLOBALS["PHP_SEFL"]."?location=submission";
		}
		header("Location: ".$return);
		exit;
	}

	// publish a topic on the front page
	function onPublishTopic() {
		// fetch data
		$topic_id = $_GET["topic_id"];
		
		// load topic
		if (!$topic = $this->loadTopic($topic_id)) {
			$this->abort();
		}
		
		// check
		if (!$this->userCanPublishTopic($topic_id)) {
			$this->abort();
		}
		
		// publish topic
		$this->publishTopic($topic_id);
		
		// lock topic
		$this->lockTopic($topic_id);
		
		// forward to front page
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	
	function onDeleteTopic() {
		// fetch data
		$topic_id = $_GET["topic_id"];
		
		// load topic
		if (!$topic = $this->loadTopic($topic_id)) {
			$this->abort();
		}

		// check user privileges
		if (!$this->userCanDeleteTopic($topic_id)) {
			$this->abort();
		}
		
		// delete topic
		$this->deleteTopic($topic_id);
		
		// if user deleted his own topic, forward to submission pipeline; otherwise, jump to front page
		if ($topic["author_id"] == $_SESSION[USER_SESSIONVAR]["id"]) {
			$return = $GLOBALS["PHP_SEFL"]."?location=pipeline";
		} else {
			$return = $GLOBALS["PHP_SEFL"];
		}
		header("Location: ".$return);
	}
	
	// displays a user's profile data
	function onProfile() {
		// load user
		if (!$user = $this->loadUser($_GET["user_id"])) {
			$this->abort();
		}
		
		// display profile
		$this->pageOpen("Profile for ".$user["name"]);
		$this->template(PROFILE_TEMPLATE, compact("user"));
		$this->pageClose();
	}
	
	// display the user profile edit form
	function onEditProfile() {
		// fetch data
		$user_id = $_GET["user_id"];
		
		// is user logged in?
		if (!$this->userIsLoggedIn()) {
			$this->abort();
		}
		
		// if no user id is specified, edit the current user
		if (!$user_id) {
			$user_id = $_SESSION[USER_SESSIONVAR]["id"];
		}
		
		// is user allowed to edit this user profile?
		if (!$this->userCanEditProfile($user_id)) {
			$this->abort();
		}
		
		// load user
		if (!$user = $this->loadUser($user_id)) {
			$this->abort();
		}
		
		// display form
		$this->pageOpen("Editing Profile");
		$this->template(PROFILEFORM_TEMPLATE, compact("user", "extra"));
		$this->pageClose();
	}
	
	// store profile
	function onStoreProfile() {
		// user id must be specified.
		if (!$user_id = $_REQUEST["user_id"]) {
			$this->abort();
		}
		
		// check if user can modify this profile.
		if (!$this->userCanEditProfile($user_id)) {
			$this->abort();
		}
		
		// load old user
		if (!$oldUser = $this->loadUser($user_id)) {
			$this->abort();
		}
		
		// prepare data
		$user = $_POST["user"];
		$user = $this->arrayTrim($this->arrayForceKeys($user,
			array_merge($this->config["user_extra_fields"], array("pagelength", "show_email", "show_online", "view_signatures", "url", "signature"))));

		// process new avatar
		$avatar = $_FILES["avatar"];
		if ($avatar["tmp_name"] && ($avatar["tmp_name"] != "none")) {
			
			// what are we going to call it?
			$user["avatar_filename"] = preg_replace("/[^[:alnum:]]/", "", $oldUser["name"])."-".time().".jpg"; // "-".preg_replace("/[^[:alnum:]\.]/", "", $avatar["name"]);
			$user["avatar_is_custom"] = "Y";
			
			// move uploaded file
			$tempFilename = $this->config["custom_avatars_dir"]."temp-".$user["avatar_filename"];
			if (!@move_uploaded_file($avatar["tmp_name"], $tempFilename)) {
				$this->abort();
			}

			// make master BMP
			$masterFilename = $this->config["custom_avatars_dir"]."master-".$user["avatar_filename"].".bmp";
			$cmd = $this->config["convert_cmd"]." -geometry ".$this->config["large_custom_avatar_width"]."x".$this->config["large_custom_avatar_height"]." ".$tempFilename." ".$masterFilename;
			$eCmd = escapeShellCmd($cmd);
			exec($eCmd);

			// make large thumbnail
			$destinationFilename = $this->config["custom_avatars_dir"]."large-".$user["avatar_filename"];
			$cmd = $this->config["convert_cmd"]." -quality 80 -geometry ".$this->config["large_custom_avatar_width"]."x".$this->config["large_custom_avatar_height"]." ".$masterFilename." jpeg:".$destinationFilename;
			$eCmd = escapeShellCmd($cmd);
			exec($eCmd);

			// make normal thumbnail
			$destinationFilename = $this->config["custom_avatars_dir"].$user["avatar_filename"];
			$cmd = $this->config["convert_cmd"]." -quality 80 -geometry ".$this->config["custom_avatar_width"]."x".$this->config["custom_avatar_height"]." ".$masterFilename." jpeg:".$destinationFilename;
			$eCmd = escapeShellCmd($cmd);
			exec($eCmd);

			// make small thumbnail
			$destinationFilename = $this->config["custom_avatars_dir"]."small-".$user["avatar_filename"];
			$cmd = $this->config["convert_cmd"]." -quality 80 -geometry ".$this->config["small_custom_avatar_width"]."x".$this->config["small_custom_avatar_height"]." ".$masterFilename." jpeg:".$destinationFilename;
			$eCmd = escapeShellCmd($cmd);
			exec($eCmd);

			// remove temporary and master files
			@unlink($tempFilename);
			@unlink($masterFilename);

			// delete old avatar
			if ($oldUser["avatar_is_custom"] == "Y") {
				@unlink($this->config["custom_avatars_dir"].$oldUser["avatar_filename"]);
				@unlink($this->config["custom_avatars_dir"]."small-".$oldUser["avatar_filename"]);
				@unlink($this->config["custom_avatars_dir"]."large-".$oldUser["avatar_filename"]);
			}
		}
				
		// strip html from selected fields (including user fields)
		$user["url"] = strip_tags($user["url"]);
		foreach ($this->config["user_extra_fields"] as $field) {
			$user[$field] = strip_tags($user[$field]);
		}
		
		// update profile data
		$this->updateUser($user_id, $user);
		
		// if user edited his own profile, refresh it!
		if ($user_id == $_SESSION[USER_SESSIONVAR]["id"]) {
			$this->refreshUser();
		}
		
		// forward to profile page
		header("Location: ".$this->buildUrl(array("action" => "profile", "user_id" => $user_id)));
		exit;
	}
	
	// vote!
	function onVote() {
		// fetch data
		$topic_id = $_GET["topic_id"];
		$publish  = $_GET["publish"];
		$return   = urldecode($_GET["return"]);
		
		// load topic
		if (!$topic = $this->loadTopic($topic_id)) {
			$this->abort();
		}
		
		// check if user can vote on this topic
		if (!$this->userCanVoteOnTopic($topic_id)) {
			$this->abort("Can't Place Vote", "You can't place a vote on this topic. It has already been published or rejected.");
		}
		
		// check if user has already voted
		if ($this->userHasVotedOnTopic($topic_id)) {
			$this->abort("Vote Already Placed", "You have already voted on this topic!");
		}
		
		// everything's cool, place vote
		$vote["user_id"]  = $_SESSION[USER_SESSIONVAR]["id"];
		$vote["topic_id"] = $topic_id;
		$vote["publish"]  = $publish;
		$this->dbInsert(VOTES_TABLE, $vote);

		// check if topic has been published/rejected by vote!
		// count all votes
		$data = $this->dbLoadSingle("select count(*) as c from ".VOTES_TABLE." where publish != 'D' and topic_id = '".addSlashes($topic_id)."'");
		if ($data["c"] > PUBLISHVOTES_THRESHOLD) {
			// count "Y" votes
			$data = $this->dbLoadSingle("select count(*) as c from ".VOTES_TABLE." where publish = 'Y' and topic_id = '".addSlashes($topic_id)."'");
			$yesVotes = $data["c"];
			
			// count "N" votes
			$data = $this->dbLoadSingle("select count(*) as c from ".VOTES_TABLE." where publish = 'N' and topic_id = '".addSlashes($topic_id)."'");
			$noVotes = $data["c"];
			
			// if topic is in submission bin, check if it gets published or rejected
			if ($topic["location"] == "submission") {
				if ($yesVotes > $noVotes) {
					// w00h00!
					$this->publishTopic($topic["id"]);
					$this->lockTopic($topic["id"]);
					// send user to front page
					$return = $_SERVER["PHP_SELF"];
				} else {
					// reject topic
					$this->rejectTopic($topic["id"]);
				}
			}
			
			// if topic is on frontpage, a "yes" vote will lock and keep it, a "no" vote will reject it.
			else if ($topic["location"] == "frontpage") {
				if ($yesVotes > $noVotes) {
					// keep the topic and lock it
					$this->lockTopic($topic["id"]);
				} else {
					// reject topic
					$this->rejectTopic($topic["id"]);
				}
			}
			
		}
		
		// forward user
		if (!$return) {
			$return = $this->buildUrl(array("location" => "submission"));
		}
		header("Location: ".$return);
	}
	
	function onArchive() {
		// load all topics
		$topics = $this->loadTopics("where location = 'frontpage' order by when_published desc");
		
		// fetch mode
		$mode = $_GET["mode"];
		
		// display archive
		$this->pageOpen("Archive");
		$this->template(ARCHIVE_TEMPLATE, compact("topics", "mode"));
		$this->pageClose();
	}
	
	function onViewOnliners() {
		// load onliners
		$onliners = $this->loadOnlines();
		
		// display
		$this->pageOpen("Online Users");
		$this->template(ONLINERS_TEMPLATE, compact("onliners"));
		$this->pageClose();
	}
	
	// renumbers a thread
	function onRenumberThread() {
		// superusers only!
		if (!$this->userIsSuperuser()) {
			$this->abort();
		}
		
		// fetch data
		$thread_id = $_GET["thread_id"];

		// thread must exist
		if (!$thread = $this->loadThread($thread_id)) {
			$this->abort();
		}
		
		// load all comments from thread
		if ($comments = $this->loadCommentsInThread($thread_id)) {
			$num = 1;
			foreach ($comments as $comment) {
				$update["num"] = $num++;
				$this->updateComment($comment["id"], $update);
			}
		}
		
		$this->touchThread($thread_id);
		
		print("done!");
	}

	// deletes a comment
	function onDeleteComment() {
		// fetch data
		$comment_id = $_GET["comment_id"];
		$return = urldecode($_GET["return"]);
		
		// check if user can delete comment
		if (!$this->userCanDeleteComment($comment_id)) {
			$this->abort();
		}
		
		// delete comment
		$this->updateComment($comment_id, array("is_deleted" => "Y"));
		
		// return user
		if (!$return) {
			$return = "/";
		}
		header("Location: ".$return);
		exit;
	}		

	// search function
	function onSearch() {
		// fetch data
		$q = $_REQUEST["q"]; // query string
		$l = $_REQUEST["l"]; // location ("c" = comments, "t" = topics)
		$num = intval($_REQUEST["num"]);  // number of results per page
		$skip = intval($_REQUEST["skip"]); // number of results to skip
				
		// prepare $num
		if (!$num) {
			$num = 10;
		}
		
		// $num mustn't be too high
		if ($num > 50) {
			$num = 50;
		}
		
		// prepare $skip
		if (!$skip) {
			$skip = 0;
		}
		
		// search?
		switch ($l) {
			// www ;-)
			case "www":
				header("Location: http://www.google.com/search?q=".urlencode($q)."&num=".$num);
				exit;
				
			// topics
			case "t":
				// search query given?
				if ($q) {
					$match = "match (title,intro,body) against ('".addSlashes($q)."' in boolean mode)";
					$results = $this->loadTopics("where location = 'frontpage' and $match order by score desc limit 100", 1, $match." as score");
				}
				break;
				
			// comments
			case "c":
				if ($q) {
					$match = "match (title,body) against ('".addSlashes($q)."' in boolean mode)";
					$results = $this->dbLoad("select *,$match as score from ".COMMENTS_TABLE." where $match order by score desc limit 100");
				}
				break;			
		}

		// display page
		$this->pageOpen("Search");
		$this->template(SEARCHFORM_TEMPLATE, compact("q", "l", "num"));
		if ($results) {
			$this->template(SEARCHRESULTS_TEMPLATE, compact("q", "l", "results", "skip", "num"));
		}
		$this->pageClose();
	}
	
	// xml-rpc stuff
	function onXmlRpc() {
	}
	
	// klipfood
	function onKlipFood() {
		// load all topics on front page
		$topics = $this->loadTopics("where location = 'frontpage' order by when_published desc limit ".TOPICS_ON_FRONTPAGE);
		
		header("Content-type: text/plain");
		print("<klipfood>\n");
		if ($topics)
		{
			foreach($topics as $topic) {
				print("  <item>\n");
				print("    <title>".utf8_encode($topic["title"])."</title>\n");
				print("    <link>http://".$_SERVER["SERVER_NAME"].$this->buildUrl(array("action" => "viewtopic", "topic_id" => $topic["id"]))."</link>\n");
				print("  </item>\n");
			}
		}
		print("</klipfood>");
	}

	// GAK - OpenBlah support
	function onXml() {
		$mode = $_GET["mode"];
		if(!isset($mode)) {
			$mode = "threads";
		}
		$functionName = "xml".ucfirst($mode);
		if(method_exists($this, $functionName)) {
			call_user_method($functionName, $this);
		}
	}
	
	function dumpXml($data, $tag) {
		header("Content-type: text/xml");
		print("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n");
		print("<$tag"."s>\n");
		if(isset($data)) {
			foreach($data as $datum) {
				print("\t<$tag>\n");
				while($e = each($datum)) {
					print("\t\t<$e[0]><![CDATA[$e[1]]]></$e[0]>\n");
				}
				print("\t</$tag>\n");
			}	
		}
		print("</$tag"."s>");
	}
	
	function xmlTopics() {
		$offset = $_GET["offset"];
		if(!isset($offset)) {
			$offset = 0;
		}

		$limit = $_GET["limit"];
		if(!isset($limit)) {
			$limit = 10;
		}

		$data = $this->loadTopics("where location='frontpage' order by when_published desc limit $limit");
		$this->dumpXml($data, "topic");
	}
	
	function xmlComments() {
		$thread_id = $_GET["thread_id"];
		if(!isset($thread_id)) {
			$thread_id = 1;
		}
		
		$offset = $_GET["offset"];
		if(!isset($offset)) {
			$offset = 0;
		}

		$limit = $_GET["limit"];
		if(!isset($limit)) {
			$limit = 10;
		}
		
		$data = $this->loadCommentsInThread($thread_id, $offset, $limit);
		$this->dumpXml($data, "comment");
	}
	
	function onAtom() {
		header('Content-Type: application/xml');
		$feed = new UniversalFeedCreator();
		// $feed->useCached();
		$feed->title = "PlanetCrap";
		$feed->description = "THIS IS WHAT DEMOCRACY LOOKS LIKE!";
		$feed->link = "http://www.planetcrap.com";
		$feed->syndicationURL = "http://www.planetcrap.com/atom.xml";
		
		$data = $this->loadTopics("where location='frontpage' order by when_published desc limit 10");
		foreach ($data as $topic) {
			$item = new FeedItem();
			$item->title = $topic['title'];
			$item->link = 'http://www.planetcrap.com/topics/'.$topic['id'].'/';
			$item->author = $topic['author_name'];
			$item->date = strtotime($topic['when_published']);
			$item->atomid = "tag:planetcrap.com:topic:".$topic['id'];
			$feed->addItem($item);
		}
		
		print($feed->createFeed("ATOM0.3"));
	}
	
}

?>
