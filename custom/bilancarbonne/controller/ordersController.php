<?php
require '../../../main.inc.php';
// require '../../../../htdocs/main.inc.php';

// Vérification des droits
// if (!$user->rights->bilancarbonne->read) {
//     accessforbidden();
// }

// Inclure le modèle
require_once '../Model/MyOrder.php';

// Initialiser le modèle
$orderModel = new MyOrderModel($db);

// Récupérer l'action via POST
$action = GETPOST('action', 'alpha');

// Récupérer les autres paramètres via POST
$sort = GETPOST('sort', 'alpha', 'date_commande'); // Par défaut : date_commande
$order = GETPOST('order', 'alpha', 'ASC'); // Par défaut : ASC
$page = (int) GETPOST('page', 'int', 0); // Par défaut : 0 (première page)
$year = GETPOST('year', 'int'); // Année choisie par l'utilisateur

// Valeurs par défaut si non définies
if (!$year) {
    $year = date('Y'); // Année en cours par défaut
}

// Vérifier l'action
switch ($action) {
    case 'list':
        $limit = 25; // Nombre d'éléments par page

        // Récupérer toutes les commandes pour l'année donnée
        $allOrders = $orderModel->getOrdersByYear($year, $sort, $order);

        // Calcul de la pagination
        $total_records = count($allOrders);
        $total_pages = ceil($total_records / $limit);
        $offset = $page * $limit;

        // Sélectionner uniquement les 25 commandes pour la page actuelle
        $orders = array_slice($allOrders, $offset, $limit);

        // Inclure la vue
        include '../template/listorders.php';
        break;
    default:
        print 'Action inconnue';
        break;
}
