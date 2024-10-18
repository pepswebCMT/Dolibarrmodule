<?php
// Dolibarr Simple Module - mymodule

// Load Dolibarr environment
require_once DOL_DOCUMENT_ROOT . '/main.inc.php'; // Utilisation du bon chemin

// Inclure les bibliothèques nécessaires
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

// Vérification de l'accès administrateur
global $user; // Ajout de la déclaration globale pour accéder à $user
if (!$user->admin) {
    // var_dump($user); // Débogage pour voir les informations sur l'utilisateur
    accessforbidden();
}

// Module main class
class modMyModule extends DolibarrModules
{
    public $id;
    public $db;
    public $numero;
    public $family;
    public $name;
    public $description;
    public $version;
    public $rights_class;
    public $module_parts;
    public $rights;
    public $menu;

    /**
     * Constructor. Define names, constants, directories, permissions, etc.
     */
    public function __construct($db)
    {
        global $conf, $user; // Ajout de $user comme global dans le constructeur

        $this->db = $db;
        $this->numero = 9999; // Numéro unique pour le module
        $this->rights_class = 'mymodule';
        $this->family = 'example'; // Famille du module (ex: "products", "hr", "crm")
        $this->name = 'MyModule'; // Nom du module
        $this->description = 'This is my module description'; // Description
        $this->version = '1.0'; // Version du module
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);

        // Debugging initial variables
        // var_dump($this->name, $this->db, $conf); // Vérification des variables globales

        // Vérification si le module est activé
        if (!isset($conf->mymodule->enabled)) {
            // var_dump($conf->mymodule->enabled); // Debugging activation status
            $this->error = "Module is not enabled or config is missing.";
            return -1; // Retourner -1 en cas d'erreur
        }

        // Définir les parties du module
        $this->module_parts = array(
            'triggers' => 0, // Pas de triggers pour cet exemple
        );

        // Définition des permissions
        $r = 0;
        $this->rights[$r][0] = $this->numero . $r; // Identifiant unique du droit
        $this->rights[$r][1] = 'Lire mon module'; // Nom du droit
        $this->rights[$r][3] = 1; // Droit activé par défaut
        $this->rights[$r][4] = 'lire'; // Action liée au droit
        $r++;

        // Définition des menus
        $this->menu = array();
        $r = 0;

        // Ajouter le menu principal sur la navbar du haut
        $this->menu[$r] = array(
            'fk_menu' => 0, // 0 signifie qu'il s'agit d'un élément de menu principal
            'type' => 'top', // Cela indique que c'est un menu de navigation en haut
            'titre' => 'My Module', // Le titre de votre module qui apparaîtra sur la barre de navigation
            'mainmenu' => 'mymodule', // L'identifiant principal pour le module (doit être unique)
            'leftmenu' => '', // Pas de sous-menu
            'url' => '/custom/mymodule/index.php', // URL du fichier qui sera chargé lorsque l'utilisateur cliquera sur l'entrée
            'langs' => 'mymodule@mymodule', // Fichier de langue (ajustez si vous avez un fichier de langue)
            'position' => 100, // Position dans le menu. Plus le nombre est bas, plus il sera affiché en premier.
            'enabled' => '$conf->mymodule->enabled', // Le menu est affiché seulement si le module est activé
            'perms' => '$user->rights->mymodule->lire', // Droits nécessaires pour voir ce menu (ajustez selon vos droits)
            'target' => '', // Cible (_blank, etc.)
            'user' => 2, // Indique pour quel type d'utilisateur le menu est visible (2 = utilisateur standard)
        );
    }

    /**
     * Create tables, keys, and data required by the module.
     */
    public function init($options = '')
    {
        global $user; // Assurez-vous que $user est global dans toutes les méthodes qui en ont besoin

        // Chargement des fichiers SQL pour la création des tables
        $sql = '/custom/mymodule/sql/tables.sql';

        // Chargement des tables
        $result = $this->_load_tables($sql);
        var_dump($result); // Debugging result to see if it's NULL or an error

        // Vérifiez si la création des tables a échoué
        if ($result < 0) {
            $this->error = $this->db->lasterror(); // Enregistrez l'erreur de la base de données
            return -1; // Retourner -1 en cas d'erreur
        }

        return 1; // Retourner 1 si tout fonctionne bien
    }

    /**
     * Remove module and clean up database.
     */
    public function remove($options = '')
    {
        global $user; // Assurez-vous que $user est global dans cette méthode aussi

        // Chargement des fichiers SQL pour la suppression des tables
        $sql = '/custom/mymodule/sql/tables.sql';

        // Suppression des tables
        $result = $this->_load_tables($sql, 1);
        var_dump($result); // Debugging result of removal

        // Vérifiez si la suppression a échoué
        if ($result < 0) {
            $this->error = $this->db->lasterror(); // Enregistrez l'erreur de la base de données
            return -1; // Retourner -1 en cas d'erreur
        }

        return 1; // Retourner 1 si la suppression est réussie
    }

    /**
     * Vérification des droits utilisateur
     */
    public function checkUserRights()
    {
        global $user; // Ajouter $user comme global dans la méthode checkUserRights

        if ($user->rights->mymodule->lire) {
            echo "Accès autorisé.";
        } else {
            accessforbidden(); // Bloquer l'accès avec un message d'erreur
        }
    }
}
