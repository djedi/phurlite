<?php

define('DBNAME', 'phurlite.sqlite3');
define('SHORT_STR_LEN', 4);
define('SHORT_CHARS', '0123456789abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTVWXYZ'); 
define('HOST', $_SERVER['HTTP_HOST']);

$create_table = !file_exists(DBNAME);

if ($db = sqlite_open(DBNAME, 0666, $sqlite_error)) {
	if ($create_table) {
		$query = '
			CREATE TABLE "links" (
			"url" TEXT NOT NULL ON CONFLICT IGNORE UNIQUE ON CONFLICT IGNORE,
			"short" TEXT UNIQUE,
			"hits" INTEGER DEFAULT 0
			);
		';
		sqlite_query($db, $query);
	}
}
else {
	die($sqlite_error);
}

if ($_GET['u']) {
	$short_str = add_url($_GET['u'], $db);
	header('Content-type: text/plain');
	echo shortlink($short_str);
	exit();
}

if ($_GET['p']) {
	$url = get_url($_GET['p'], $db);
	add_hit($url, $db);
	header("Location: $url");
	exit();
}

if (isset($_GET['list'])) {
	$query = "SELECT * FROM links ORDER BY rowid DESC;";
	$res = sqlite_query($db, $query);
	echo '<table><tr><th>Shortened</th><th>Original</th><th>Hits</th></tr>';
	while ($row = sqlite_fetch_object($res)) {
		echo '<tr><td>'.shortlink_html($row->short)."</td><td>$row->url</td><td>$row->hits</td></tr>";
	}
	echo '</table>';
}

function add_url($url, &$db) {
	# verify it is unique
	$query = "SELECT short FROM links WHERE url='$url';";
	$res = sqlite_query($db, $query);
	if (sqlite_num_rows($res)) {
		$row = sqlite_fetch_array($res);
		return $row['short'];
	}
	else {
		$short = get_short_str($url, $db);
		$query = "INSERT INTO links (url, short) VALUES ('$url', '$short');";
		sqlite_query($db, $query);
		return $short;
	}
}

function get_short_str($url, &$db) {
	$short = substr(str_shuffle(SHORT_CHARS), 0, SHORT_STR_LEN);
	# make sure it is not being used
	$query = "SELECT short FROM links WHERE short='$short';";
	$res = sqlite_query($db, $query);
	if (sqlite_num_rows($res)) {
		return get_short_str($url, $db);
	}
	return $short;
}

function get_url($short_str, &$db) {
	$query = "SELECT url FROM links WHERE short='$short_str';";
	$res = sqlite_query($db, $query);
	$row = sqlite_fetch_object($res);
	return $row->url;
}

function add_hit($url, &$db) {
	$query = "UPDATE links SET hits = hits + 1 WHERE url='$url';";
	sqlite_query($db, $query);
}

function shortlink($short_str) {
	return 'http://'.HOST.'/'.$short_str;
}

function shortlink_html($short_str) {
	$shortlink = shortlink($short_str);
	return "<a href=\"$shortlink\">$short_str</a>";
}
