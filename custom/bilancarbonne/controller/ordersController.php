<?php
// require '../../../main.inc.php';

// dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');

// // Bloque l'accès aux utilisateurs externes
// if ($user->socid > 0) {
//     accessforbidden();
// }
// // Vérification des droits de lecture
// if (! $user->hasRight('bilancarbonne', 'myobject', 'read')) {
//     accessforbidden();
// }

// if (! $user->hasRight('bilancarbonne', 'commande', 'read')) {
//     accessforbidden();
// }

// $orderModel = new MyOrderModel($db);

// $year = GETPOST('year', 'int') ?: date('Y');
// $page = GETPOST('page', 'int') ?: 0;
// $limit = GETPOST('limit', 'int') ?: 25;
// $offset = $page * $limit;

// // Récupération des commandes
// $orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', $limit, $offset);

// // Calcul des distances pour chaque commande
// foreach ($orders as &$order) {
//     $clientAddress = "{$order->client_address}, {$order->client_zip} {$order->client_town}";
//     $supplierAddress = "{$order->supplier_address}, {$order->supplier_zip} {$order->supplier_town}";

//     $distance = $orderModel->calculateDistance($clientAddress, $supplierAddress);
//     $order->distance_km = $distance ? round($distance, 2) : null; // Ajoutez la distance à chaque commande
// }

// // Passez les données à la vue
// include '../template/listorders.php';

// listorders.php
require '../../../main.inc.php';
dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');

if ($user->socid > 0 || !$user->hasRight('bilancarbonne', 'myobject', 'read') || !$user->hasRight('bilancarbonne', 'commande', 'read')) {
    accessforbidden();
}

$action = GETPOST('action', 'alpha');
$orderModel = new MyOrderModel($db);
$year = GETPOST('year', 'int') ?: date('Y');
$page = GETPOST('page', 'int') ?: 0;
$limit = GETPOST('limit', 'int') ?: 25;
$offset = $page * $limit;

// Endpoint pour le calcul de distance Ajax
if ($action === 'calculate_distance') {
    $clientAddress = GETPOST('client_address', 'alpha');
    $supplierAddress = GETPOST('supplier_address', 'alpha');
    $orderId = GETPOST('order_id', 'int');

    $distance = $orderModel->calculateDistance($clientAddress, $supplierAddress);

    // Mettre à jour la distance en base de données
    if ($distance !== null) {
        $sql = "UPDATE " . MAIN_DB_PREFIX . "commande SET distance_km = " . $distance . " WHERE rowid = " . $orderId;
        $db->query($sql);
    }

    echo json_encode(['distance' => $distance ? round($distance, 2) : null]);
    exit;
}

// Récupération des commandes sans calcul de distance
$orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', $limit, $offset);

// Inclusion du template avec les données
include '../template/listorders.php';
