CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT
);

CREATE TABLE books (
  isbn TEXT NOT NULL UNIQUE,
  title TEXT NOT NULL
);

CREATE TABLE stock_entries (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  isbn TEXT NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 0,
  unit_price INTEGER NOT NULL,
  currency TEXT NOT NULL DEFAULT 'EUR',
  UNIQUE (isbn, currency, unit_price),
  FOREIGN KEY (isbn) REFERENCES books (isbn)
);

CREATE TABLE cart_entries (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  stock_entry_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 0,
  created_at INTEGER NOT NULL DEFAULT (strftime('%s','now')),
  UNIQUE (user_id, stock_entry_id),
  FOREIGN KEY (user_id) REFERENCES users (id),
  FOREIGN KEY (stock_entry_id) REFERENCES stock_entries (id)
);

CREATE TABLE orders (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  total_price INTEGER NOT NULL,
  currency TEXT NOT NULL DEFAULT 'EUR',
  state TEXT NOT NULL,
  created_at INTEGER NOT NULL DEFAULT (strftime('%s','now')),
  FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE order_lines (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id INTEGER NOT NULL,
  isbn TEXT NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 0,
  unit_price INTEGER NOT NULL,
  currency TEXT NOT NULL DEFAULT 'EUR',
  stock_entry_id INTEGER NOT NULL,
  UNIQUE (order_id, isbn, currency, unit_price),
  FOREIGN KEY (order_id) REFERENCES orders (id),
  FOREIGN KEY (isbn) REFERENCES books (isbn),
  FOREIGN KEY (stock_entry_id) REFERENCES stock_entries (id)
);
