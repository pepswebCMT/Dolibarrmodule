<?php
// class MyOrderModel
// {
//     private $db;

//     public function __construct($db)
//     {
//         $this->db = $db;
//     }

//     public function getOrdersByYear($year, $sort = 'date_commande', $order = 'ASC', $limit = 25, $offset = 0)
//     {
//         $allowedSortFields = ['commande_id', 'product_ref', 'qty', 'weight', 'date_commande'];
//         $allowedOrder = ['ASC', 'DESC'];

//         // Validation stricte des paramètres
//         if (!in_array($sort, $allowedSortFields)) {
//             $sort = 'date_commande';
//         }
//         if (!in_array($order, $allowedOrder)) {
//             $order = 'ASC';
//         }

//         // Construction de la requête SQL avec échappement pour $year
// $sql = "SELECT 
//             c.rowid,
//             c.ref,
//             c.date_commande,
//             cd.fk_product,
//             cd.qty,
//             p.weight,
//             p.ref AS product_ref,
//             s.rowid, 
//             s.nom,
//             s.address, 
//             s.zip, 
//             s.town,
//             ps.fk_entrepot AS entrepot_id,
//             e.ref AS entrepot_ref,
//             ps.reel AS stock_quantity,
//             ed.fk_entrepot AS expedition_entrepot_id,
//             ee.ref AS expedition_entrepot_ref,
//             sf.rowid AS fournisseur_id,
//             sf.nom AS fournisseur_name,
//             sf.address AS fournisseur_address,
//             sf.zip AS fournisseur_zip,
//             sf.town AS fournisseur_town


//         FROM 
//             " . MAIN_DB_PREFIX . "commande c
//         INNER JOIN 
//             " . MAIN_DB_PREFIX . "commandedet cd ON c.rowid = cd.fk_commande
//         INNER JOIN 
//             " . MAIN_DB_PREFIX . "product p ON cd.fk_product = p.rowid
//         INNER JOIN 
//             " . MAIN_DB_PREFIX . "societe s ON s.rowid = c.fk_soc
//               LEFT JOIN 
//             " . MAIN_DB_PREFIX . "product_stock ps ON ps.fk_product = p.rowid
//         LEFT JOIN 
//             " . MAIN_DB_PREFIX . "entrepot e ON ps.fk_entrepot = e.rowid
//         LEFT JOIN 
//             " . MAIN_DB_PREFIX . "expeditiondet ed ON ed.fk_origin_line = cd.rowid
//         LEFT JOIN 
//             " . MAIN_DB_PREFIX . "entrepot ee ON ed.fk_entrepot = ee.rowid
//         LEFT JOIN 
//             " . MAIN_DB_PREFIX . "product_fournisseur_price pf ON pf.fk_product = p.rowid
//         LEFT JOIN 
//             " . MAIN_DB_PREFIX . "societe sf ON sf.rowid = pf.fk_soc
//        WHERE 
//             YEAR(c.date_commande) = " . $this->db->escape($year) . " 
//         AND p.ref NOT LIKE 'frais_de_port%'
//         AND p.ref NOT LIKE '%PrestaShipping%'
//         ORDER BY 
//             $sort $order
//             LIMIT $limit OFFSET $offset";




//         // Exécution de la requête
//         $resql = $this->db->query($sql);
//         if (!$resql) {
//             // Affiche une erreur détaillée
//             dol_print_error($this->db, $sql);
//             return [];
//         }

//         // Récupération des résultats
//         $orders = [];
//         while ($obj = $this->db->fetch_object($resql)) {
//             $orders[] = $obj;
//         }

//         return $orders;
//     }

//     public function getAvailableYears()
//     {
//         $sql = "SELECT DISTINCT YEAR(date_commande) AS year FROM " . MAIN_DB_PREFIX . "commande ORDER BY year DESC";
//         $resql = $this->db->query($sql);

//         if (!$resql) {
//             dol_print_error($this->db, $sql);
//             return [];
//         }

//         $years = [];
//         while ($obj = $this->db->fetch_object($resql)) {
//             $years[] = $obj->year;
//         }

//         return $years;
//     }

//     // public function calculateDistance($startCoords, $endCoords)
//     // {
//     //     $apiKey = '5b3ce3597851110001cf6248dfab9e725a7f411dbc05cf5da9543949';
//     //     $url = "https://api.openrouteservice.org/v2/matrix/driving-car";

//     //     // Préparer les données brutes
//     //     $coordinates = [
//     //         [$startCoords['lon'], $startCoords['lat']],
//     //         [$endCoords['lon'], $endCoords['lat']]
//     //     ];

//     //     $payload = json_encode([
//     //         "locations" => $coordinates,
//     //         "metrics" => ["distance"]
//     //     ]);

//     //     $options = [
//     //         "http" => [
//     //             "header" => "Authorization: $apiKey\r\nContent-Type: application/json\r\n",
//     //             "method" => "POST",
//     //             "content" => $payload
//     //         ]
//     //     ];

//     //     $context = stream_context_create($options);

//     //     $response = file_get_contents($url, false, $context);
//     //     $data = json_decode($response, true);

//     //     if (!empty($data['distances'][0][1])) {
//     //         return $data['distances'][0][1] / 1000; // Distance en kilomètres
//     //     }

//     //     error_log("Erreur dans la réponse OpenRouteService : " . print_r($data, true));
//     //     return null;
//     // }
//     public function calculateDistance($clientAddress, $supplierAddress)
//     {
//         $apiKey = 'ksiu53cxlv9SAHTk4g9xULyC9rh0EdUNGFaaZyJaDRYp9lqBNGc4KTrHe7QMcX7c'; // Remplacez par votre clé API Jawg.io

//         // Encodage des adresses pour l'URL
//         $encodedClientAddress = urlencode($clientAddress);
//         $encodedSupplierAddress = urlencode($supplierAddress);

//         // URL de l'API Jawg pour le géocodage
//         $geocodingUrl = "https://api.jawg.io/jawg-geocoding/search?text={$encodedClientAddress}&access-token={$apiKey}";
//         $geocodingSupplierUrl = "https://api.jawg.io/jawg-geocoding/search?text={$encodedSupplierAddress}&access-token={$apiKey}";

//         // Options pour la requête HTTP
//         $options = [
//             'http' => [
//                 'method' => 'GET',
//                 'header' => "User-Agent: DolibarrApp/1.0\r\n"
//             ]
//         ];
//         $context = stream_context_create($options);

//         // Récupération des coordonnées du client
//         $clientGeocodeResponse = file_get_contents($geocodingUrl, false, $context);
//         $clientGeocodeData = json_decode($clientGeocodeResponse, true);

//         // Récupération des coordonnées du fournisseur
//         $supplierGeocodeResponse = file_get_contents($geocodingSupplierUrl, false, $context);
//         $supplierGeocodeData = json_decode($supplierGeocodeResponse, true);

//         // Vérification des données géocodées
//         if (
//             empty($clientGeocodeData['features']) ||
//             empty($supplierGeocodeData['features'])
//         ) {
//             error_log("Géocodage impossible pour l'adresse : client ou fournisseur");
//             return null;
//         }

//         // Extraction des coordonnées
//         $clientCoords = $clientGeocodeData['features'][0]['geometry']['coordinates'];
//         $supplierCoords = $supplierGeocodeData['features'][0]['geometry']['coordinates'];

//         // URL de l'API de routage Jawg
//         $routingUrl = "https://api.jawg.io/routing/route/v1/driving/{$clientCoords[0]},{$clientCoords[1]};{$supplierCoords[0]},{$supplierCoords[1]}?access-token={$apiKey}";

//         // Requête de routage
//         $routingResponse = file_get_contents($routingUrl, false, $context);
//         $routingData = json_decode($routingResponse, true);

//         // Vérification des données de routage
//         if (
//             empty($routingData['routes']) ||
//             !isset($routingData['routes'][0]['distance'])
//         ) {
//             error_log("Impossible de calculer l'itinéraire");
//             return null;
//         }

//         // Retourne la distance en km
//         return $routingData['routes'][0]['distance'] / 1000;

//         error_log("Réponse client : " . print_r($clientGeocodeData, true));
//         error_log("Réponse fournisseur : " . print_r($supplierGeocodeData, true));
//     }

//     // private function getCoordinates($address)
//     // {
//     //     // Géocodage avec OpenStreetMap Nominatim ou une autre API
//     //     $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($address) . "&format=json&limit=1";

//     //     $response = file_get_contents($url, false, stream_context_create([
//     //         'http' => [
//     //             'header' => "User-Agent: VotreApplication/1.0 (contact@exemple.com)\r\n"
//     //         ]
//     //     ]));

//     //     $data = json_decode($response, true);

//     //     if (!empty($data)) {
//     //         return ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']];
//     //     }

//     //     error_log("Impossible de trouver les coordonnées pour l'adresse : " . $address);
//     //     return null;
//     // }
// }

class MyOrderModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Fonction pour récupérer les commandes par année
    public function getOrdersByYear($year, $sort = 'date_commande', $order = 'ASC', $limit = 25, $offset = 0)
    {
        $allowedSortFields = ['commande_id', 'product_ref', 'qty', 'weight', 'date_commande'];
        $allowedOrder = ['ASC', 'DESC'];

        // Validation stricte des paramètres
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'date_commande';
        }
        if (!in_array($order, $allowedOrder)) {
            $order = 'ASC';
        }

        // Construction de la requête SQL avec échappement pour $year
        $sql = "SELECT 
        c.rowid,
        c.ref,
        c.date_commande,
        cd.fk_product,
        cd.qty,
        p.weight,
        p.ref AS product_ref,
        s.rowid, 
        s.nom,
        s.address, 
        s.zip, 
        s.town,
        ps.fk_entrepot AS entrepot_id,
        e.ref AS entrepot_ref,
        ps.reel AS stock_quantity,
        ed.fk_entrepot AS expedition_entrepot_id,
        ee.ref AS expedition_entrepot_ref,
        sf.rowid AS fournisseur_id,
        sf.nom AS fournisseur_name,
        sf.address AS fournisseur_address,
        sf.zip AS fournisseur_zip,
        sf.town AS fournisseur_town


    FROM 
        " . MAIN_DB_PREFIX . "commande c
    INNER JOIN 
        " . MAIN_DB_PREFIX . "commandedet cd ON c.rowid = cd.fk_commande
    INNER JOIN 
        " . MAIN_DB_PREFIX . "product p ON cd.fk_product = p.rowid
    INNER JOIN 
        " . MAIN_DB_PREFIX . "societe s ON s.rowid = c.fk_soc
          LEFT JOIN 
        " . MAIN_DB_PREFIX . "product_stock ps ON ps.fk_product = p.rowid
    LEFT JOIN 
        " . MAIN_DB_PREFIX . "entrepot e ON ps.fk_entrepot = e.rowid
    LEFT JOIN 
        " . MAIN_DB_PREFIX . "expeditiondet ed ON ed.fk_origin_line = cd.rowid
    LEFT JOIN 
        " . MAIN_DB_PREFIX . "entrepot ee ON ed.fk_entrepot = ee.rowid
    LEFT JOIN 
        " . MAIN_DB_PREFIX . "product_fournisseur_price pf ON pf.fk_product = p.rowid
    LEFT JOIN 
        " . MAIN_DB_PREFIX . "societe sf ON sf.rowid = pf.fk_soc
   WHERE 
        YEAR(c.date_commande) = " . $this->db->escape($year) . " 
    AND p.ref NOT LIKE 'frais_de_port%'
    AND p.ref NOT LIKE '%PrestaShipping%'
    ORDER BY 
        $sort $order
        LIMIT $limit OFFSET $offset";

        // Exécution de la requête
        $resql = $this->db->query($sql);
        if (!$resql) {
            dol_print_error($this->db, $sql);
            return [];
        }

        $orders = [];
        while ($obj = $this->db->fetch_object($resql)) {
            $orders[] = $obj;
        }

        return $orders;
    }

    // Fonction pour calculer la distance entre deux adresses
    public function calculateDistance($clientAddress, $supplierAddress)
    {
        $apiKey = 'ksiu53cxlv9SAHTk4g9xULyC9rh0EdUNGFaaZyJaDRYp9lqBNGc4KTrHe7QMcX7c';

        // Géocodage des adresses
        $clientCoords = $this->geocodeAddress($clientAddress, $apiKey);
        $supplierCoords = $this->geocodeAddress($supplierAddress, $apiKey);

        if (!$clientCoords || !$supplierCoords) {
            return null; // Impossible de calculer la distance
        }

        // Appel à l'API de routage
        $routingUrl = "https://api.jawg.io/routing/route/v1/car/{$clientCoords[0]},{$clientCoords[1]};{$supplierCoords[0]},{$supplierCoords[1]}?overview=false&access-token={$apiKey}";

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: DolibarrApp/1.0\r\n"
            ]
        ];
        $context = stream_context_create($options);

        $response = file_get_contents($routingUrl, false, $context);
        $routingData = json_decode($response, true);

        if (!empty($routingData['routes'][0]['distance'])) {
            return $routingData['routes'][0]['distance'] / 1000; // Distance en kilomètres
        }

        return null;
    }

    // Fonction pour géocoder une adresse
    private function geocodeAddress($address, $apiKey)
    {
        $encodedAddress = urlencode($address);
        $url = "https://api.jawg.io/places/v1/search?text={$encodedAddress}&access-token={$apiKey}";

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: DolibarrApp/1.0\r\n"
            ]
        ];
        $context = stream_context_create($options);

        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        if (!empty($data['features'][0]['geometry']['coordinates'])) {
            return $data['features'][0]['geometry']['coordinates']; // [longitude, latitude]
        }

        return null;
    }
}
