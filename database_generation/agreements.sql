-- auto-generated definition
CREATE TABLE agreements
(
  agreement_id VARCHAR(255)                                                                                                                       NOT NULL
    PRIMARY KEY,
  seller_id    VARCHAR(255)                                                                                                                       NULL,
  buyer_id     VARCHAR(255)                                                                                                                       NULL,
  state        ENUM ('CREATED', 'LOCKED', 'TRANSIT', 'DELIVERED', 'REJECTED', 'RETURN', 'RETURNED', 'CLERK', 'RESOLVED', 'COMPLETED', 'INACTIVE') NULL,
  violation    TINYINT(1) DEFAULT '0'                                                                                                             NULL,
  terms_id     VARCHAR(255)                                                                                                                       NULL,
  date_created DATETIME                                                                                                                           NULL,
  date_locked  DATETIME                                                                                                                           NULL,
  CONSTRAINT agreements_agreement_id_uindex
  UNIQUE (agreement_id),
  CONSTRAINT FK_agreement_seller_id
  FOREIGN KEY (seller_id) REFERENCES accounts (account_id),
  CONSTRAINT FK_agreement_buyer_id
  FOREIGN KEY (buyer_id) REFERENCES accounts (account_id)
)
  ENGINE = InnoDB;

CREATE INDEX FK_agreement_seller_id
  ON agreements (seller_id);

CREATE INDEX FK_agreement_buyer_id
  ON agreements (buyer_id);