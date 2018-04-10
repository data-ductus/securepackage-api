ALTER TABLE securepackage.accounts
  ADD PRIMARY KEY (account_id);

ALTER TABLE securepackage.agreements
  ADD PRIMARY KEY (agreement_id);

ALTER TABLE securepackage.bankaccountsimulation
  ADD PRIMARY KEY (bankaccount);

ALTER TABLE securepackage.images
  ADD PRIMARY KEY (image_id);

ALTER TABLE securepackage.items
  ADD PRIMARY KEY (agreement_id);

ALTER TABLE securepackage.paymentsimulation
  ADD PRIMARY KEY (agreement_id);

ALTER TABLE securepackage.sensors
  ADD PRIMARY KEY (sensor_id);

ALTER TABLE securepackage.terms
  ADD PRIMARY KEY (terms_id);

ALTER TABLE securepackage.logistics_simulation
  ADD PRIMARY KEY (kolli_id);
