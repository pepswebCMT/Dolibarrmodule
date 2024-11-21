<?php
require '../main.inc.php';

// Sécurité Dolibarr
if (!$user->rights->yourmodule->read) {
    accessforbidden();
}

// Inclure les classes nécessaires
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';

// Initialisation des variables
$page = GETPOST('page', 'int') ?: 0; // Page courante
$limit = 25; // Nombre de lignes par page
$offset = $page * $limit;

// Création de l'objet commande
$commande = new Commande($db);

// Récupération des commandes
$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "commande WHERE 1=1";
$sql .= $db->order("date_creation DESC");
$sql .= $db->plimit($limit, $offset);

$resql = $db->query($sql);
if (!$resql) {
    dol_print_error($db);
    exit;
}

$orders = [];
while ($obj = $db->fetch_object($resql)) {
    $commande->fetch($obj->rowid);
    $orders[] = $commande;
}

// Affichage
include DOL_DOCUMENT_ROOT . '/custom/mymodule/template/listorders.tpl.php';
