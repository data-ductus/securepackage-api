CREATE TABLE images
(
  image_id VARCHAR(255) NOT NULL
    PRIMARY KEY,
  item_id  VARCHAR(255) NULL,
  image    VARCHAR(255) NULL,
  CONSTRAINT images_image_id_uindex
  UNIQUE (image_id),
  CONSTRAINT FK_image_item_id
  FOREIGN KEY (item_id) REFERENCES items (agreement_id)
)
  ENGINE = InnoDB;

CREATE INDEX FK_image_item_id
  ON images (item_id);