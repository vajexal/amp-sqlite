SQLite client for [amphp](https://amphp.org) using [parallel](https://amphp.org/parallel/)

[![Build Status](https://github.com/vajexal/amp-sqlite/workflows/Build/badge.svg)](https://github.com/vajexal/amp-sqlite/actions)

### Installation

```bash
composer require vajexal/amp-sqlite
```

### Usage

```php
<?php

declare(strict_types=1);

use Amp\Loop;
use Vajexal\AmpSQLite\SQLiteCommandResult;
use Vajexal\AmpSQLite\SQLiteConnection;
use Vajexal\AmpSQLite\SQLiteResultSet;
use Vajexal\AmpSQLite\SQLiteStatement;
use function Vajexal\AmpSQLite\connect;

require_once 'vendor/autoload.php';

Loop::run(function () {
    /** @var SQLiteConnection $connection */
    $connection = yield connect('database.sqlite');

    yield $connection->execute('drop table if exists users');
    yield $connection->execute('create table users (id integer primary key, name text not null)');
    yield $connection->execute('insert into users (name) values (:name)', [
        ':name' => 'Bob',
    ]);

    /** @var SQLiteResultSet $results */
    $results = yield $connection->query('select * from users');
    while (yield $results->advance()) {
        $row = $results->getCurrent();
        echo "Hello {$row['name']}\n";
    }

    /** @var SQLiteStatement $statement */
    $statement = yield $connection->prepare('update users set name = :name where id = 1');
    /** @var SQLiteCommandResult $result */
    $result = yield $statement->execute([
        ':name' => 'John',
    ]);
    echo "Updated {$result->getAffectedRowCount()} rows\n";
});
```

#### Transactions

```php
<?php

declare(strict_types=1);

use Amp\Loop;
use Vajexal\AmpSQLite\SQLiteConnection;
use Vajexal\AmpSQLite\SQLiteTransaction;
use function Vajexal\AmpSQLite\connect;

require_once 'vendor/autoload.php';

Loop::run(function () {
    /** @var SQLiteConnection $connection */
    $connection = yield connect('database.sqlite');

    yield $connection->execute('drop table if exists users');
    yield $connection->execute('create table users (id integer primary key, name text not null)');

    /** @var SQLiteTransaction $transaction */
    $transaction = yield $connection->beginTransaction();
    yield $transaction->execute('insert into users (name) values (:name)', [
        ':name' => 'Bob',
    ]);

    yield $transaction->createSavepoint('change_name');
    yield $transaction->execute('update users set name = :name where id = 1', [
        ':name' => 'John',
    ]);
    yield $transaction->releaseSavepoint('change_name');

    yield $transaction->commit();
});
```
