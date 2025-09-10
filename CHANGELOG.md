# Changelog

## ????
- Bump workerman to 4.1.*
- Bump phpstan to 1.10.*
- Bump pest to 2.30.*
- Add lint checking for PHP files
- [#3](https://github.com/josephscott/marmerine/pull/3) @joanhey - Call time() instead of microtime() for uptime tracking
- [#4](https://github.com/josephscott/marmerine/pull/4) @joanhey - Track Marmerine version as a constant
- [#5](https://github.com/josephscott/marmerine/pull/5) @joanhey - Use static anonymous functions, provides a small memory improvement
- [#8](https://github.com/josephscott/marmerine/pull/8) @joanhey - Set the workerman name
- [#7](https://github.com/josephscott/marmerine/pull/7) @joanhey - Remove int casting from verbose()
- [#9](https://github.com/josephscott/marmerine/pull/9) @joanhey - Test file naming style changes
- Set a busy timeout for the SQLite database, and always enable WAL
- [#12](https://github.com/josephscott/marmerine/pull/12) @joanhey - Send ERROR when a command is not supported
- [#16](https://github.com/josephscott/marmerine/pull/16) @joanhey - Add Github action 
- [#16](https://github.com/josephscott/marmerine/pull/16) @joanhey - Add ENV support for setting the port of the server to run tests against 
- [#6](https://github.com/josephscott/marmerine/pull/6) @joanhey - Use Workerman "onWorkerStart" to create the storage object


## 0.0.3 - 2022-05-21
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
- Bump workerman to 4.0.37
- Bump phpstan to 1.6.7
- Bump pest to 1.21.3


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
