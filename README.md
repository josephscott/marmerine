# Marmerine

This is an alternate Memcached implementation.

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

### `replace`

### `append`

### `prepend`

### `cas`

### `touch`

## Retrieve Commands 

### `get`

- **Supported:** No &#9940;
- **Format:** `get <key> [key2 key3 ... keyn]\r\n`
- **Found Response:** `VALUE <key> <flags> <length>\r\n<data>\r\nEND\r\n`
- **Not Found Response:** `END\r\n`

__Examples__
```shell
$ printf "set thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "get thing\r\n" | nc localhost 11211
VALUE thing 0 3
abc
END
```

```shell
$ printf "get thing\r\n" | nc localhost 11211
END
```

```shell
$ printf "set thing1 0 300 4\r\n1abc\r\n" | nc localhost 11211
STORED
$ printf "set thing2 0 300 4\r\n2abc\r\n" | nc localhost 11211
STORED
$ printf "set thing3 0 300 4\r\n3abc\r\n" | nc localhost 11211
STORED
$ printf "get thing thing1 thing2 thing3\r\n" | nc localhost 11211
VALUE thing1 0 4
1abc
VALUE thing2 0 4
2abc
VALUE thing3 0 4
3abc
END
```

__Description__

Gets the value for the given key.  When the key does not exist, a response with just `END\r\n` is given.  When multiple keys are provided, only ones that exist will be returned.

### `gets`

### `gat`

### `gats`

## Delete Commands

### `delete`

- **Supported:** No &#9940;
- **Format:** `delete <key> [noreply]\r\n`
- **Success Response:** `DELETED\r\n`
- **Not Found Response:** `NOT_FOUND\r\n`

__Examples__
```shell
$ printf "set thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "delete thing\r\n" | nc localhost 11211
DELETED
```

```shell
$ printf "delete thing\r\n" | nc localhost 11211
NOT_FOUND
```

__Description__

Delete the given key.  If the key does not exist then `NOT_FOUND` is returned.

### `flush_all`

- **Supported:** No &#9940;
- **Format:** `flush_all\r\n`
- **Every Response:** `OK\r\n`

__Examples__
```shell
$ printf "flush_all\r\n" | nc localhost 11211
OK
```

```shell
$ printf "flush_all\r\n" | nc localhost 11211
OK
$ printf "flush_all\r\n" | nc localhost 11211
OK
```

__Description__

Delete all of the stored keys.  There is no fail or error condition, it always returns `OK`.

## Arithmetic Commands

### `incr`

### `decr`

## Miscellaneous Commands

### `quit`

### `version`

### `verbosity`

### `stats`

## Memcached Resources

- [Memcached](https://github.com/memcached/memcached), the original.
- [Memcached Protocol.txt](https://github.com/memcached/memcached/blob/master/doc/protocol.txt), general description of the protocols.
- [Twemproxy](https://github.com/twitter/twemproxy), a proxy for Memcached and Redis from Twitter.
- [Twemproxy Memcached Notes](https://github.com/twitter/twemproxy/blob/master/notes/memcache.md), helpful list of of supported Memcached comands in Twemproxy.
- [MySQL Memcached TCP Text Protocol](https://docs.oracle.com/cd/E17952_01/mysql-5.6-en/ha-memcached-interfaces-protocol.html), commands outlined for the MySQL implementation.
- [Memcached Cheat Sheet](https://lzone.de/cheat-sheet/memcached), a list of other Memcached resources and examples.
- [libmemcached-awesome](https://github.com/awesomized/libmemcached), an updated version of the original [libmemcached](https://libmemcached.org/libMemcached.html).
- [memc.rs](https://www.memc.rs/intro), Memcached clone written in Rust, compatible with the binary protocol.
- [memtier_benchmark](https://github.com/RedisLabs/memtier_benchmark), a Memcached ( and Redis ) benchmarking tool.
- [Expiration Times](https://www.php.net/manual/en/memcached.expiration.php), good description of how Memcached treats the expiration times.
