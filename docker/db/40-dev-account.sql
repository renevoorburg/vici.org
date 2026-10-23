-- Give the anonymized user1 account a known dev password on localhost.
-- Password: vici-dev  (bcrypt hash in acc_passwd, verified via password_verify)

UPDATE `vici`.`accounts`
SET `acc_passwd` = '$2y$10$blVM1oWkj11ji/t8RFwbAetXH2cc0mt97jptpdi2EsKs79QGXGMP2'
WHERE acc_id = 1;
