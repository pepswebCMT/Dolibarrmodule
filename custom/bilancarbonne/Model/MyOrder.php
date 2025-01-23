<?php
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

    public function calculateDistance($clientAddress, $supplierAddress)
    {
        $apiKey = 'ksiu53cxlv9SAHTk4g9xULyC9rh0EdUNGFaaZyJaDRYp9lqBNGc4KTrHe7QMcX7c';

        $clientCoords = $this->geocodeAddress($clientAddress, $apiKey);
        $supplierCoords = $this->geocodeAddress($supplierAddress, $apiKey);

        if (!$clientCoords || !$supplierCoords) {
            return [
                'distance' => null,
                'error' => 'Coordonnées manquantes',
                'clientCoords' => $clientCoords,
                'supplierCoords' => $supplierCoords,
                'supplierAddress' => $supplierAddress
            ];
        }


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
            return [
                'distance' => $routingData['routes'][0]['distance'] / 1000
            ];
        }

        return [
            'distance' => null,
            'error' => 'Erreur API de routage',
            'routingData' => $routingData
        ];
    }



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

        // Ajoutez des logs pour le débogage
        if (!empty($data['features'][0]['geometry']['coordinates'])) {
            return $data['features'][0]['geometry']['coordinates']; // [longitude, latitude]
        }

        return [
            'error' => 'Erreur géocodage',
            'address' => $address,
            'url' => $url,
            'response' => $response,
            'data' => $data
        ];
    }
}
