-- auto-generated definition
CREATE TABLE clerk_accounts
(
  clerk_id VARCHAR(255) NOT NULL
    PRIMARY KEY,
  password VARCHAR(255) NULL,
  CONSTRAINT clerk_accounts_clerk_id_uindex
  UNIQUE (clerk_id)
)
  ENGINE = InnoDB;
