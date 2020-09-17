# Phurlite

Super Simple URL Shortener  
PHP + URL + SQLite = Phurlite  
2012 - Dustin Davis  
https://dustindavis.me  
License: Public Domain

Goals of this script:

- Simple
- 1 Self-contained PHP file + .htaccess file
- SQLite for storage
- API for use with Tweetbot

## Requirements

- PHP 7+
- SQLite3+
- Apache (.htaccess)

## How it works

This is very simple. There are a few GET parameters you can pass in.

### `?u=https://example.com`

This will create a new short link and return the short-link URL

### `?u=https://example.com&custom=example`

Similar to above, but this will allow you to set a custom short-link. If the custom short-link is in use, you will get a 409 error code.

### `?list`

This will list all the available short-links available in a simple table.

See blog post: https://dustindavis.me/blog/phurlite-simple-php-sqlite-url-shortener
