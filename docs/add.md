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
