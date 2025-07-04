<?php
require '../../../../main.inc.php';
session_start();

dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');

header('Content-Type: application/json');

$year = GETPOST('year', 'int') ?: date('Y');
$orderModel = new MyOrderModel($db);

// Récupération de toutes les commandes
$orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', 10000, 0);
$totalOrders = count($orders);
$processedOrders = 0;
$emissionFactor = $orderModel->getEmissionFactor();

if ($totalOrders === 0) {
    echo json_encode(['success' => false, 'message' => 'Aucune commande trouvée']);
    exit;
}

// Fichier temporaire pour la progression
$progressFile = __DIR__ . '/progress.json';

// Réinitialisation du fichier de progression
file_put_contents($progressFile, json_encode(['processed' => 0, 'total' => $totalOrders]));

foreach ($orders as $order) {
    $orderId = $order->rowid;
    $clientAddress = "{$order->address}, {$order->zip} {$order->town}";
    $supplierAddress = "{$order->fournisseur_address}, {$order->fournisseur_zip} {$order->fournisseur_town}";
    $transitAddress = $order->transit_address;

    $distanceData = $orderModel->getCachedDistance($clientAddress, $supplierAddress, $transitAddress);

    if (!$distanceData) {
        $distanceResult = !empty($transitAddress) ?
            $orderModel->calculateDistance($clientAddress, $transitAddress, $supplierAddress) :
            $orderModel->calculateDistance($clientAddress, $supplierAddress);

        if (!empty($distanceResult['distance']) && is_numeric($distanceResult['distance'])) {
            $distance = round($distanceResult['distance'], 2);
            $weight = $orderModel->getOrderWeight($orderId) ?: 1;
            $co2 = ($weight / 1000) * $distance * $emissionFactor / 1000;

            $orderModel->storeDistance($clientAddress, $supplierAddress, $transitAddress, $distance, $co2);
        }
    }

    // Mise à jour de la progression
    $processedOrders++;
    file_put_contents($progressFile, json_encode(['processed' => $processedOrders, 'total' => $totalOrders]));
}

// Retourne un succès
echo json_encode(['success' => true, 'message' => 'Calcul terminé']);
