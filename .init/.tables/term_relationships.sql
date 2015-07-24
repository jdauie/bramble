CREATE TABLE term_relationships (
  object int NOT NULL,
  taxonomy int NOT NULL,
  PRIMARY KEY (object, taxonomy),
  FOREIGN KEY (object) REFERENCES objects(id) ON DELETE CASCADE,
  FOREIGN KEY (taxonomy) REFERENCES term_taxonomy(id) ON DELETE RESTRICT
)