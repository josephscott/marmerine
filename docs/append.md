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
