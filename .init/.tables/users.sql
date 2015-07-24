CREATE TABLE users (
  id int NOT NULL,
  name varchar(60) NOT NULL,
  email varchar(100),
  password varchar(100),
  display varchar(60),
  PRIMARY KEY (id),
  UNIQUE (name)
)