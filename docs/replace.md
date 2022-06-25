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
