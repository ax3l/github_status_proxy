github_status_proxy
===================

A php proxy to fetch github status changes.

This collection of scripts is used to work as a **receiver** for
*GitHub's POST requests* ( **push hooks** ) and as a scheduler for
*behind-firewall integration clients*.

It stores the received events in a *round-robin* sqlite3 database.


Install
-------

Requires
- `php5-sqlite` (SQLite3)
- `php5-curl`

Recommented
- `sqlite`

Prepare:
- create hook:
  ```bash
  curl -i -u :user -d \
    '{"name": "web", "active": true, "events": ["push", "pull_request"], \
      "config": {"url": ":url", "content_type": "form"}}' \
    https://api.github.com/repos/:user/:repo/hooks
  ```

- create a tooken for the `config.php` -> `access_token`:
  ```bash
  curl -i -u :user -d \
    '{"scopes": ["repo:status"], "note": ["GitHub Proxy"], "note_url": ["yourUrl"]}' \
    https://api.github.com/authorizations
  ```

- create **two** random secrets:
  - for your test client to connect to your proxy/scheduler `config.php` -> `$client_secret`
  - for the salt for the keys for unauthorized users `config.php` -> `statusSalt`

  ```bash
  apg -m 20
  ```

License
-------

This code is licensed under the **GPLv3+**. See [LICENSE](LICENSE) for
more details.


To do
-----

- check where to set the status for a pull-request in the GitHub API
- create entries for each test client in `test` table as soon as an event
  is created OR define "no entry" = "has to be tested"
- Round Robin
    http://www.mail-archive.com/sqlite-users@sqlite.org/msg60752.html
- put db in a password protected sub dir (and check that)
- add Syntax Highlighting for user side views
  - [install](http://alexgorbatchev.com/SyntaxHighlighter/manual/installation.html)
    [config](http://alexgorbatchev.com/SyntaxHighlighter/manual/configuration/)
  - output between `<pre ...><?php htmlentities($text, ENT_COMPAT, 'UTF-8'); ?> </pre>`
  - write brush for [ANSI escape codes](http://en.wikipedia.org/wiki/ANSI_escape_code)
    [manual](http://alexgorbatchev.com/SyntaxHighlighter/manual/brushes/custom.html)
    [css example](https://github.com/alexgorbatchev/SyntaxHighlighter/blob/master/src/js/shBrushCss.js)
    [xml example](https://github.com/alexgorbatchev/SyntaxHighlighter/blob/master/src/js/shBrushXml.js)
    [regex lib](https://github.com/alexgorbatchev/SyntaxHighlighter/blob/master/src/js/shCore.js#L103)
  - some kind of code/line-folding support?
