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
- `php5-sqlite` on a apache2 webserver (SQLite3).
- `php5-curl`

Recommented
- `sqlite`


License
-------

This code is licensed under the **GPLv3+**. See [LICENSE](LICENSE) for
more details.

