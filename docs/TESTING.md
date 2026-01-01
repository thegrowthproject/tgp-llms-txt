# Testing Guide

This document explains how to run and write tests for the TGP LLMs.txt plugin.

## Overview

The plugin uses two testing frameworks:

| Framework | Language | Purpose |
|-----------|----------|---------|
| PHPUnit   | PHP      | Server-side logic, shared helpers, block rendering |
| Jest      | JS       | Interactivity API stores, client-side behavior |

## Quick Start

```bash
# Install dependencies
composer install
npm install

# Run all tests
composer test
npm test

# Run with coverage
composer test:coverage
npm run test:coverage
```

## PHP Testing (PHPUnit)

### Setup

PHPUnit is configured via `phpunit.xml.dist`. Tests use [Brain Monkey](https://brain-wp.github.io/BrainMonkey/) for mocking WordPress functions without loading WordPress core.

### Directory Structure

```
tests/php/
├── bootstrap.php           # Test bootstrap (loads Brain Monkey)
└── includes/
    ├── ButtonBlockRendererTest.php
    └── PillBlockRendererTest.php
```

### Running Tests

```bash
# Run all PHP tests
composer test

# Run specific test file
vendor/bin/phpunit tests/php/includes/ButtonBlockRendererTest.php

# Run specific test method
vendor/bin/phpunit --filter testGetStyleAttributesExtractsBlockSupports

# Run with coverage report
composer test:coverage
# Coverage report generated in coverage/ directory
```

### Writing PHP Tests

Tests extend `PHPUnit\Framework\TestCase` and use Brain Monkey for WordPress function mocks:

```php
<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class MyBlockTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Mock WordPress functions as needed
        Functions\when( 'esc_attr' )->returnArg();
        Functions\when( 'esc_html' )->returnArg();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testSomething(): void {
        // Your test code
        $this->assertEquals( 'expected', 'expected' );
    }
}
```

### What to Test (PHP)

- **Shared Helpers** (`includes/class-*-renderer.php`)
  - Style attribute extraction
  - Class name building
  - Color resolution
  - Inline style generation

- **Block Registration** (future)
  - Block attributes
  - Render callbacks

## JavaScript Testing (Jest)

### Setup

Jest is configured in `package.json` with a custom mock for `@wordpress/interactivity`.

### Directory Structure

```
tests/js/
├── __mocks__/
│   └── interactivity.js    # Mock for @wordpress/interactivity
├── setup.js                # Jest setup (DOM matchers, fetch mock)
└── blocks/
    ├── copy-button.test.js
    └── blog-filters.test.js
```

### Running Tests

```bash
# Run all JS tests
npm test

# Watch mode (re-runs on file changes)
npm run test:watch

# Run specific test file
npm test -- tests/js/blocks/copy-button.test.js

# Run with coverage
npm run test:coverage
```

### Writing JS Tests

Tests use the Interactivity API mock which provides helpers for setting context and state:

```javascript
import { store, getContext, setMockContext, setMockState, getStore } from '@wordpress/interactivity';

describe( 'my-store', () => {
    beforeEach( () => {
        // Set up global state (from PHP wp_interactivity_state)
        setMockState( {
            posts: [],
            categories: [],
        } );

        // Register your store
        store( 'my-namespace/my-store', {
            state: {
                get computedValue() {
                    const ctx = getContext();
                    return ctx.someValue * 2;
                },
            },
            actions: {
                doSomething() {
                    const ctx = getContext();
                    ctx.someValue = 42;
                },
            },
        } );
    } );

    it( 'computes value correctly', () => {
        setMockContext( { someValue: 5 } );

        const myStore = getStore( 'my-namespace/my-store' );
        expect( myStore.state.computedValue ).toBe( 10 );
    } );

    it( 'action modifies context', () => {
        const ctx = { someValue: 0 };
        setMockContext( ctx );

        const myStore = getStore( 'my-namespace/my-store' );
        myStore.actions.doSomething();

        expect( ctx.someValue ).toBe( 42 );
    } );
} );
```

### Mock API Reference

The `@wordpress/interactivity` mock provides:

| Function | Description |
|----------|-------------|
| `store( namespace, definition )` | Register a store |
| `getContext()` | Get current mock context |
| `setMockContext( ctx )` | Set context for next test |
| `setMockState( state )` | Set global state for next test |
| `getStore( namespace )` | Get registered store |
| `resetMocks()` | Reset all mocks (called in beforeEach) |

### What to Test (JS)

- **State Getters**
  - Computed values based on context
  - Conditional logic (active states, visibility)
  - Text formatting (labels, counts)

- **Actions**
  - Context mutations
  - State changes
  - Async operations (use generators for fetch)

## Test Patterns

### Testing State Getters

```javascript
describe( 'state.isActive', () => {
    it( 'returns true when condition met', () => {
        setMockContext( { status: 'active' } );
        expect( getStore( 'my/store' ).state.isActive ).toBe( true );
    } );

    it( 'returns false when condition not met', () => {
        setMockContext( { status: 'inactive' } );
        expect( getStore( 'my/store' ).state.isActive ).toBe( false );
    } );
} );
```

### Testing Actions That Modify Context

```javascript
describe( 'actions.toggle', () => {
    it( 'toggles value', () => {
        const ctx = { enabled: false };
        setMockContext( ctx );

        getStore( 'my/store' ).actions.toggle();

        expect( ctx.enabled ).toBe( true );
    } );
} );
```

### Testing Generator Actions (Async)

```javascript
describe( 'actions.fetchData', () => {
    it( 'fetches and updates context', async () => {
        global.fetch = jest.fn().mockResolvedValue( {
            ok: true,
            json: () => Promise.resolve( { data: 'test' } ),
        } );

        const ctx = { data: null, loading: false };
        setMockContext( ctx );

        // Generator actions need to be iterated
        const generator = getStore( 'my/store' ).actions.fetchData();
        await runGenerator( generator );

        expect( ctx.data ).toBe( 'test' );
    } );
} );

// Helper to run generator actions
async function runGenerator( gen ) {
    let result = gen.next();
    while ( ! result.done ) {
        const value = await result.value;
        result = gen.next( value );
    }
    return result.value;
}
```

## Coverage

Both test runners generate coverage reports:

- **PHPUnit**: `coverage/` directory (HTML report)
- **Jest**: `coverage/` directory (HTML report)

View the HTML reports in a browser to see line-by-line coverage.

## CI Integration

Tests run automatically on GitHub Actions. See `.github/workflows/lint.yml` for the CI configuration.

To add test jobs:

```yaml
jobs:
  test-php:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install
      - run: composer test

  test-js:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
      - run: npm ci
      - run: npm test
```

## Troubleshooting

### PHPUnit "Class not found" errors

Ensure autoload is up to date:
```bash
composer dump-autoload
```

### Jest "Cannot find module" errors

Check that the mock path in `package.json` is correct:
```json
"moduleNameMapper": {
    "^@wordpress/interactivity$": "<rootDir>/tests/js/__mocks__/interactivity.js"
}
```

### Brain Monkey "Function not mocked" warnings

Add the missing function mock in your test's `setUp()`:
```php
Functions\when( 'missing_function' )->returnArg();
```
