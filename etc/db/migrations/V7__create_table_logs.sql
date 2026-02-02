CREATE TABLE logs (
  id uuid PRIMARY KEY,
  message varchar(255),
  created timestamptz(6) NOT NULL,
  modified timestamptz(6) NOT NULL
);
