CREATE TABLE term_taxonomy (
  id int NOT NULL,
  term int NOT NULL,
  taxonomy varchar(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (term, taxonomy)
)