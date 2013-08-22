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

Prepate:
- create hook:
  ```bash
  curl -i -u :user -d \
    '{"name": "web", "active": true, "events": ["push", "pull_request"], \
      "config": {"url": ":url", "content_type": "form"}}' \
    https://api.github.com/repos/:user/:repo/hooks
  ```

- create a tooken for the `config.php`:
  ```bash
  curl -i -u :user -d \
    '{"scopes": ["repo:status"], "note": ["GitHub Proxy"], "note_url": ["yourUrl"]}' \
    https://api.github.com/authorizations
  ```

- create a random secret for your client to connect to our scheduler `config.php` -> `$client_secret`:
  ```bash
  apg -m 20
  ```

License
-------

This code is licensed under the **GPLv3+**. See [LICENSE](LICENSE) for
more details.

