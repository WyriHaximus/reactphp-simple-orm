CREATE TABLE users (
  id uuid PRIMARY KEY,
  name varchar(255),
  created timestamptz(6) NOT NULL,
  modified timestamptz(6) NOT NULL
);
