# Marmerine

This is alternative implementation of Memcached.

## Storage Commands

### `set`

- **Supported:** No &#9940;
- **Format:** `set <key> <flags> <exptime> <length> [noreply]\r\n<data>\r\n`
- **Success Response:** `STORED\r\n`
- **Error Response:** `CLIENT_ERROR [error]\r\nERROR\r\n`

__Examples__
```shell
$ printf "set thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
```

```shell
$ printf "set thing 0 300 3\r\nabc123XYZ\r\n" | nc localhost 11211
CLIENT_ERROR bad data chunk
ERROR
```

__Description__

This will store the value of the given key, even if it already exists.

---

### `add`

- **Supported:** No &#9940;
- **Format:** `add <key> <flags> <exptime> <length> [noreply]\r\n<data>\r\n`
- **Success Response:** `STORED\r\n`
- **Failure Response:** `NOT_STORED\r\n`
- **Error Response:** `CLIENT_ERROR [error]\r\nERROR\r\n`

__Examples__
```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
```

```shell
$ printf "add thing 0 300 3\r\nabc123XYZ\r\n" | nc localhost 11211
CLIENT_ERROR bad data chunk
ERROR
```

```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
NOT_STORED
```

__Description__

This will store the value of the given key, only if it does not already exist.


## Memcached Resources

- [Memcached](https://github.com/memcached/memcached), the original.
- [Memcached Protocol.txt](https://github.com/memcached/memcached/blob/master/doc/protocol.txt), general description of the protocols.
- [Twemproxy](https://github.com/twitter/twemproxy), a proxy for Memcached and Redis from Twitter.
- [Twemproxy Memcached Notes](https://github.com/twitter/twemproxy/blob/master/notes/memcache.md), helpful list of of supported Memcached comands in Twemproxy.
- [MySQL Memcached TCP Text Protocol](https://docs.oracle.com/cd/E17952_01/mysql-5.6-en/ha-memcached-interfaces-protocol.html), commands outlined for the MySQL implementation.
- [Memcached Cheat Sheet](https://lzone.de/cheat-sheet/memcached), a list of other Memcached resources and examples.
