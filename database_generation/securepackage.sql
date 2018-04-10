CREATE TABLE securepackage.agreements (
  agreement_id VARCHAR(255),
  seller_id VARCHAR(255),
  buyer_id VARCHAR(255),
  item_id VARCHAR(255),
  state ENUM('CREATED', 'LOCKED', 'TRANSIT', 'DELIVERED', 'RETURN', 'RETURNED', 'CLERK', 'COMPLETED','INACTIVE'),
  violation BOOLEAN,
  terms_id VARCHAR(255),
  price INT);

CREATE TABLE securepackage.terms (
  terms_id VARCHAR(255),
  agreement_id VARCHAR(255),
  account_id VARCHAR(255),
  status ENUM('PROPOSED','DENIED','ACCEPTED'),
  item_id VARCHAR(255),
  price INT,
  postage_time TINYINT,
  pressure FLOAT,
  humidity FLOAT,
  temperature FLOAT,
  gps BOOLEAN,
  accelerometer FLOAT);

CREATE TABLE securepackage.images (
  image_id INT,
  item_id VARCHAR(255),
  image VARCHAR(255));

CREATE TABLE securepackage.items (
  agreement_id VARCHAR(255),
  title TEXT,
  description TEXT);

CREATE TABLE securepackage.sensors (
  sensor_id VARCHAR(255),
  kolli_id VARCHAR(255),
  sensor_type ENUM('GPS', 'TEMP', 'HUMID', 'ACC', 'PRES'),
  sensor_data VARCHAR(255));

CREATE TABLE securepackage.accounts (
  account_id VARCHAR(255),
  public_key VARCHAR(255),
  pass VARCHAR(255));

CREATE TABLE securepackage.paymentsimulation (
  agreement_id VARCHAR(255),
  paid BOOLEAN);

CREATE TABLE securepackage.bankaccountsimulation (
  bankaccount VARCHAR(255),
  account_id VARCHAR(255));

CREATE TABLE securepackage.logistics_simulation (
  kolli_id VARCHAR(255),
  agreement_id VARCHAR(255)
);

