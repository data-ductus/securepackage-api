-- auto-generated definition
CREATE TABLE clerk_actions
(
  agreement_id   VARCHAR(255)                                         NOT NULL
    PRIMARY KEY,
  clerk_id       VARCHAR(255)                                         NULL,
  liable_party   ENUM ('UNDECIDABLE', 'SELLER', 'BUYER', 'LOGISTICS') NULL,
  message        TEXT                                                 NULL,
  buyer_confirm  TINYINT(1) DEFAULT '0'                               NULL,
  seller_confirm TINYINT(1) DEFAULT '0'                               NULL,
  CONSTRAINT clerk_actions_agreement_id_uindex
  UNIQUE (agreement_id),
  CONSTRAINT FK_clerk_actions_clerk_id
  FOREIGN KEY (clerk_id) REFERENCES clerk_accounts (clerk_id)
)
  ENGINE = InnoDB;

CREATE INDEX FK_clerk_actions_clerk_id
  ON clerk_actions (clerk_id);