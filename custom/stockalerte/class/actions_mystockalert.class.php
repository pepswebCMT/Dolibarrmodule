<?php

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';

$langs->load('products');

if (!$user->rights->mystockalert->read) {
    accessforbidden();
}

llxHeader('', 'Produits en alerte de stock');

print load_fiche_titre('Produits en alerte de stock');

$sql = "SELECT p.rowid, p.ref, p.label, p.seuil_stock_alerte, ps.reel";
$sql .= " FROM " . MAIN_DB_PREFIX . "product as p";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON ps.fk_product = p.rowid";
$sql .= " WHERE p.tosell = 1";
$sql .= " AND p.seuil_stock_alerte IS NOT NULL";
$sql .= " AND ps.reel <= p.seuil_stock_alerte";
$sql .= " ORDER BY ps.reel ASC";

$resql = $db->query($sql);

if ($resql) {
    print '<table class="liste centpercent">';
    print '<tr class="liste_titre">';
    print '<th>Réf.</th><th>Nom</th><th>Stock</th><th>Seuil alerte</th>';
    print '</tr>';

    while ($obj = $db->fetch_object($resql)) {
        print '<tr>';
        print '<td><a href="' . DOL_URL_ROOT . '/product/card.php?id=' . $obj->rowid . '">' . dol_escape_htmltag($obj->ref) . '</a></td>';
        print '<td>' . dol_escape_htmltag($obj->label) . '</td>';
        print '<td>' . $obj->reel . '</td>';
        print '<td>' . $obj->seuil_stock_alerte . '</td>';
        print '</tr>';
    }

    print '</table>';
} else {
    dol_print_error($db);
}

llxFooter();
