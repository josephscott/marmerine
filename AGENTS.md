# Marmerine - Agent Context Guide

## Project Overview

Marmerine is an alternate implementation of the Memcached server written in PHP using the Workerman async library. It provides a compatible text protocol implementation with SQLite3 as the storage backend. The project aims to be a lightweight, feature-complete Memcached server that supports most of the standard commands.

**Key Technologies:**
- PHP 8.1+ (strict types, named arguments, type hints)
- Workerman 5.1+ (async TCP server framework)
- SQLite3 (data persistence with WAL mode)
- Pest (testing framework)

## Project Architecture
- server.php - the main server file
- lib/protocols/memcached-text.php - the protocol layer
- lib/storage/sqlite.php - the storage backend
- tests/ - Pest test files

## Code Conventions & Patterns

### PHP Style
- **Strict typing:** All files use `declare(strict_types = 1)`
- **Naming:** snake_case for variables/functions, PascalCase for classes
- **Arguments:** Use named arguments for method calls
- **Error handling:** Return false/error codes rather than exceptions
- **Type hints:** All function parameters and return types are typed

### Workerman Integration
- Custom protocol class handles parsing
- Connection objects passed to handlers
- Stats tracked globally across workers
- Graceful handling of connection lifecycle

### Running Tests
```bash
make tests        # Full test suite with server lifecycle
make server-start # Start server manually
make server-stop  # Stop server manually
vendor/bin/pest   # Run tests against existing server
```

## Development Guidelines

### Adding New Commands
1. Add parsing logic in `Memcached_Text::decode()`
2. Add command handling in `server.php` switch statement
3. Implement storage method in `Memcached_Storage` class
4. Add corresponding test file in `tests/`
5. Update documentation if needed

### Protocol Parsing Pattern
- Commands with data payloads need special handling in `input()`
- Parse command parts in `decode()`
- Handle `noreply` flag consistently
- Return structured object with command details

### Error Handling
- Storage failures return `false`
- Protocol errors send `ERROR` or `CLIENT_ERROR`
- Network errors handled by Workerman
- No exceptions - use return values

### Statistics Tracking
- Use `bump_stat()` for command counters
- Track both `_hits` and `_misses` for each command
- Global `$stats` array persists across requests
- Stats available via `stats` command

## Debugging Tips

### Verbose Mode
Start server with `--verbose=1` to see:
- All incoming commands
- SQL queries being executed
- Connection events

### Common Issues
- **Port conflicts:** Check if port 11211 is available
- **Database locks:** SQLite busy timeout set to 1000ms
- **Memory usage:** Each worker process has separate SQLite connection
- **Test failures:** Ensure server is running before tests

### Key Files for Debugging
- `workerman.log` - Server startup/shutdown logs
- `data/marmerine.db` - SQLite database file
- Protocol parsing happens in real-time - no persistent logs

## Performance Considerations

### Workerman Configuration
- 4 worker processes by default
- Each process handles connections independently

### SQLite Optimizations
- WAL mode enabled for better concurrency
- Prepared statements cached automatically
- Indexes on frequently queried columns
- Automatic cleanup of expired keys