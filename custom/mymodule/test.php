<?php
// Charger l'environnement Dolibarr
include '../../main.inc.php'; // Inclure le fichier de configuration principale

// Inclure les bibliothèques nécessaires
require_once DOL_DOCUMENT_ROOT . '/custom/mymodule/core/modules/modMyModule.class.php';

// Vérifier si l'utilisateur est administrateur
if (!$user->admin) {
    accessforbidden();
}

// Créer une instance de ton module
$mymodule = new modMyModule($db);

// Test de l'activation
echo "<h1>Test de l'activation du module</h1>";
$result = $mymodule->init();
if ($result > 0) {
    echo "<p>Le module a été activé et les tables ont été créées avec succès.</p>";
} else {
    echo "<p>Erreur lors de l'activation du module : " . $mymodule->error . "</p>";
}

// Test de la désactivation
echo "<h1>Test de la désactivation du module</h1>";
$result = $mymodule->remove();
if ($result > 0) {
    echo "<p>Le module a été désactivé et les tables ont été supprimées avec succès.</p>";
} else {
    echo "<p>Erreur lors de la désactivation du module : " . $mymodule->error . "</p>";
}
