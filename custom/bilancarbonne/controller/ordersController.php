<?php

// // require '../../../main.inc.php';

// // dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');


// // require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
// // require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// // if ($user->socid > 0 || !$user->hasRight('bilancarbonne', 'myobject', 'read') || !$user->hasRight('bilancarbonne', 'commande', 'read')) {
// //     accessforbidden();
// // }

// // $action = GETPOST('action', 'alpha');
// // $orderModel = new MyOrderModel($db);
// // $year = GETPOST('year', 'int') ?: date('Y');
// // $page = GETPOST('page', 'int') ?: 0;
// // $limit = GETPOST('limit', 'int') ?: 25;
// // $offset = $page * $limit;


// // if ($action === 'calculate_distance') {
// //     // Ces lignes sont manquantes au début de la condition
// //     $clientAddress = GETPOST('client_address', 'alpha');
// //     $supplierAddress = GETPOST('supplier_address', 'alpha');
// //     $transitAddress = GETPOST('transit_address', 'alpha');
// //     $orderId = GETPOST('order_id', 'int');
// //     $emissionFactor = GETPOST('emission_factor', 'float') ?: 80.8;

// //     error_log("Debug - Paramètres reçus:");
// //     error_log("Client: " . $clientAddress);
// //     error_log("Fournisseur: " . $supplierAddress);
// //     error_log("Transit: " . $transitAddress);
// //     error_log("Order ID: " . $orderId);
// //     error_log("Emission Factor: " . $emissionFactor);
// //     // Vérifier d'abord le cache
// //     $cachedDistance = $orderModel->getCachedDistance($clientAddress, $supplierAddress, $transitAddress);

// //     if ($cachedDistance) {
// //         $response = [
// //             'success' => true,
// //             'distance' => $cachedDistance['distance'],
// //             'co2' => $cachedDistance['co2']
// //         ];
// //     } else {

// //         error_log("DEBUG: Calcul distance pour la commande $orderId");
// //         error_log("Client Address: " . $clientAddress);
// //         error_log("Supplier Address: " . $supplierAddress);
// //         error_log("Transit Address: " . ($transitAddress ?: "N/A"));
// //         // Calcul normal si pas en cache
// //         $result = !empty($transitAddress)
// //             ? $orderModel->calculateDistance($clientAddress, $transitAddress, $supplierAddress)
// //             : $orderModel->calculateDistance($clientAddress, $supplierAddress);

// //         error_log("Résultat de calculateDistance: " . print_r($result, true));

// //         if (!empty($result['distance'])) {
// //             $result['distance'] = round($result['distance'], 2);
// //             $weight = $orderModel->getOrderWeight($orderId);
// //             $co2 = ($weight / 1000) * $result['distance'] * $emissionFactor / 1000;

// //             // Stocker dans le cache
// //             $orderModel->storeDistance(
// //                 $clientAddress,
// //                 $supplierAddress,
// //                 $transitAddress,
// //                 $result['distance'],
// //                 $co2
// //             );

// //             $response = [
// //                 'success' => true,
// //                 'distance' => $result['distance'],
// //                 'co2' => $co2
// //             ];
// //         } else {
// //             $response = [
// //                 'success' => false,
// //                 'error' => 'Impossible de calculer la distance'
// //             ];
// //         }
// //     }

// //     header('Content-Type: application/json');
// //     echo json_encode($response);
// //     exit;
// //     error_log("Réponse API Jawg : " . print_r($routingData, true));
// // }

// // $orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', $limit, $offset);

// // // Debug : Affichez les résultats pour confirmer
// // error_log(print_r($orders, true));

// // // Si $orders est vide
// // if (empty($orders)) {
// //     echo '<p>Aucune commande trouvée pour l\'année ' . $year . '.</p>';
// //     exit;
// // }


// // // Inclusion du template avec les données
// // include '../template/listorders.php';


// require '../../../main.inc.php';

// dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');

// require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
// require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// if ($user->socid > 0 || !$user->hasRight('bilancarbonne', 'myobject', 'read') || !$user->hasRight('bilancarbonne', 'commande', 'read')) {
//     accessforbidden();
// }

// $action = GETPOST('action', 'alpha');
// $orderModel = new MyOrderModel($db);
// $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
// $page = GETPOST('page', 'int') ?: 0;
// $limit = GETPOST('limit', 'int') ?: 25;
// $offset = $page * $limit;

// if ($action === 'calculate_distance') {
//     // Récupération des paramètres
//     $clientAddress = GETPOST('client_address', 'alpha');
//     $supplierAddress = GETPOST('supplier_address', 'alpha');
//     $transitAddress = GETPOST('transit_address', 'alpha');
//     $orderId = GETPOST('order_id', 'int');
//     // $emissionFactor = GETPOST('emission_factor', 'float');
//     // if (!$emissionFactor || $emissionFactor <= 0) {
//     //     error_log("Facteur d'émission invalide, utilisation de la valeur par défaut");
//     //     $emissionFactor = 80.8;
//     // }

//     $defaultEmissionFactor = 80.8;  // Valeur par défaut
//     $emissionFactor = isset($_SESSION['emission_factor']) ? $_SESSION['emission_factor'] : $defaultEmissionFactor;

//     // if ($action === 'update_emission_factor') {
//     //     $newFactor = GETPOST('emission_factor', 'float');
//     //     if ($newFactor > 0) {
//     //         $_SESSION['emission_factor'] = $newFactor;
//     //         $emissionFactor = $newFactor;
//     //     }
//     // }a   

//     if ($action === 'update_emission_factor') {
//         $newFactor = GETPOST('emission_factor', 'float');
//         if ($newFactor > 0) {
//             $_SESSION['emission_factor'] = $newFactor;
//             // Répondre avec un statut de succès
//             header('Content-Type: application/json');
//             echo json_encode(['success' => true]);
//             exit;
//         }
//     }
//     // 🛠️ Nettoyage des adresses
//     function cleanAddress($address)
//     {
//         $address = strtoupper($address);
//         $address = preg_replace('/\b(CS|CEDEX|BP|ZAC|ZI|IMPASSE|PARC|ACTIPOLE|STOCK)\b\s*\d*/', '', $address);
//         $address = str_replace(["\n", "\r"], ' ', $address); // Supprime les retours à la ligne
//         return trim($address);
//     }

//     $clientAddress = cleanAddress($clientAddress);
//     $supplierAddress = cleanAddress($supplierAddress);
//     $transitAddress = cleanAddress($transitAddress);

//     // Log des paramètres reçus
//     error_log("DEBUG - Commande #$orderId");
//     error_log("Client : '$clientAddress'");
//     error_log("Supplier : '$supplierAddress'");
//     error_log("Transit : '" . ($transitAddress ?: "N/A") . "'");
//     error_log("Emission Factor : $emissionFactor");

//     // Vérifier si la distance est déjà en cache
//     $cachedDistance = $orderModel->getCachedDistance($clientAddress, $supplierAddress, $transitAddress);

//     if ($cachedDistance) {
//         error_log("✅ Distance récupérée en base pour #$orderId : " . print_r($cachedDistance, true));

//         $response = [
//             'success' => true,
//             'distance' => $cachedDistance['distance'],
//             'co2' => $cachedDistance['co2']
//         ];
//     } else {
//         error_log("Aucune donnée en cache, appel à l'API pour #$orderId");

//         if (empty($clientAddress) || empty($supplierAddress)) {
//             error_log("ERREUR : Adresse manquante pour #$orderId");
//             $response = [
//                 'success' => false,
//                 'error' => 'Adresse manquante'
//             ];
//         } else {
//             // Calcul de la distance
//             $result = !empty($transitAddress)
//                 ? $orderModel->calculateDistance($clientAddress, $transitAddress, $supplierAddress)
//                 : $orderModel->calculateDistance($clientAddress, $supplierAddress);

//             error_log("Résultat API pour #$orderId : " . print_r($result, true));

//             if (!empty($result['distance']) && is_numeric($result['distance'])) {
//                 $distance = round($result['distance'], 2);
//                 $weight = $orderModel->getOrderWeight($orderId) ?: 1;
//                 $co2 = ($weight / 1000) * $distance * $emissionFactor / 1000;

//                 if ($distance > 0 && $co2 > 0) {
//                     $orderModel->storeDistance($clientAddress, $supplierAddress, $transitAddress, $distance, $co2);
//                     error_log("Distance stockée pour #$orderId : $distance km | CO₂ = $co2 kg");

//                     $response = [
//                         'success' => true,
//                         'distance' => $distance,
//                         'co2' => $co2
//                     ];
//                 } else {
//                     error_log("ERREUR : Distance ou CO₂ invalide pour #$orderId");
//                     $response = [
//                         'success' => false,
//                         'error' => 'Données invalides'
//                     ];
//                 }
//             } else {
//                 error_log("ERREUR : Impossible de calculer la distance pour #$orderId");
//                 $response = [
//                     'success' => false,
//                     'error' => 'Impossible de calculer la distance'
//                 ];
//             }
//         }
//     }

//     header('Content-Type: application/json');
//     echo json_encode($response);
//     exit;
// }

// // Récupération des commandes
// $orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', $limit, $offset);

// error_log("Commandes récupérées en base : " . print_r($orders, true));

// // Vérification des distances récupérées en base
// foreach ($orders as $order) {
//     if (!empty($order->distance)) {
//         error_log("Commande #{$order->rowid} a déjà une distance : {$order->distance} km");
//     } else {
//         error_log("Commande #{$order->rowid} n'a pas de distance enregistrée en base !");
//     }
// }

// // Inclusion du template avec les données
// include '../template/listorders.php';


// 1. INCLUSIONS ET INITIALISATIONS
// ==========================
// INCLUSIONS ET INITIALISATIONS
// ==========================
require '../../../main.inc.php';
session_start();

dol_include_once('/custom/bilancarbonne/Model/MyOrder.php');
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérification des droits d'accès
if ($user->socid > 0 || !$user->hasRight('bilancarbonne', 'myobject', 'read') || !$user->hasRight('bilancarbonne', 'commande', 'read')) {
    accessforbidden();
}

// Initialisation du modèle
$orderModel = new MyOrderModel($db);
$action = GETPOST('action', 'alpha');

// ==========================
// GESTION DES REQUÊTES POST
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        switch ($action) {
            case 'update_emission_factor':
                handleUpdateEmissionFactor($orderModel);
                break;

            case 'calculate_distance':
                handleCalculateDistance($orderModel);
                break;

            case 'get_orders_with_changed_weight':
                handleGetOrdersWithChangedWeight($orderModel);
                break;

            default:
                throw new Exception("Action non reconnue");
        }
    } catch (Exception $e) {
        error_log("Erreur: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ==========================
// GESTION DES REQUÊTES GET (Affichage des commandes)
// ==========================
$year = GETPOST('year', 'int') ?: date('Y');
$page = GETPOST('page', 'int') ?: 0;
$limit = GETPOST('limit', 'int') ?: 25;
$offset = $page * $limit;

try {
    $orders = $orderModel->getOrdersByYear($year, 'date_commande', 'ASC', $limit, $offset);

    if (empty($orders)) {
        error_log("Aucune commande trouvée pour l'année $year");
    }

    include '../template/listorders.php';
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des commandes: " . $e->getMessage());
    print $e->getMessage();
}

// ==========================
// FONCTIONS AUXILIAIRES
// ==========================

/**
 * Met à jour le facteur d'émission dans la base de données
 */
function handleUpdateEmissionFactor($orderModel)
{
    $newFactor = GETPOST('emission_factor', 'float');

    if ($newFactor <= 0) {
        throw new Exception("Le facteur d'émission doit être positif.");
    }

    $orderModel->updateEmissionFactor($newFactor);
    error_log("Facteur d'émission mis à jour : " . $newFactor);

    echo json_encode(['success' => true, 'new_factor' => $newFactor]);
}

/**
 * Gère le calcul du bilan carbone (vérification cache + calcul)
 */
function handleCalculateDistance($orderModel)
{

    error_log("DEBUG : Requête reçue pour calcul distance");
    error_log("Données reçues : " . print_r($_POST, true));


    header('Content-Type: application/json');

    $clientAddress = cleanAddress(GETPOST('client_address', 'alpha'));
    $supplierAddress = cleanAddress(GETPOST('supplier_address', 'alpha'));
    $transitAddress = cleanAddress(GETPOST('transit_address', 'alpha'));
    $orderId = GETPOST('order_id', 'int');

    if (empty($clientAddress) || empty($supplierAddress)) {
        throw new Exception("Adresses manquantes pour la commande #$orderId");
    }

    // Récupération du facteur d'émission
    $emissionFactor = $orderModel->getEmissionFactor();

    // Vérification du cache ou appel à l'API
    $distance = getDistanceFromCacheOrAPI($orderModel, $clientAddress, $supplierAddress, $transitAddress, $orderId);

    if (!$distance) {
        throw new Exception("Impossible d'obtenir la distance pour la commande #$orderId");
    }

    // Calcul du CO₂
    $co2 = calculateCO2($orderModel, $orderId, $distance, $emissionFactor);

    error_log("Calcul terminé - Distance : $distance km | CO₂ : $co2 kg");

    // Stockage en cache si nécessaire
    if ($distance > 0 && $co2 > 0) {
        $orderModel->storeDistance($clientAddress, $supplierAddress, $transitAddress, $distance, $co2);
    }

    echo json_encode(['success' => true, 'distance' => $distance, 'co2' => $co2]);
}

/**
 * Vérifie la distance en cache ou la calcule via l'API si elle est absente
 */
function getDistanceFromCacheOrAPI($orderModel, $clientAddress, $supplierAddress, $transitAddress, $orderId)
{
    $cachedDistance = $orderModel->getCachedDistance($clientAddress, $supplierAddress, $transitAddress);

    if ($cachedDistance) {
        error_log("Distance récupérée en cache pour #$orderId : {$cachedDistance['distance']} km");
        return $cachedDistance['distance'];
    }

    error_log("Distance non trouvée en cache, appel à l'API pour #$orderId");
    $result = !empty($transitAddress)
        ? $orderModel->calculateDistance($clientAddress, $transitAddress, $supplierAddress)
        : $orderModel->calculateDistance($clientAddress, $supplierAddress);

    if (empty($result['distance']) || !is_numeric($result['distance'])) {
        error_log("Échec du calcul de distance pour #$orderId");
        return false;
    }

    return round($result['distance'], 2);
}

/**
 * Calcule les émissions de CO₂ en fonction du poids et de la distance
 */
function calculateCO2($orderModel, $orderId, $distance, $emissionFactor)
{
    $weight = $orderModel->getOrderWeight($orderId) ?: 1;
    return ($weight / 1000) * $distance * $emissionFactor / 1000;
}

/**
 * Récupère les commandes dont le poids a changé et doit être recalculé
 */
function handleGetOrdersWithChangedWeight($orderModel)
{
    $ordersToRecalculate = $orderModel->getOrdersWithChangedWeight();
    echo json_encode(['orders' => $ordersToRecalculate]);
}

/**
 * Nettoie une adresse pour supprimer les termes inutiles
 */
function cleanAddress($address)
{
    $address = strtoupper($address);
    $address = preg_replace('/\b(CS|CEDEX|BP|ZAC|ZI|IMPASSE|PARC|ACTIPOLE|STOCK)\b\s*\d*/', '', $address);
    $address = str_replace(["\n", "\r"], ' ', $address);
    return trim($address);
}
