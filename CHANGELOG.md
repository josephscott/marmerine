# Changelog


## ????
- Implement the stats command ( only a subset of data is available )
- Fix: do not try to repeatedly enable SQLite WAL
- Implement the cas command
- New test: cas command
- New test: gets command
- New test: stats command
- CLI option: --port
- CLI option: --verbose
- SQLite schema update: added cas field
- SQLite: enable WAL
- Bump workerman to 4.0.33


## 0.0.2 - 2022-03-15
- Rename the test files
- Implement the version command
- Implement the replace command
- Implement the append command
- Implement the prepend command
- Implement the touch command
- Implement the gets command
- Implement the incr command
- Implement the decr command
- New test: version command
- New test: replace command
- New test: append command
- New test: prepend command
- New test: touch command
- New test: incr command
- New test: decr command
- Bump workerman to 4.0.30
- Bump pest to 1.21.2


## 0.0.1 - 2022-02-01
- Start protocol information details
- Start set of PHP based tests against a Memcached server
- SQLite based storage backend
- Command support for : add
- Command support for : set
- Command support for : get
- Command support for : quit
- Command support for : flush_all
- Command support for : delete
