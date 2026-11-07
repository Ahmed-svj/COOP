-- Runs once when the MySQL container is first created (after 01-schema.sql):
-- the app connects from the "app" container, so its account must accept any host.
CREATE USER IF NOT EXISTS 'coop_app'@'%' IDENTIFIED BY 'ChangeThisStrongPassword!';
GRANT SELECT, INSERT, UPDATE, DELETE ON coop_db.* TO 'coop_app'@'%';
FLUSH PRIVILEGES;
