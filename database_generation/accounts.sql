-- auto-generated definition
CREATE TABLE accounts
(
  account_id     VARCHAR(255) NOT NULL
    PRIMARY KEY,
  public_key     VARCHAR(255) NULL,
  pass           VARCHAR(255) NULL,
  full_name      VARCHAR(255) NULL,
  street_address VARCHAR(255) NULL,
  city           VARCHAR(255) NULL,
  postcode       VARCHAR(255) NULL,
  CONSTRAINT accounts_account_id_uindex
  UNIQUE (account_id)
)
  ENGINE = InnoDB;