CREATE TABLE sensor_data
(
  sensor_id        VARCHAR(255) NULL,
  output           FLOAT        NULL,
  server_timestamp DATETIME     NULL,
  CONSTRAINT FK_sensor_data_sensor_id
  FOREIGN KEY (sensor_id) REFERENCES sensors (sensor_id)
)
  ENGINE = InnoDB;

CREATE INDEX FK_sensor_data_sensor_id
  ON sensor_data (sensor_id);