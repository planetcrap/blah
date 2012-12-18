<?php
switch ($l) {
	case "t":
		foreach ($results as $topic) {
			//print($topic["score"]);
			$this->template(TOPIC_TEMPLATE, compact("topic"));
		}
		break;
	case "c":
		foreach ($results as $comment) {
			//print($topic["score"]);
			$this->template(COMMENT_TEMPLATE, compact("comment"));
		}
		break;
}
?>
