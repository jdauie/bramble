CREATE TABLE terms (
  id int NOT NULL,
  name varchar(100),
  slug varchar(100),
  PRIMARY KEY (id),
  UNIQUE (name),
  UNIQUE (slug)
)