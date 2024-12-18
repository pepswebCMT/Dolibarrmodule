<?php
require '../../../main.inc.php';

dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');

$orderModel = new MyOrderModel($db);

$year = GETPOST('year', 'int') ?: date('Y');
$page = GETPOST('page', 'int') ?: 0;
$limit = GETPOST('limit', 'int') ?: 25;
$offset = $page * $limit;

// Récupération des commandes
$orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', $limit, $offset);

// Calcul des distances pour chaque commande
foreach ($orders as &$order) {
    $clientAddress = "{$order->client_address}, {$order->client_zip} {$order->client_town}";
    $supplierAddress = "{$order->supplier_address}, {$order->supplier_zip} {$order->supplier_town}";

    $distance = $orderModel->calculateDistance($clientAddress, $supplierAddress);
    $order->distance_km = $distance ? round($distance, 2) : null; // Ajoutez la distance à chaque commande
}

// Passez les données à la vue
include '../template/listorders.php';
