SQL Helper for PHP and MySQL
============================

The purpose of this small piece of code is to have a simple sql-helper that
makes writing queries faster, simpler and more secure.

It is convention-based, and expects the objects to map directly to the database.
It can automatically append prefix to tables and will escape all values to guard
against SQL injection.

The objects returned from the various query functions are chainable, and
designed to resemble the sql-syntax. This means that it should be easy to see
what is going on even when looking at the code some time later, whilst still
retaining some higher-level functions.

Running the tests
-----------------

To run the tests, first execute `install-tests` to get the dependencies installed.

Then simply execute `runTests.php`.


-----

__NOTE:__

There are certain conventions used in this document. They all have their own
chapter, which holds more information.

-	`$helper` is the instance of the `\sql\SQLHelper` class which is the
	backbone of the library.
-	When creating queries, the builder is called `$query`. It is an instance of
	any subclasses of `\sql\builders\QueryBuilder`.
- When the `$query` is executed, it returns `$results`. This is an instance of
	`\sql\Results`.
-	`$table` refers to a single table. This is given as a string.
-	`$tables` refers to an array of tables.
-	`$values` refers to an array of values, given in a key-value order. Key
	corresponds to the column-name and value is usually a string.

	The value is escaped, unless it is an instance of `\sql\LiteralValue`,
	in which case it will be executed as-is. This is the way to use mysql
	functions.


Connecting to the database
==========================

	$helper =
		new \sql\SQLHelper(array(
			  'user'=> 'username'
			, 'password'=> 'password'
			, 'db'=> 'database name'
		
			// defaults to localhost
			, 'host'=> 'localhost'
		))

	// Calling setPrefix will ensure that all tables are prefixed.
	// This makes it simple to use the same codebase multiple times on the
	// same server.
		->setPrefix('abc')

	// Creating the actual connection
		->connect();


Handling Results
================

All methods interacting with the underlying database except where those
explicitly saying otherwise returns an instance of `\sql\Results`.

`Results` supports the most common functionality of results:

-	`getLastId()` - Returns the last automatically created ID. This refers to
	the last column with AUTO_INCREMENT that the server ran.
-	`length()` - The number of rows in question. For a `SELECT`-statement, it
	is the number of returned row. For data-altering statements, it is the
	number of affected rows.
-	`nextRow()` - Returns the next row of the fetched data.
-	`toArray()` - Converts the selected rows into an array and returns that.
	`Results::nextRow()` can still be used to fetch individual rows.


Running queries
===============

There is not, and there never will be, functions for doing all that can be done
with a database. The goal of this library is not to wrap everything that modern
databases can, but to ease day-to-day operations.

But neither is it the purpose of this library to stand in the way of the
programmer. It is actually the exact opposite; to empower the programmer and
hide the more ugly parts of PHP/MySQL by removing the grim reality of static SQL
strings and automatically escape values.

And in order to not stand in the way, the helper exposes the raw connection and
allows for direct queries to be run:

	// $helper is created and connected above this statement
	$query = 'SELECT * FROM abc WHERE id < 10';

	$results = $helper->query($query)->exec();

	while($row = $results->nextRow()) {
		// do something with row
	}

But there are some high-level versions of the 4 basic query-types (SELECT,
UPDATE, INSERT, DELETE) which makes it faster to do the basic types.

They have certain things in common:

-	The query is started with calling a function on `$helper`, which corresponds
	to the name of the type (ie. `$helper->select()`). It returns a
	query-instance (henceforth called `$query`).
-	Queries are not executed until `$query->exec()` is called, at which point it
	will return a `Results` object.
-	The current query-string can be fetched at any point during the
	build-process, by calling `$query->toString()`.
-	Table-names will always be prefixed according to the info given to `$helper`.
-	All methods can be called multiple times. Only the last call has any effect.


Selecting
=========

	// The following two are equivalent
	$results = $helper->select('a AS A, b AS B')
	$results = $helper->select(array('a'=> 'A', 'b'=> 'B'))

	// There are two ways to denote the tables
		->from($table)
		->from(array($tables))

	// The following calls are optional, and can be made in any order
		->where('a=? AND b=2', 'A')
		->order('b DESC')

	// Ending the query and returning the result
		->exec();


Updating
========

	$result = $helper
		->update($table)
		->set(array('key'=> 'value', 'second_key'=> 'second value'))
		->where('a=2 AND b=?', $someValue)

		->exec();


Inserting
=========

	$results = $helper
		->insert($values)
		->into($table)

		->exec();


Deleting
========

	$results = $helper
		->delete()
		->from($tables)
		->where('a=2 AND b=?', $someValue)

		->exec();
