<?php

require '../../main.inc.php';

$socid = GETPOST('socid', 'int');

if (!$socid) {
    echo json_encode(['success' => false, 'error' => 'ID client manquant']);
    exit;
}

// Récupérer les extrafields de type 'societe'
$sql = "SELECT name, param FROM llx_extrafields WHERE elementtype = 'societe'";
$resql = $db->query($sql);

if (!$resql) {
    echo json_encode(['success' => false, 'error' => 'Erreur SQL lors de la récupération des extrafields']);
    exit;
}

$extrafields = [];
while ($obj = $db->fetch_object($resql)) {
    $extrafields[$obj->name] = unserialize($obj->param);
}

error_log("Extrafields trouvés: " . print_r($extrafields, true));


$sql = "SELECT * FROM llx_societe_extrafields WHERE fk_object = " . intval($socid);
error_log("SQL Exécutée: $sql");

$resql = $db->query($sql);

if (!$resql) {
    echo json_encode(['success' => false, 'error' => 'Erreur SQL lors de la récupération des valeurs extrafields']);
    exit;
}

$obj = $db->fetch_object($resql);

if (!$obj) {
    echo json_encode(['success' => false, 'error' => 'Aucune donnée trouvée pour cette société']);
    exit;
}

error_log("Valeurs extrafields récupérées: " . print_r($obj, true));


$data = [];

foreach ($extrafields as $key => $params) {
    $value = isset($obj->$key) ? $obj->$key : '';

    if (isset($params['options']) && is_array($params['options'])) {
        $data[$key] = $params['options'][$value] ?? $value;
    } else {
        $data[$key] = $value;
    }
}

error_log("Données finales envoyées: " . print_r($data, true));


header('Content-Type: application/json');
echo json_encode(['success' => true, 'extrafields' => $data]);
