# Marmerine

## Commands

### Storage

| Command | Supported | Format | Valid Response | Error |
| ------- | --------- | ------ | -------- | ----- |
| **set** | No | `set <key> <flags> <exptime> <length> [noreply]\r\n<data>\r\n` | `STORED\r\n` | `CLIENT_ERROR [error]\r\nERROR\r\n` |

## Memcached Resources

- [Memcached](https://github.com/memcached/memcached), the original.
- [Memcached Protocol.txt](https://github.com/memcached/memcached/blob/master/doc/protocol.txt), general description of the protocols.
- [Twemproxy](https://github.com/twitter/twemproxy), a proxy for Memcached and Redis from Twitter.
- [Twemproxy Memcached Notes](https://github.com/twitter/twemproxy/blob/master/notes/memcache.md), helpful list of of supported Memcached comands in Twemproxy.
- [MySQL Memcached TCP Text Protocol](https://docs.oracle.com/cd/E17952_01/mysql-5.6-en/ha-memcached-interfaces-protocol.html), commands outlined for the MySQL implementation.
- [Memcached Cheat Sheet](https://lzone.de/cheat-sheet/memcached), a list of other Memcached resources and examples.
