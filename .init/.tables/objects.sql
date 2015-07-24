CREATE TABLE objects (
  id int NOT NULL,
  parent int,
  author int NOT NULL,
  time datetime NOT NULL,
  title varchar(200) NOT NULL,
  slug varchar(200) NOT NULL,
  content text NOT NULL,
  type varchar(100) NOT NULL,
  menu_order tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  FOREIGN KEY (parent) REFERENCES objects (id) ON DELETE SET NULL,
  FOREIGN KEY (author) REFERENCES users (id) ON DELETE RESTRICT
)