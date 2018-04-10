ALTER TABLE securepackage.agreements ADD CONSTRAINT FK_agreement_seller_id
  FOREIGN KEY (seller_id) REFERENCES securepackage.accounts(account_id);

ALTER TABLE securepackage.agreements ADD CONSTRAINT FK_agreement_buyer_id
  FOREIGN KEY (buyer_id) REFERENCES securepackage.accounts(account_id);

ALTER TABLE securepackage.agreements ADD CONSTRAINT FK_agreement_terms_id
  FOREIGN KEY (terms_id) REFERENCES securepackage.terms(terms_id);

ALTER TABLE securepackage.bankaccountsimulation ADD CONSTRAINT FK_bankaccounts_account_id
  FOREIGN KEY (account_id) REFERENCES securepackage.accounts(account_id);

ALTER TABLE securepackage.images ADD CONSTRAINT FK_image_item_id
  FOREIGN KEY (item_id) REFERENCES securepackage.items(agreement_id);

ALTER TABLE securepackage.paymentsimulation ADD CONSTRAINT FK_payments_agreement_id
  FOREIGN KEY (agreement_id) REFERENCES securepackage.agreements(agreement_id);

ALTER TABLE securepackage.terms ADD CONSTRAINT FK_terms_agreement_id
  FOREIGN KEY (agreement_id) REFERENCES securepackage.agreements(agreement_id);

ALTER TABLE securepackage.logistics_simulation ADD CONSTRAINT FK_logisticssimulation_agreement_id
  FOREIGN KEY (agreement_id) REFERENCES  securepackage.agreements(agreement_id);

ALTER TABLE securepackage.sensors ADD CONSTRAINT FK_sensors_kolli_id
  FOREIGN KEY (kolli_id) REFERENCES  securepackage.logistics_simulation(kolli_id);
