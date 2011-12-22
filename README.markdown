## Overview

Using this code you can make your application do automatic upgrades of database schema.

## Install

Add this options to your config file:

    migration.dir = APPLICATION_PATH "/../migrations"
    migration.schema_version = 1.0.0

`schema_version` could be any "PHP-standardized" version string

Add migration init to your bootstrap like in example.

Create `migrations` folder and put migration scripts in it.

## Usage

First you have create `install-ver.php` script that contains
sql commands that describe first version of your DB schema.
This script must return version number string.

Then programm checks whether it needs for upgrade.
And if it does, app starts looking for migration script that begin with current 
version number.
This process continues until the current version will not be equal to a version defined 
in config file.

## Syntax of migration files

Migration scripts are executed from within Migration class, so you have `$this` variable
that refers to that class object.
One method that you should use is 

    $this->_execSql('sql command');

Ofcourse you can call other methods but it is not recomended.

I know it's maybe bad idea to run scripts from within `Migration` class 
but I wanted reduce amount of files for this project.

**In the end of migration script you must return new version number.**