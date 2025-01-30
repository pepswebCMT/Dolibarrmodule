<?php
class MyOrderModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Fonction pour récupérer les commandes par année
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
    //         $sql = "SELECT 
    //         c.rowid,
    //         c.ref,
    //         c.date_commande,
    //         cd.fk_product,
    //         cd.qty,
    //         p.weight,
    //         p.ref AS product_ref,
    //         s.rowid, 
    //         s.nom,
    //         s.address, 
    //         s.zip, 
    //         s.town,
    //         ps.fk_entrepot AS entrepot_id,
    //         e.ref AS entrepot_ref,
    //         ps.reel AS stock_quantity,
    //         ed.fk_entrepot AS expedition_entrepot_id,
    //         ee.ref AS expedition_entrepot_ref,
    //         CASE 
    //             WHEN ee.ref = 'STOCK-ACTILEV-GAILLON' THEN 'Route de la Garenne, 27600 Gaillon'
    //             WHEN ee.ref = 'STOCK-DEPOT' THEN 'Châteaubernard, 16100'
    //             ELSE NULL
    //         END AS transit_address,
    //         sf.rowid AS fournisseur_id,
    //         sf.nom AS fournisseur_name,
    //         sf.address AS fournisseur_address,
    //         sf.zip AS fournisseur_zip,
    //         sf.town AS fournisseur_town


    //     FROM 
    //         " . MAIN_DB_PREFIX . "commande c
    //     INNER JOIN 
    //         " . MAIN_DB_PREFIX . "commandedet cd ON c.rowid = cd.fk_commande
    //     INNER JOIN 
    //         " . MAIN_DB_PREFIX . "product p ON cd.fk_product = p.rowid
    //     INNER JOIN 
    //         " . MAIN_DB_PREFIX . "societe s ON s.rowid = c.fk_soc
    //           LEFT JOIN 
    //         " . MAIN_DB_PREFIX . "product_stock ps ON ps.fk_product = p.rowid
    //     LEFT JOIN 
    //         " . MAIN_DB_PREFIX . "entrepot e ON ps.fk_entrepot = e.rowid
    //     LEFT JOIN 
    //         " . MAIN_DB_PREFIX . "expeditiondet ed ON ed.fk_origin_line = cd.rowid
    //     LEFT JOIN 
    //         " . MAIN_DB_PREFIX . "entrepot ee ON ed.fk_entrepot = ee.rowid
    //     LEFT JOIN 
    //         " . MAIN_DB_PREFIX . "product_fournisseur_price pf ON pf.fk_product = p.rowid
    //     LEFT JOIN 
    //         " . MAIN_DB_PREFIX . "societe sf ON sf.rowid = pf.fk_soc
    //    WHERE 

    //    YEAR(c.date_commande) = 2024
    //     AND p.ref NOT LIKE 'frais_de_port%'
    //     AND p.ref NOT LIKE '%PrestaShipping%'
    //     ORDER BY 
    //         $sort $order
    //         LIMIT $limit OFFSET $offset";

    //     public function getOrdersByYear($year, $sort = 'date_commande', $order = 'ASC', $limit = 25, $offset = 0)
    //     {
    //         $allowedSortFields = ['commande_id', 'product_ref', 'qty', 'weight', 'date_commande'];
    //         $allowedOrder = ['ASC', 'DESC'];

    //         if (!in_array($sort, $allowedSortFields)) {
    //             $sort = 'date_commande';
    //         }
    //         if (!in_array($order, $allowedOrder)) {
    //             $order = 'ASC';
    //         }

    //         $sql = "SELECT 
    //     c.rowid,
    //     c.ref,
    //     c.date_commande,
    //     SUM(cd.qty) AS total_qty,
    //     SUM(p.weight * cd.qty) AS total_weight,
    //     GROUP_CONCAT(DISTINCT p.ref) AS product_refs,
    //     s.rowid AS societe_id, 
    //     s.nom,
    //     s.address, 
    //     s.zip, 
    //     s.town,
    //     MAX(ee.ref) AS expedition_entrepot_ref,
    //     MAX(CASE 
    //         WHEN ee.ref = 'STOCK-ACTILEV-GAILLON' THEN 'Route de la Garenne, 27600 Gaillon'
    //         WHEN ee.ref = 'STOCK-DEPOT' THEN 'Châteaubernard, 16100'
    //         ELSE NULL
    //     END) AS transit_address,
    //     MAX(sf.rowid) AS fournisseur_id,
    //     MAX(sf.nom) AS fournisseur_name,
    //     MAX(sf.address) AS fournisseur_address,
    //     MAX(sf.zip) AS fournisseur_zip,
    //     MAX(sf.town) AS fournisseur_town,
    //     dc.distance,
    //     dc.co2
    // FROM 
    //     llx_commande c
    // INNER JOIN 
    //     llx_commandedet cd ON c.rowid = cd.fk_commande
    // INNER JOIN 
    //     llx_product p ON cd.fk_product = p.rowid
    // INNER JOIN 
    //     llx_societe s ON s.rowid = c.fk_soc
    // LEFT JOIN 
    //     llx_expeditiondet ed ON ed.fk_origin_line = cd.rowid
    // LEFT JOIN 
    //     llx_entrepot ee ON ed.fk_entrepot = ee.rowid
    // LEFT JOIN 
    //     llx_product_fournisseur_price pf ON pf.fk_product = p.rowid
    // LEFT JOIN 
    //     llx_societe sf ON sf.rowid = pf.fk_soc
    // LEFT JOIN 
    //     llx_distance_cache dc ON 
    //         dc.client_address = CONCAT(s.address, ', ', s.zip, ' ', s.town) AND
    //         dc.supplier_address = CONCAT(sf.address, ', ', sf.zip, ' ', sf.town) AND
    //         dc.transit_address = CASE 
    //             WHEN ee.ref = 'STOCK-ACTILEV-GAILLON' THEN 'Route de la Garenne, 27600 Gaillon'
    //             WHEN ee.ref = 'STOCK-DEPOT' THEN 'Châteaubernard, 16100'
    //             ELSE NULL
    //         END
    // WHERE 
    //     YEAR(c.date_commande) = 2024
    //     AND p.ref NOT LIKE 'frais_de_port%'
    //     AND p.ref NOT LIKE '%PrestaShipping%'
    // GROUP BY 
    //     c.rowid, c.ref, c.date_commande, 
    //     s.rowid, s.nom, s.address, s.zip, s.town,
    //     dc.distance, dc.co2
    // ORDER BY 
    //     c.date_commande ASC
    // LIMIT 25 OFFSET 0";


    //         // $result = $this->db->query($this->db->build_sql_match($sql, [$year, $limit, $offset]));

    //         $result = $this->db->query($sql, array($year, $limit, $offset));
    //         if (!$result) {
    //             dol_print_error($this->db);
    //             return [];
    //         }

    //         $orders = [];
    //         while ($obj = $this->db->fetch_object($result)) {
    //             $orders[] = $obj;
    //         }

    //         return $orders;
    //     }

    public function getOrdersByYear($year, $sort = 'date_commande', $order = 'ASC', $limit = 25, $offset = 0)
    {
        $allowedSortFields = ['commande_id', 'product_ref', 'qty', 'weight', 'date_commande'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'date_commande';
        }
        if (!in_array($order, $allowedOrder)) {
            $order = 'ASC';
        }

        // Sécuriser year en tant qu'entier
        $year = (int)$year;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT 
            c.rowid,
            c.ref,
            c.date_commande,
            SUM(cd.qty) AS total_qty,
            SUM(p.weight * cd.qty) AS total_weight,
            GROUP_CONCAT(DISTINCT p.ref) AS product_refs,
            s.rowid AS societe_id, 
            s.nom,
            s.address, 
            s.zip, 
            s.town,
            MAX(ee.ref) AS expedition_entrepot_ref,
            MAX(CASE 
                WHEN ee.ref = 'STOCK-ACTILEV-GAILLON' THEN 'Route de la Garenne, 27600 Gaillon'
                WHEN ee.ref = 'STOCK-DEPOT' THEN 'Châteaubernard, 16100'
                ELSE NULL
            END) AS transit_address,
            MAX(sf.rowid) AS fournisseur_id,
            MAX(sf.nom) AS fournisseur_name,
            MAX(sf.address) AS fournisseur_address,
            MAX(sf.zip) AS fournisseur_zip,
            MAX(sf.town) AS fournisseur_town,
            dc.distance,
            dc.co2
        FROM 
            " . MAIN_DB_PREFIX . "commande c
        INNER JOIN 
            " . MAIN_DB_PREFIX . "commandedet cd ON c.rowid = cd.fk_commande
        INNER JOIN 
            " . MAIN_DB_PREFIX . "product p ON cd.fk_product = p.rowid
        INNER JOIN 
            " . MAIN_DB_PREFIX . "societe s ON s.rowid = c.fk_soc
        LEFT JOIN 
            " . MAIN_DB_PREFIX . "expeditiondet ed ON ed.fk_origin_line = cd.rowid
        LEFT JOIN 
            " . MAIN_DB_PREFIX . "entrepot ee ON ed.fk_entrepot = ee.rowid
        LEFT JOIN 
            " . MAIN_DB_PREFIX . "product_fournisseur_price pf ON pf.fk_product = p.rowid
        LEFT JOIN 
            " . MAIN_DB_PREFIX . "societe sf ON sf.rowid = pf.fk_soc
        LEFT JOIN 
            " . MAIN_DB_PREFIX . "distance_cache dc ON 
                  UPPER(TRIM(dc.client_address)) = UPPER(TRIM(CONCAT(s.address, ', ', s.zip, ' ', s.town))) AND
    UPPER(TRIM(dc.supplier_address)) = UPPER(TRIM(CONCAT(sf.address, ', ', sf.zip, ' ', sf.town))) AND
    UPPER(TRIM(dc.transit_address)) = UPPER(TRIM(CASE 
        WHEN ee.ref = 'STOCK-ACTILEV-GAILLON' THEN 'Route de la Garenne, 27600 Gaillon'
        WHEN ee.ref = 'STOCK-DEPOT' THEN 'Châteaubernard, 16100'
        ELSE NULL
    END))
        WHERE 
            YEAR(c.date_commande) = 2024
            AND p.ref NOT LIKE 'frais_de_port%'
            AND p.ref NOT LIKE '%PrestaShipping%'
        GROUP BY 
            c.rowid, c.ref, c.date_commande, 
            s.rowid, s.nom, s.address, s.zip, s.town,
            dc.distance, dc.co2
        ORDER BY 
            " . $sort . " " . $order . "
        LIMIT " . $limit . " OFFSET " . $offset;

        error_log("SQL Debug - Recherche dans cache : " . $sql);


        $result = $this->db->query($sql);
        if (!$result) {
            dol_print_error($this->db);
            return [];
        }

        if ($this->db->lasterror()) {
            error_log("Erreur SQL : " . $this->db->lasterror());
        }


        $orders = [];
        while ($obj = $this->db->fetch_object($result)) {
            // Assurons-nous que distance et co2 sont définis même si null
            $obj->distance = $obj->distance ?? null;
            $obj->co2 = $obj->co2 ?? null;
            $orders[] = $obj;
        }

        return $orders;
    }


    public function calculateDistance($clientAddress, $transitAddress, $supplierAddress = null)
    {
        $apiKey = 'NpDmJrHBrbbRpQMavTD4JqAdPcWArSgoolEdaQ9Z9W2dSv4l8OG9Ir4AjwHb9Z0V';


        error_log("Début du calcul de distance");
        error_log("Adresse client: " . $clientAddress);
        error_log("Adresse transit: " . $transitAddress);
        error_log("Adresse fournisseur: " . $supplierAddress);

        $clientCoords = $this->geocodeAddress($clientAddress, $apiKey);
        $addressToRouteCoords = $this->geocodeAddress($supplierAddress, $apiKey);


        // if (!$clientCoords || !$supplierCoords) {
        //     return [
        //         'distance' => null,s
        //         'error' => 'Coordonnées manquantes',
        //         'clientCoords' => $clientCoords,
        //         'supplierCoords' => $supplierCoords,
        //         'supplierAddress' => $supplierAddress
        //     ];
        // }


        // // $routingUrl = "https://api.jawg.io/routing/route/v1/car/{$clientCoords[0]},{$clientCoords[1]};{$supplierCoords[0]},{$supplierCoords[1]}?overview=false&access-token={$apiKey}";
        // $routingUrl = "https://api.jawg.io/routing/route/v1/car/{$clientCoords[0]},{$clientCoords[1]};{$transitCoords[0]},{$transitCoords[1]};{$supplierCoords[0]},{$supplierCoords[1]}?overview=false&access-token={$apiKey}";

        if ($supplierAddress === null) {
            $addressToRouteCoords = $this->geocodeAddress($transitAddress, $apiKey);

            if (!$clientCoords || !$addressToRouteCoords) {
                return [
                    'distance' => null,
                    'error' => 'Coordonnées manquantes'
                ];
            }

            $routingUrl = "https://api.jawg.io/routing/route/v1/car/{$clientCoords[0]},{$clientCoords[1]};{$addressToRouteCoords[0]},{$addressToRouteCoords[1]}?overview=false&access-token={$apiKey}";
        }
        // Cas avec trois adresses
        else {
            $transitCoords = $this->geocodeAddress($transitAddress, $apiKey);
            $supplierCoords = $this->geocodeAddress($supplierAddress, $apiKey);

            if (!$clientCoords || !$transitCoords || !$supplierCoords) {
                return [
                    'distance' => null,
                    'error' => 'Coordonnées manquantes'
                ];
            }


            $routingUrl = "https://api.jawg.io/routing/route/v1/car/{$clientCoords[0]},{$clientCoords[1]};{$transitCoords[0]},{$transitCoords[1]};{$supplierCoords[0]},{$supplierCoords[1]}?overview=false&access-token={$apiKey}";
            error_log("Coordinates: 
                Client Address: {$clientAddress},
    Client: {$clientCoords[0]}, {$clientCoords[1]}
    Transit: {$transitCoords[0]}, {$transitCoords[1]} 
    Supplier: {$supplierCoords[0]}, {$supplierCoords[1]}");
        }

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
            return $data['features'][0]['geometry']['coordinates'];
        }

        return [
            'error' => 'Erreur géocodage',
            'address' => $address,
            'url' => $url,
            'response' => $response,
            'data' => $data
        ];
    }

    public function getOrderWeight($orderId)
    {
        $sql = "SELECT SUM(p.weight * cd.qty) AS total_weight
                FROM " . MAIN_DB_PREFIX . "commandedet cd
                JOIN " . MAIN_DB_PREFIX . "product p ON cd.fk_product = p.rowid
                WHERE cd.fk_commande = " . intval($orderId);

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            error_log("Poids total pour la commande $orderId : " . ($obj->total_weight ?: "Valeur par défaut 1"));
            return $obj && $obj->total_weight > 0 ? $obj->total_weight : 1; // Valeur par défaut
        }

        error_log("Erreur lors de la récupération du poids pour la commande $orderId");
        return 1; // Valeur par défaut
    }

    public function storeDistance($clientAddress, $supplierAddress, $transitAddress, $distance, $co2)
    {
        error_log("Stockage distance : client={$clientAddress}, supplier={$supplierAddress}, transit={$transitAddress}, distance={$distance}, co2={$co2}");
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "distance_cache 
            (client_address, supplier_address, transit_address, distance, co2, date_creation) 
            VALUES (
                '" . $this->db->escape($clientAddress) . "', 
                '" . $this->db->escape($supplierAddress) . "', 
                '" . $this->db->escape($transitAddress) . "', 
                " . floatval($distance) . ", 
                " . floatval($co2) . ", 
                NOW()
            ) 
            ON DUPLICATE KEY UPDATE 
            distance = " . floatval($distance) . ", 
            co2 = " . floatval($co2) . ", 
            date_creation = NOW()";

        $this->db->query($sql);
        if ($this->db->lasterror()) {
            error_log("Erreur SQL stockage : " . $this->db->lasterror());
        }
    }

    // public function getCachedDistance($clientAddress, $supplierAddress, $transitAddress)
    // {
    //     $sql = "SELECT distance, co2 FROM " . MAIN_DB_PREFIX . "distance_cache 
    //         WHERE client_address = '" . $this->db->escape($clientAddress) . "'
    //         AND supplier_address = '" . $this->db->escape($supplierAddress) . "'
    //         AND transit_address = '" . $this->db->escape($transitAddress) . "'
    //         AND date_creation > DATE_SUB(NOW(), INTERVAL 6 MONTH)";

    //     error_log("Requête de cache : " . $sql);

    //     $resql = $this->db->query($sql);
    //     if ($resql) {
    //         $obj = $this->db->fetch_object($resql);
    //         error_log("Résultat de cache : " . print_r($obj, true)); // Ajout du log
    //         return $obj ? [
    //             'distance' => $obj->distance,
    //             'co2' => $obj->co2
    //         ] : null;
    //     }

    //     return null;
    // }
    public function getCachedDistance($clientAddress, $supplierAddress, $transitAddress)
    {
        $sql = "SELECT distance, co2 FROM " . MAIN_DB_PREFIX . "distance_cache 
            WHERE UPPER(TRIM(client_address)) = UPPER(TRIM('" . $this->db->escape($clientAddress) . "'))
            AND UPPER(TRIM(supplier_address)) = UPPER(TRIM('" . $this->db->escape($supplierAddress) . "'))
            AND (UPPER(TRIM(transit_address)) = UPPER(TRIM('" . $this->db->escape($transitAddress) . "')) OR transit_address IS NULL)
            AND date_creation > DATE_SUB(NOW(), INTERVAL 6 MONTH)";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);

            if ($obj && is_numeric($obj->distance) && is_numeric($obj->co2)) {
                return [
                    'distance' => $obj->distance,
                    'co2' => $obj->co2
                ];
            }
        }

        return null;
    }
}
