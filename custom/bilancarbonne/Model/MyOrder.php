<?php
class MyOrderModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOrdersByYear($year, $sort = 'date_commande', $order = 'ASC')
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
                    s.town

                FROM 
                    " . MAIN_DB_PREFIX . "commande c
                INNER JOIN 
                    " . MAIN_DB_PREFIX . "commandedet cd ON c.rowid = cd.fk_commande
                INNER JOIN 
                    " . MAIN_DB_PREFIX . "product p ON cd.fk_product = p.rowid
                INNER JOIN 
                    " . MAIN_DB_PREFIX . "societe s ON s.rowid = c.fk_soc
                WHERE 
                    YEAR(c.date_commande) = " . $this->db->escape($year) . "
                ORDER BY 
                    $sort $order";

        // Exécution de la requête
        $resql = $this->db->query($sql);
        if (!$resql) {
            // Affiche une erreur détaillée
            dol_print_error($this->db, $sql);
            return [];
        }

        // Récupération des résultats
        $orders = [];
        while ($obj = $this->db->fetch_object($resql)) {
            $orders[] = $obj;
        }

        return $orders;
    }
    public function getAvailableYears()
    {
        $sql = "SELECT DISTINCT YEAR(date_commande) AS year FROM " . MAIN_DB_PREFIX . "commande ORDER BY year DESC";
        $resql = $this->db->query($sql);

        if (!$resql) {
            dol_print_error($this->db, $sql);
            return [];
        }

        $years = [];
        while ($obj = $this->db->fetch_object($resql)) {
            $years[] = $obj->year;
        }

        return $years;
    }
}
