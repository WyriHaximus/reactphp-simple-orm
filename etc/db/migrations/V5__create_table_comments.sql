CREATE TABLE comments (
  id uuid PRIMARY KEY,
  author_id uuid NOT NULL,
  blog_post_id uuid NOT NULL,
  contents varchar(255),
  created timestamptz(6) NOT NULL,
  modified timestamptz(6) NOT NULL
);
