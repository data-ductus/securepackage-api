CREATE TABLE sensors
(
  sensor_id   VARCHAR(255)                                 NOT NULL
    PRIMARY KEY,
  kolli_id    VARCHAR(255)                                 NULL,
  sensor_type ENUM ('GPS', 'TEMP', 'HUMID', 'ACC', 'PRES') NULL,
  CONSTRAINT sensors_sensor_id_uindex
  UNIQUE (sensor_id),
  CONSTRAINT FK_sensors_kolli_id
  FOREIGN KEY (kolli_id) REFERENCES logistics_simulation (kolli_id)
)
  ENGINE = InnoDB;

CREATE INDEX FK_sensors_kolli_id
  ON sensors (kolli_id);

