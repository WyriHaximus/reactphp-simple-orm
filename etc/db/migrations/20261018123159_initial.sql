-- Create "blog_posts" table
CREATE TABLE "blog_posts" (
  "id" uuid NOT NULL,
  "previous_blog_post_id" uuid NULL,
  "next_blog_post_id" uuid NULL,
  "author_id" uuid NOT NULL,
  "publisher_id" uuid NOT NULL,
  "title" character varying(255) NULL,
  "contents" character varying(255) NULL,
  "views" integer NOT NULL,
  "created" timestamptz NOT NULL,
  "modified" timestamptz NOT NULL,
  PRIMARY KEY ("id")
);
-- Create "comments" table
CREATE TABLE "comments" (
  "id" uuid NOT NULL,
  "author_id" uuid NOT NULL,
  "blog_post_id" uuid NOT NULL,
  "contents" character varying(255) NULL,
  "created" timestamptz NOT NULL,
  "modified" timestamptz NOT NULL,
  PRIMARY KEY ("id")
);
-- Create "logs" table
CREATE TABLE "logs" (
  "id" uuid NOT NULL,
  "message" character varying(255) NULL,
  "created" timestamptz NOT NULL,
  "modified" timestamptz NOT NULL,
  PRIMARY KEY ("id")
);
-- Create "users" table
CREATE TABLE "users" (
  "id" uuid NOT NULL,
  "name" character varying(255) NULL,
  "created" timestamptz NOT NULL,
  "modified" timestamptz NOT NULL,
  PRIMARY KEY ("id")
);
