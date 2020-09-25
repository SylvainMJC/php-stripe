#!/usr/bin/env bash

if [ -n "$SQLITE_FILENAME" ]; then
  echo "Running: sqlite3 $SQLITE_FILENAME < $(dirname $BASH_SOURCE)/schema.sql"
  sqlite3 $SQLITE_FILENAME < $(dirname $BASH_SOURCE)/schema.sql
  exit 0
else
  echo "SQLITE_FILENAME is missing from your environment"
  exit 1
fi
