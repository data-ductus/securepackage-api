-- auto-generated definition
CREATE TABLE logistics_simulation
(
  kolli_id     VARCHAR(255)                NOT NULL
    PRIMARY KEY,
  agreement_id VARCHAR(255)                NULL,
  cost         INT                         NULL,
  weight       FLOAT                       NULL,
  direction    ENUM ('TRANSFER', 'RETURN') NULL,
  CONSTRAINT logistics_simulation_kolli_id_uindex
  UNIQUE (kolli_id),
  CONSTRAINT FK_logisticssimulation_agreement_id
  FOREIGN KEY (agreement_id) REFERENCES agreements (agreement_id)
)
  ENGINE = InnoDB;

CREATE INDEX FK_logisticssimulation_agreement_id
  ON logistics_simulation (agreement_id);