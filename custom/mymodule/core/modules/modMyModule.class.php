<?php

// Dolibarr Simple Module - mymodule

// Load Dolibarr environment
require_once DOL_DOCUMENT_ROOT . '/main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

// Vérification de l'accès administrateur
global $user;
if (!$user->admin) {
    accessforbidden();
}

// Module main class
class modMyModule extends DolibarrModules
{
    public $db;
    public $numero;
    public $rights_class;
    public $module_parts;

    /**
     * Constructor. Define names, constants, directories, permissions, etc.
     */
    public function __construct($db)
    {
        global $conf, $user;
        $this->db = $db;
        $this->numero = 9999;  // Numéro unique de module
        $this->rights_class = 'mymodule';
        $this->name = 'MyModule';
        $this->description = 'This is my module description';
        $this->version = '1.0';
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);

        // Si le module n'est pas activé, on arrête ici
        if (empty($conf->global->MAIN_MODULE_MYMODULE) || $conf->global->MAIN_MODULE_MYMODULE != 1) {
            dol_syslog("Le module MyModule est désactivé", LOG_WARNING);
            return -1;
        }

        $this->module_parts = array('triggers' => 0);

        // Ajouter le menu principal sur la navbar du haut
        $this->menu[] = array(
            'fk_menu' => 0, // 0 signifie qu'il s'agit d'un élément de menu principal
            'type' => 'top', // Cela indique que c'est un menu de navigation en haut
            'titre' => 'My Module', // Le titre de votre module qui apparaîtra sur la barre de navigation
            'mainmenu' => 'mymodule', // L'identifiant principal pour le module (doit être unique)
            'leftmenu' => '', // Pas de sous-menu
            'url' => '/custom/mymodule/index.php', // URL du fichier qui sera chargé lorsque l'utilisateur cliquera sur l'entrée
            'langs' => '', // Désactiver la langue si elle n'existe pas
            'position' => 100, // Position dans le menu. Plus le nombre est bas, plus il sera affiché en premier.
            'enabled' => 1, // Le menu est activé si le module est activé
            'perms' => $user->rights->mymodule->lire, // Droits nécessaires pour voir ce menu
            'target' => '', // Cible (_blank, etc.)
            'user' => 2, // Indique pour quel type d'utilisateur le menu est visible (2 = utilisateur standard)
            'module' => 'mymodule' // Nom du module auquel le menu est lié
        );
    }

    /**
     * Create tables, keys, and data required by the module.
     * Cette méthode est appelée lors de l'activation du module.
     */
    public function init($options = '')
    {
        global $conf;

        dol_syslog("Activation du module MyModule", LOG_DEBUG);

        // Activer le module en mettant à jour la constante
        dolibarr_set_const($this->db, 'MAIN_MODULE_MYMODULE', '1', 'chaine', 0, '', $conf->entity);

        // Vérifier que le tableau $this->menu contient des données valides
        if (!empty($this->menu)) {
            // Boucle pour ajouter les menus définis dans $this->menu
            foreach ($this->menu as $menu) {
                // Vérifier si le menu existe déjà dans la base de données
                $sql_check = "SELECT rowid FROM " . MAIN_DB_PREFIX . "menu WHERE module = '" . $menu['module'] . "' AND mainmenu = '" . $menu['mainmenu'] . "'";
                $resql_check = $this->db->query($sql_check);

                if ($resql_check && $this->db->num_rows($resql_check) == 0) {
                    // Insérer le menu s'il n'existe pas déjà
                    $sql_insert = "INSERT INTO " . MAIN_DB_PREFIX . "menu (fk_menu, type, titre, mainmenu, leftmenu, url, langs, position, enabled, perms, target, module)
                                   VALUES (" . intval($menu['fk_menu']) . ", '" . $this->db->escape($menu['type']) . "', '" . $this->db->escape($menu['titre']) . "',
                                   '" . $this->db->escape($menu['mainmenu']) . "', '" . $this->db->escape($menu['leftmenu']) . "', '" . $this->db->escape($menu['url']) . "',
                                   '" . $this->db->escape($menu['langs']) . "', " . intval($menu['position']) . ", " . intval($menu['enabled']) . ",
                                   '" . $this->db->escape($menu['perms']) . "', '" . $this->db->escape($menu['target']) . "',
                                   '" . $this->db->escape($menu['module']) . "')";
                    $resql_insert = $this->db->query($sql_insert);

                    if (!$resql_insert) {
                        dol_print_error($this->db); // Afficher l'erreur SQL
                        dol_syslog("Erreur lors de l'ajout du menu : " . $menu['titre'], LOG_ERR);
                        return -1; // En cas d'erreur SQL, retourner une erreur
                    }
                }
            }
        }

        return 1;  // Retourner 1 pour indiquer que l'activation a réussi
    }

    /**
     * Remove module and clean up database.
     * Cette méthode est appelée lors de la désactivation du module.
     */
    public function remove($options = '')
    {
        global $conf, $db;

        dol_syslog("Désactivation du module MyModule", LOG_DEBUG);

        // Désactiver le module en mettant la constante à 0
        dolibarr_set_const($db, 'MAIN_MODULE_MYMODULE', '0', 'chaine', 0, '', $conf->entity);

        // Supprimer les entrées du menu associées au module
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "menu WHERE module = 'mymodule'";
        $resql = $this->db->query($sql);
        if (!$resql) {
            dol_syslog("Erreur lors de la suppression des menus", LOG_ERR);
            return -1; // En cas d'erreur SQL, retourner une erreur
        }

        return 1; // Retourner 1 pour indiquer que la désactivation a réussi
    }

    /**
     * Vérification des droits utilisateur
     */
    public function checkUserRights()
    {
        global $user;

        if ($user->rights->mymodule->lire) {
            echo "Accès autorisé.";
        } else {
            accessforbidden(); // Bloquer l'accès avec un message d'erreur
        }
    }
}
