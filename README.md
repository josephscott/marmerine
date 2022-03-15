# Marmerine

This is an alternate Memcached implementation.

```
git clone https://github.com/josephscott/marmerine.git
composer install
php server.php start
```

## Storage Commands

### `set`

- **Supported:** Yes &#9989;
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

- **Supported:** Yes &#9989;
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

- **Supported:** Yes &#9989;
- **Format:** `replace <key> <flags> <exptime> <length> [noreply]\r\n<data>\r\n`
- **Success Response:** `STORED\r\n`
- **Error Response:** `NOT_STORED\r\n`

__Examples__
```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "replace thing 0 300 12\r\nabc-REPLACED\r\n" | nc localhost 11211
STORED
$ printf "get thing\r\n" | nc localhost 11211
VALUE thing 0 12
abc-REPLACED
END
```

```shell
$ printf "replace thing 0 300 3\r\nabc\r\n" | nc localhost 11211
NOT_STORED
```

__Description__

This will replace the value of the given key.

### `append`

- **Supported:** Yes &#9989;
- **Format:** `append <key> <flags> <exptime> <length> [noreply]\r\n<data>\r\n`
- **Success Response:** `STORED\r\n`
- **Error Response:** `NOT_STORED\r\n`

__Examples__
```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "append thing 0 300 6\r\n-AFTER\r\n" | nc localhost 11211
STORED
$ printf "get thing\r\n" | nc localhost 11211
VALUE thing 0 9
abc-AFTER
END
```

```shell
$ printf "append thing 0 300 6\r\n-AFTER\r\n" | nc localhost 11211
NOT_STORED
```

__Description__
This will append a string to the value of an existing key.

### `prepend`

- **Supported:** Yes &#9989;
- **Format:** `prepend <key> <flags> <exptime> <length> [noreply]\r\n<data>\r\n`
- **Success Response:** `STORED\r\n`
- **Error Response:** `NOT_STORED\r\n`

__EXAMPLES__
```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "prepend thing 0 300 7\r\nBEFORE-\r\n" | nc localhost 11211
STORED
$ printf "get thing\r\n" | nc localhost 11211
VALUE thing 0 10
BEFORE-abc
END
```

__Description__
This will prepend a string to the value of an existing key.

### `cas`

### `touch`

- **Supported:** Yes &#9989;
- **Format:** `touch <key> <expiry> [noreply]\r\n`
- **Success Response:** `TOUCHED\r\n`
- **Error Resposne:** `NOT_FOUND\r\n`

__Examples__
```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "touch thing 1800\r\n" | nc localhost 11211
TOUCHED
```

__Description__

Update the expiration time of an existing key.

## Retrieve Commands 

### `get`

- **Supported:** Yes &#9989;
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

This is the same as the `get` command, and is implemented as an alias to `get`.

### `gat`

### `gats`

## Delete Commands

### `delete`

- **Supported:** Yes &#9989;
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

- **Supported:** Yes &#9989;
- **Format:** `flush_all [delay] [noreply]\r\n`
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

- **Supported:** Yes &#9989;
- **Format:** `incr <key> <value> [noreply]\r\n`
- **Success Response:** `<incremented value>\r\n`
- **Error Response:** `CLIENT_ERROR cannot increment or decrement non-numeric value`

__Examples__
```shell
$ printf "add thing 0 300 1\r\n1\r\n" | nc localhost 11211
STORED
$ printf "incr thing 1\r\n" | nc localhost 11211
2
$ printf "incr thing 1\r\n" | nc localhost 11211
3
```

```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "incr thing 1\r\n" | nc localhost 11211
CLIENT_ERROR cannot increment or decrement non-numeric value
```

__Description__

This only works on integer values.

### `decr`

- **Supported:** Yes &#9989;
- **Format:** `decr <key> <value> [noreply]\r\n`
- **Success Response:** `<decremented value>\r\n`
- **Error Response:** `CLIENT_ERROR cannot increment or decrement non-numeric value`

__Examples__
```shell
$ printf "add thing 0 300 1\r\n9\r\n" | nc localhost 11211
STORED
$ printf "decr thing 1\r\n" | nc localhost 11211
8
$ printf "decr thing 1\r\n" | nc localhost 11211
7
```

```shell
$ printf "add thing 0 300 3\r\nabc\r\n" | nc localhost 11211
STORED
$ printf "decr thing 1\r\n" | nc localhost 11211
CLIENT_ERROR cannot increment or decrement non-numeric value
```

__Description__

This only works on integer values.

## Miscellaneous Commands

### `quit`

- **Supported:** Yes &#9989;
- **Format:** `quit\r\n`
- **Every Response:** ( None )

__Examples__
```shell
$ printf "quit\r\n" | nc localhost 11211
```

__Description__

This closes the connection to the server.  It does not return anything.

### `version`

- **Supported:** Yes &#9989;
- **Format:** `version\r\n`
- **Every Response:** `VERSION <version>\r\n`

__Examples__
```shell
$ printf "version\r\n" | nc localhost 11211
VERSION 1.6.12
```

__Description__

Get the version number from the server.

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
