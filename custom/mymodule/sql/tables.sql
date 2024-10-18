DROP TABLE IF EXISTS llx_mymodule_example;

CREATE TABLE llx_mymodule_products (
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL
);
