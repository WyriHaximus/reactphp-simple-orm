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
