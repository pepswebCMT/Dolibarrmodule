<?php

require '../../../main.inc.php';

dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');


require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

if ($user->socid > 0 || !$user->hasRight('bilancarbonne', 'myobject', 'read') || !$user->hasRight('bilancarbonne', 'commande', 'read')) {
    accessforbidden();
}

$action = GETPOST('action', 'alpha');
$orderModel = new MyOrderModel($db);
$year = GETPOST('year', 'int') ?: date('Y');
$page = GETPOST('page', 'int') ?: 0;
$limit = GETPOST('limit', 'int') ?: 25;
$offset = $page * $limit;

if ($action === 'calculate_distance') {
    $clientAddress = GETPOST('client_address', 'alpha');
    $supplierAddress = GETPOST('supplier_address', 'alpha');
    $orderId = GETPOST('order_id', 'int');

    $result = $orderModel->calculateDistance($clientAddress, $supplierAddress);

    // Mise à jour en base de données si distance est calculée
    if (!empty($result['distance'])) {
        $sql = "UPDATE " . MAIN_DB_PREFIX . "commande SET distance_km = " . $result['distance'] . " WHERE rowid = " . $orderId;
        $db->query($sql);
    }

    echo json_encode($result);
    exit;
}

// Récupération des commandes sans calcul de distance
$orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', $limit, $offset);

// Inclusion du template avec les données
include '../template/listorders.php';
