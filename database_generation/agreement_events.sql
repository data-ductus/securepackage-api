-- auto-generated definition
CREATE TABLE agreement_events
(
  event_id         VARCHAR(255)                                                                                                                                                                                                      NOT NULL
    PRIMARY KEY,
  event_type       ENUM ('CREATE', 'PROPOSE', 'ACCEPT', 'DECLINE', 'S_POST', 'B_POST', 'B_DELIVER', 'S_DELIVER', 'VIOLATE', 'B_APPROVE', 'S_APPROVE', 'B_REJECT', 'S_REJECT', 'B_NOFEED', 'S_NOFEED', 'B_ABORT', 'S_ABORT', 'CLERK') NULL,
  event_payload    TEXT                                                                                                                                                                                                              NULL,
  timestamp        DATETIME                                                                                                                                                                                                          NULL,
  target_agreement VARCHAR(255)                                                                                                                                                                                                      NULL,
  CONSTRAINT agreement_events_event_id_uindex
  UNIQUE (event_id)
)
  ENGINE = InnoDB;