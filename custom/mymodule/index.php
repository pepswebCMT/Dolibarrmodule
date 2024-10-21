<?php
// Charger l'environnement Dolibarr
include '../../main.inc.php'; // Inclure le fichier de configuration principale

// Vérifier si l'utilisateur a les droits nécessaires pour accéder au module
if (!$user->rights->mymodule->lire) {
    accessforbidden(); // Bloquer l'accès si l'utilisateur n'a pas le droit "lire"
}

// Commencez la page Dolibarr
llxHeader('', 'My Module Page');

// Contenu de la page
print_fiche_titre("Bienvenue sur My Module");
print '<p>Ceci est la page principale de votre module.</p>';

// Fin de la page
llxFooter();
