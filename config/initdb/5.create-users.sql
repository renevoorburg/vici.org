CREATE USER 'vici-user'@'%' IDENTIFIED BY 'vici-password';
GRANT ALL PRIVILEGES ON vici.* TO 'vici-user'@'%';
GRANT SELECT ON geo.* TO 'vici-user'@'%';
FLUSH PRIVILEGES;