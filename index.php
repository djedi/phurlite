<?php

define('DBNAME', 'phurlite.sqlite3.db');
define('SHORT_STR_LEN', 4);
define('SHORT_CHARS', '0123456789abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTVWXYZ');
define('HOST', $_SERVER['HTTP_HOST']);

$create_table = !file_exists(DBNAME);

$db = new SQLite3(DBNAME);
if ($create_table) {
    $query = '
        CREATE TABLE "links" (
        "url" TEXT NOT NULL ON CONFLICT IGNORE,
        "short" TEXT UNIQUE,
        "hits" INTEGER DEFAULT 0
        );
    ';
    $db->exec($query);
}

if (isset($_GET['u'])) {
    $custom = isset($_GET['custom']) ? $_GET['custom'] : false;
    $short_str = add_url($_GET['u'], $custom, $db);
    header('Content-type: text/plain');
    echo shortlink($short_str);
    exit();
}

if (isset($_GET['p'])) {
    $url = get_url($_GET['p'], $db);
    add_hit($url, $db);
    header("Location: $url");
    exit();
}

if (isset($_GET['list'])) {
    $query = 'SELECT * FROM links ORDER BY rowid DESC;';
    $res = $db->query($query);
    echo '<table><tr><th>Shortened</th><th>Original</th><th>Hits</th></tr>';
    while ($row = $res->fetchArray()) {
        echo '<tr><td>' . shortlink_html($row['short']) . "</td><td>{$row['url']}</td><td>{$row['hits']}</td></tr>";
    }
    echo '</table>';
}

function add_url($url, $custom, &$db)
{
    if ($custom) {
        # make sure custom URL is not in use
        $stmt = $db->prepare('SELECT short, url FROM links WHERE short = :short;');
        $stmt->bindParam(':short', $custom, SQLITE3_TEXT);
        $res = $stmt->execute();
        $row = $res->fetchArray();
        if ($row) {
            if ($row['url'] == $url) {
                return $custom;
            }
            http_response_code(409);
            echo 'This custom short code is already in use.';
            exit;
        }
        $short = $custom;
    } else {
        # verify it is unique
        $statement = $db->prepare('SELECT short FROM links WHERE url = :url');
        $statement->bindParam(':url', $url, SQLITE3_TEXT);
        $res = $statement->execute();
        $row = $res->fetchArray();
        if ($row) {
            return $row['short'];
        } else {
            $short = get_short_str($url, $db);
        }
    }
    $statement = $db->prepare('INSERT INTO links (url, short) VALUES (:url, :short);');
    $statement->bindParam(':url', $url, SQLITE3_TEXT);
    $statement->bindParam(':short', $short, SQLITE3_TEXT);
    $statement->execute();
    return $short;
}

function get_short_str($url, &$db)
{
    $short = substr(str_shuffle(SHORT_CHARS), 0, SHORT_STR_LEN);
    # make sure it is not being used
    $statement = $db->prepare('SELECT short FROM links WHERE short = :short;');
    $statement->bindParam(':short', $short, SQLITE3_TEXT);
    $res = $statement->execute();
    if ($res->fetchArray()) {
        return get_short_str($url, $db);
    }
    return $short;
}

function get_url($short_str, &$db)
{
    $statement = $db->prepare('SELECT url FROM links WHERE short = :short;');
    $statement->bindParam(':short', $short_str);
    $res = $statement->execute();
    $row = $res->fetchArray();
    return $row['url'];
}

function add_hit($url, &$db)
{
    $statement = $db->prepare('UPDATE links SET hits = hits + 1 WHERE url = :url;');
    $statement->bindParam(':url', $url, SQLITE3_TEXT);
    $statement->execute();
}

function shortlink($short_str)
{
    return 'http://' . HOST . '/' . $short_str;
}

function shortlink_html($short_str)
{
    $shortlink = shortlink($short_str);
    return "<a href=\"$shortlink\">$short_str</a>";
}
