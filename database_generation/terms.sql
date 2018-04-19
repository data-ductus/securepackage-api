CREATE TABLE terms
(
  terms_id         VARCHAR(255)                                       NOT NULL
    PRIMARY KEY,
  agreement_id     VARCHAR(255)                                       NULL,
  author_account   VARCHAR(255)                                       NULL,
  status           ENUM ('INITIAL', 'PROPOSED', 'DENIED', 'ACCEPTED') NULL,
  price            INT                                                NULL,
  postage_time     TINYINT                                            NULL,
  accelerometer    FLOAT                                              NULL,
  pressure_low     FLOAT                                              NULL,
  pressure_high    FLOAT                                              NULL,
  humidity_low     FLOAT                                              NULL,
  humidity_high    FLOAT                                              NULL,
  temperature_low  FLOAT                                              NULL,
  temperature_high FLOAT                                              NULL,
  gps              TINYINT(1)                                         NULL,
  CONSTRAINT terms_terms_id_uindex
  UNIQUE (terms_id),
  CONSTRAINT FK_terms_agreement_id
  FOREIGN KEY (agreement_id) REFERENCES agreements (agreement_id)
)
  ENGINE = InnoDB;

CREATE INDEX FK_terms_agreement_id
  ON terms (agreement_id);
