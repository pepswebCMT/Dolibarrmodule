-- Suppression des tables à la désactivation
DROP TABLE IF EXISTS llx_mymodule;

-- Suppression de la constante pour désactiver le module
DELETE FROM llx_const WHERE name = 'MAIN_MODULE_MYMODULE';