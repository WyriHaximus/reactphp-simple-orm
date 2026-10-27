CREATE TABLE users (
  id uuid PRIMARY KEY,
  name varchar(255),
  created timestamptz(6) NOT NULL,
  modified timestamptz(6) NOT NULL
);

CREATE TABLE blog_posts (
  id uuid PRIMARY KEY,
  previous_blog_post_id uuid,
  next_blog_post_id uuid,
  author_id uuid NOT NULL,
  publisher_id uuid NOT NULL,
  title varchar(255),
  contents varchar(255),
  views int4 NOT NULL,
  created timestamptz(6) NOT NULL,
  modified timestamptz(6) NOT NULL
);

CREATE TABLE comments (
  id uuid PRIMARY KEY,
  author_id uuid NOT NULL,
  blog_post_id uuid NOT NULL,
  contents varchar(255),
  created timestamptz(6) NOT NULL,
  modified timestamptz(6) NOT NULL
);

CREATE TABLE logs (
  id uuid PRIMARY KEY,
  message varchar(255),
  created timestamptz(6) NOT NULL,
  modified timestamptz(6) NOT NULL
);
