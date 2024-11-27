<?php
require '../../../main.inc.php';

// Bloque l'accès aux utilisateurs externes
if ($user->socid > 0) {
    accessforbidden();
}

// Vérification des droits de lecture
if (! $user->hasRight('bilancarbonne', 'myobject', 'read')) {
    accessforbidden();
}


dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');

// Initialiser le modèle
$orderModel = new MyOrderModel($db);

// Récupérer les paramètres via POST
$action = GETPOST('action', 'alpha');
$sort = GETPOST('sort', 'alpha', 'date_commande'); // Par défaut : date_commande
$order = GETPOST('order', 'alpha', 'ASC'); // Par défaut : ASC
$page = (int) GETPOST('page', 'int', 0); // Par défaut : 0 (première page)
$year = GETPOST('year', 'int'); // Année choisie par l'utilisateur
$limit = (int) GETPOST('limit', 'int', 25); // Par défaut : 25 lignes par page

// Validation du paramètre $limit
if ($limit <= 0) {
    $limit = 25; // Valeur par défaut si $limit est invalide
}

// Valeurs par défaut si non définies
if (!$year) {
    $year = date('Y'); // Année en cours par défaut
}

// Vérifier l'action
switch ($action) {
    case 'list':

        if (! $user->hasRight('bilancarbonne', 'commande', 'read')) {
            accessforbidden(); // Bloque l'accès si l'utilisateur n'a pas les droits nécessaires
        }

        // Récupérer toutes les commandes pour l'année donnée
        $allOrders = $orderModel->getOrdersByYear($year, $sort, $order);

        // Récupérer toutes les années disponibles
        $availableYears = $orderModel->getAvailableYears();

        // Calcul de la pagination
        $total_records = count($allOrders);
        $total_pages = $limit > 0 ? ceil($total_records / $limit) : 1; // Sécurité supplémentaire
        $offset = $page * $limit;

        // Sélectionner uniquement les commandes pour la page actuelle
        $orders = array_slice($allOrders, $offset, $limit);

        // Inclure la vue
        include '../template/listorders.php';
        break;

    default:
        print 'Action inconnue';
        break;
}
