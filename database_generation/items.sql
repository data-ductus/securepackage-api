CREATE TABLE items
(
  agreement_id VARCHAR(255) NOT NULL
    PRIMARY KEY,
  title        TEXT         NULL,
  description  TEXT         NULL,
  CONSTRAINT FK_items_agreement_id
  FOREIGN KEY (agreement_id) REFERENCES agreements (agreement_id)
)
  ENGINE = InnoDB;
