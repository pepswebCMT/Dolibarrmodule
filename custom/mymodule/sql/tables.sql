-- DROP TABLE IF EXISTS llx_mymodule_example;

-- CREATE TABLE llx_mymodule_products (
--     rowid INT AUTO_INCREMENT PRIMARY KEY,
--     name VARCHAR(255) NOT NULL,
--     price DECIMAL(10, 2) NOT NULL
-- );


-- Fichier tables.sql pour l'activation et la désactivation

-- Création des tables à l'activation
CREATE TABLE IF NOT EXISTS llx_mymodule (
    rowid INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    field1 VARCHAR(255) NOT NULL,
    field2 TEXT,
    field3 INT(11) DEFAULT 0,
    datec DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tms TIMESTAMP
) ENGINE=innodb;

-- Insertion de la constante pour activer le module
-- INSERT INTO llx_const (name, value, type, visible, note) VALUES ('MAIN_MODULE_MYMODULE', '1', 'chaine', 1, 'Module MyModule activation');


