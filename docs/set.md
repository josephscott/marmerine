# `set`

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
