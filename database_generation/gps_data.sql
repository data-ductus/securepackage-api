CREATE TABLE gps_data
(
  sensor_id VARCHAR(255) NOT NULL
    PRIMARY KEY,
  latitude  FLOAT        NULL,
  longitude FLOAT        NULL,
  CONSTRAINT gps_data_sensor_id_uindex
  UNIQUE (sensor_id)
)
  ENGINE = InnoDB;
