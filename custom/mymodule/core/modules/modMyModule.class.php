<?php

// Dolibarr Simple Module - mymodule

// Load Dolibarr environment
require_once DOL_DOCUMENT_ROOT . '/main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

// Vérification de l'accès administrateur
global $user, $db;
if (!$user->admin) {
    accessforbidden();
}

// Module main class
class modMyModule extends DolibarrModules
{
    public $db;
    public $numero = 9999; // Numéro unique du module
    public $rights_class = 'mymodule';

    public function __construct($db)
    {
        global $conf;
        $this->db = $db;
        $this->name = 'MyModule';
        $this->description = 'This is my custom module';
        $this->version = '1.0';
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);

        // Vérification si le module est actif ou non
        if (empty($conf->global->MAIN_MODULE_MYMODULE)) {
            dol_syslog("Module non actif", LOG_WARNING);
            return;
        }
    }

    /**
     * Méthode d'initialisation - appelée lors de l'activation du module
     */
    public function init($options = '')
    {
        global $conf;

        dol_syslog("Activation du module MyModule", LOG_DEBUG);

        // Activer le module
        dolibarr_set_const($this->db, 'MAIN_MODULE_MYMODULE', '1', 'chaine', 0, '', $conf->entity);

        // Activer les menus en mettant `enabled` à 1
        $this->toggleMenus(1);

        // Forcer le rafraîchissement des menus (vider le cache des fichiers statiques)
        clearstatcache();

        return 1;
    }

    /**
     * Méthode pour désactiver le module
     */
    public function remove($options = '')
    {
        global $conf;

        dol_syslog("Désactivation du module MyModule", LOG_DEBUG);

        // Désactiver le module
        dolibarr_set_const($this->db, 'MAIN_MODULE_MYMODULE', '0', 'chaine', 0, '', $conf->entity);

        // Désactiver les menus en mettant `enabled` à 0
        $this->toggleMenus(0);

        return 1;
    }

    /**
     * Méthode pour activer ou désactiver les menus en changeant la valeur de `enabled`
     */
    private function toggleMenus($enabled)
    {
        // Mettre à jour tous les menus associés au module 'mymodule'
        $sql = "UPDATE " . MAIN_DB_PREFIX . "menu SET enabled = " . intval($enabled) . " WHERE module = 'mymodule'";
        $this->db->query($sql);
    }

    /**
     * Méthode pour ajouter les menus
     */
    private function createMenus()
    {
        global $conf, $user;

        // Exemple d'un menu à ajouter (menu principal)
        $menuItems = [
            [
                'fk_menu' => 0,
                'type' => 'top',
                'mainmenu' => 'mymodule',
                'titre' => 'My Module',
                'url' => '/custom/mymodule/index.php',
                'langs' => 'mymodule@mymodule',
                'position' => 100,
                'enabled' => 1,
                'perms' => '$user->rights->mymodule->read',
                'target' => '',
                'module' => 'mymodule'
            ]
        ];

        foreach ($menuItems as $menu) {
            // Vérifier si le menu existe déjà
            $sql_check = "SELECT rowid FROM " . MAIN_DB_PREFIX . "menu WHERE mainmenu = '" . $this->db->escape($menu['mainmenu']) . "' AND module = '" . $this->db->escape($menu['module']) . "'";
            $resql_check = $this->db->query($sql_check);

            if ($resql_check && $this->db->num_rows($resql_check) == 0) {
                // Ajouter le menu si non existant
                $sql_insert = "INSERT INTO " . MAIN_DB_PREFIX . "menu (menu_handler, entity, module, type, mainmenu, fk_menu, position, url, target, titre, langs, perms, enabled, usertype)
                    VALUES ('eldy', " . intval($conf->entity) . ", '" . $this->db->escape($menu['module']) . "', '" . $this->db->escape($menu['type']) . "',
                    '" . $this->db->escape($menu['mainmenu']) . "', " . intval($menu['fk_menu']) . ", " . intval($menu['position']) . ", 
                    '" . $this->db->escape($menu['url']) . "', '" . $this->db->escape($menu['target']) . "', '" . $this->db->escape($menu['titre']) . "',
                    '" . $this->db->escape($menu['langs']) . "', '" . $menu['perms'] . "', " . intval($menu['enabled']) . ", 2)";

                // Exécuter l'insertion
                $this->db->query($sql_insert);
            }
        }
    }
}
