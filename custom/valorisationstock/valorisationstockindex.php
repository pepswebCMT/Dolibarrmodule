<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       valorisationstock/valorisationstockindex.php
 *	\ingroup    valorisationstock
 *	\brief      Page de valorisation de stock avec tri par valeur décroissante
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';

// Load translation files required by the page
$langs->loadLangs(array("valorisationstock@valorisationstock", "products", "stocks"));

$action = GETPOST('action', 'aZ09');
$search_ref = GETPOST('search_ref', 'alpha');
$search_label = GETPOST('search_label', 'alpha');
$search_warehouse = GETPOST('search_warehouse', 'int');
$search_date = dol_mktime(0, 0, 0, GETPOST('search_datemonth', 'int'), GETPOST('search_dateday', 'int'), GETPOST('search_dateyear', 'int'));

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : 250;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1) {
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Construction de la chaîne de paramètres pour préserver les filtres lors du tri
$param = '';
if ($search_ref) $param .= '&search_ref=' . urlencode($search_ref);
if ($search_label) $param .= '&search_label=' . urlencode($search_label);
if ($search_warehouse > 0) $param .= '&search_warehouse=' . (int)$search_warehouse;
if ($limit != 250) $param .= '&limit=' . (int)$limit;
// IMPORTANT : ajouter les paramètres de date pour qu'ils soient préservés lors du tri
if (GETPOST('search_dateday', 'int')) $param .= '&search_dateday=' . (int)GETPOST('search_dateday', 'int');
if (GETPOST('search_datemonth', 'int')) $param .= '&search_datemonth=' . (int)GETPOST('search_datemonth', 'int');
if (GETPOST('search_dateyear', 'int')) $param .= '&search_dateyear=' . (int)GETPOST('search_dateyear', 'int');

// Security check - Protection if external user
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

/**
 * Fonction pour récupérer le prix unitaire de la dernière facture fournisseur pour un produit
 * @param object $db Database handler
 * @param int $product_id ID du produit
 * @return float Prix unitaire de la dernière facture fournisseur
 */
function getLastSupplierInvoicePrice($db, $product_id)
{
	$sql = "SELECT ffd.pu_ht, ff.datef";
	$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn as ff";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "facture_fourn_det as ffd ON ff.rowid = ffd.fk_facture_fourn";
	$sql .= " WHERE ffd.fk_product = " . ((int) $product_id);
	$sql .= " AND ff.fk_statut >= 1"; // Facture validée
	$sql .= " ORDER BY ff.datef DESC, ff.rowid DESC";
	$sql .= " LIMIT 1";

	$resql = $db->query($sql);
	if ($resql && $db->num_rows($resql) > 0) {
		$obj = $db->fetch_object($resql);
		$db->free($resql);
		return (float) $obj->pu_ht;
	}

	return 0;
}

/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
	$massaction = '';
}

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);
$productstatic = new Product($db);
$warehousestatic = new Entrepot($db);

$title = $langs->trans("Valorisation Stock");
llxHeader("", $title, '', '', 0, 0, '', '', '', 'mod-valorisationstock page-index');

print load_fiche_titre($title, '', 'valorisationstock.png@valorisationstock');

// ====== SECTION FILTRES SÉPARÉE ======
print '<div class="fichecenter">';
print '<div class="fichehalfleft">';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<th colspan="2">' . $langs->trans("SearchFilters") . '</th>';
print '</tr>';

// Formulaire de recherche
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="search">';

print '<tr class="oddeven">';
print '<td><label for="limit">' . $langs->trans("Limit") . '</label></td>';
print '<td>';
print '<select name="limit" id="limit" class="flat maxwidth200">';
$limits = array(100, 250, 500, 1000);
foreach ($limits as $limitval) {
	$selected = ($limit == $limitval) ? ' selected="selected"' : '';
	print '<option value="' . $limitval . '"' . $selected . '>' . $limitval . ' ' . $langs->trans("elements") . '</option>';
}
print '</select>';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td><label for="search_ref">' . $langs->trans("Ref") . '</label></td>';
print '<td><input class="flat maxwidth200" type="text" id="search_ref" name="search_ref" value="' . dol_escape_htmltag($search_ref) . '"></td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td><label for="search_label">' . $langs->trans("Label") . '</label></td>';
print '<td><input class="flat maxwidth200" type="text" id="search_label" name="search_label" value="' . dol_escape_htmltag($search_label) . '"></td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td><label for="search_warehouse">' . $langs->trans("Warehouse") . '</label></td>';
print '<td>' . $formproduct->selectWarehouses($search_warehouse, 'search_warehouse', '', 1, 0, 0, '', 0, 0, array(), 'maxwidth200') . '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td><label for="search_date">' . $langs->trans("Date") . ' <span style="color: red;">*</span></label></td>';
print '<td>' . $form->selectDate($search_date, 'search_date', 0, 0, 1, "search_date", 1, 1) . '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td colspan="2" class="center">';
print '<input type="submit" class="button buttongen" value="' . $langs->trans("Search") . '" name="button_search">';
print ' &nbsp; ';
print '<input type="submit" class="button buttongen" value="' . $langs->trans("Reset") . '" name="button_removefilter">';
print '</td>';
print '</tr>';

print '</form>';

print '</table>';
print '</div>';

print '</div>';

print '<div class="clearboth"></div>';
print '</div>';

// Réinitialiser les filtres si demandé
if (GETPOST('button_removefilter', 'alpha')) {
	$search_ref = '';
	$search_label = '';
	$search_warehouse = 0;
	$search_date = '';
	$action = '';
}

// ====== SECTION TABLEAU SÉPARÉE ======
// N'afficher le tableau que si une date est sélectionnée
if (!empty($search_date) && (GETPOST('button_search', 'alpha') || $action == 'search' || (!empty($search_ref) || !empty($search_label) || !empty($search_warehouse)))) {

	print '<br><hr><br>';
	print '<div class="div-table-responsive-no-min">';
	print '<table class="liste centpercent">';

	// Ligne de titres
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("Ref"), $_SERVER["PHP_SELF"], "p.ref", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Label"), $_SERVER["PHP_SELF"], "p.label", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Stock à date"), $_SERVER["PHP_SELF"], "", "", $param, "center", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Stock actuel"), $_SERVER["PHP_SELF"], "stock_actuel", "", $param, "center", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Dernier prix fournisseur"), $_SERVER["PHP_SELF"], "supplier_price", "", $param, "right", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Valuation"), $_SERVER["PHP_SELF"], "valuation", "", $param, "right", $sortfield, $sortorder);
	print_liste_field_titre('', $_SERVER["PHP_SELF"], "", "", $param, "", $sortfield, $sortorder, 'center maxwidthsearch ');
	print "</tr>\n";

	// Requête pour récupérer les données
	$sql = "SELECT DISTINCT p.rowid, p.ref, p.label, p.price, p.price_ttc, p.tva_tx,";
	$sql .= " ps.fk_entrepot as warehouse_id, ps.reel as stock_reel,";
	$sql .= " e.ref as warehouse_label";
	$sql .= " FROM " . MAIN_DB_PREFIX . "product as p";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON p.rowid = ps.fk_product";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "entrepot as e ON ps.fk_entrepot = e.rowid";
	$sql .= " WHERE p.entity IN (" . getEntity('product') . ")";
	$sql .= " AND p.fk_product_type = 0"; // Produits uniquement (pas services)
	$sql .= " AND ps.fk_entrepot IS NOT NULL"; // Seulement les produits qui ont des stocks

	// Filtres
	if ($search_ref) {
		$sql .= " AND p.ref LIKE '%" . addslashes($search_ref) . "%'";
	}
	if ($search_label) {
		$sql .= " AND p.label LIKE '%" . addslashes($search_label) . "%'";
	}
	if ($search_warehouse > 0) {
		$sql .= " AND ps.fk_entrepot = " . ((int) $search_warehouse);
	}

	// Limite 
	if (!empty($limit)) {
		$sql .= $db->plimit($limit + 1, $offset);
	}

	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		$i = 0;

		$arrayofvalues = array();

		while ($i < $num) {
			$obj = $db->fetch_object($resql);

			// Stock actuel depuis product_stock
			$stock_actuel = $obj->stock_reel ? $obj->stock_reel : 0;

			// Calculer le stock à la date donnée
			$stock_date = $stock_actuel; // Par défaut = stock actuel

			if (!empty($search_date) && $obj->warehouse_id) {
				// Calcul basé sur les mouvements de stock
				$sql_movements_after = "SELECT SUM(sm.value) as movements_after";
				$sql_movements_after .= " FROM " . MAIN_DB_PREFIX . "stock_mouvement as sm";
				$sql_movements_after .= " WHERE sm.fk_product = " . ((int) $obj->rowid);
				$sql_movements_after .= " AND sm.fk_entrepot = " . ((int) $obj->warehouse_id);
				$sql_movements_after .= " AND sm.datem > '" . $db->idate($search_date) . "'";

				$resql_movements = $db->query($sql_movements_after);
				if ($resql_movements) {
					$obj_movements = $db->fetch_object($resql_movements);
					$movements_after_date = $obj_movements->movements_after ? $obj_movements->movements_after : 0;
					$stock_date = $stock_actuel - $movements_after_date;
					$db->free($resql_movements);
				} else {
					$stock_date = 0;
				}

				// S'assurer que le stock n'est jamais négatif
				if ($stock_date < 0) {
					$stock_date = 0;
				}
			}

			// Récupérer le prix unitaire de la dernière facture fournisseur
			$supplier_unit_price = getLastSupplierInvoicePrice($db, $obj->rowid);

			// Si aucun prix fournisseur trouvé, utiliser le prix de vente du produit
			if ($supplier_unit_price == 0) {
				$supplier_unit_price = $obj->price ? $obj->price : 0;
			}

			// Calculer la valorisation avec le prix fournisseur
			$valuation = $supplier_unit_price * $stock_date;

			// Stocker les données avec la valorisation pour le tri
			$arrayofvalues[] = array(
				'obj' => $obj,
				'stock_date' => $stock_date,
				'stock_actuel' => $stock_actuel,
				'supplier_unit_price' => $supplier_unit_price,
				'valuation' => $valuation
			);

			$i++;
		}

		// Appliquer le tri selon le champ demandé
		if (!empty($sortfield)) {
			switch ($sortfield) {
				case 'p.ref':
					usort($arrayofvalues, function ($a, $b) use ($sortorder) {
						$result = strcmp($a['obj']->ref, $b['obj']->ref);
						return ($sortorder === 'DESC') ? -$result : $result;
					});
					break;
				case 'p.label':
					usort($arrayofvalues, function ($a, $b) use ($sortorder) {
						$result = strcmp($a['obj']->label, $b['obj']->label);
						return ($sortorder === 'DESC') ? -$result : $result;
					});
					break;
				case 'e.libelle':
					// Tri par entrepôt supprimé car colonne supprimée
					break;
				case 'stock_actuel':
					usort($arrayofvalues, function ($a, $b) use ($sortorder) {
						$result = $a['stock_actuel'] <=> $b['stock_actuel'];
						return ($sortorder === 'DESC') ? -$result : $result;
					});
					break;
				case 'supplier_price':
					usort($arrayofvalues, function ($a, $b) use ($sortorder) {
						$result = $a['supplier_unit_price'] <=> $b['supplier_unit_price'];
						return ($sortorder === 'DESC') ? -$result : $result;
					});
					break;
				case 'valuation':
					usort($arrayofvalues, function ($a, $b) use ($sortorder) {
						$result = $a['valuation'] <=> $b['valuation'];
						return ($sortorder === 'DESC') ? -$result : $result;
					});
					break;
				default:
					// Tri par valorisation décroissante par défaut
					usort($arrayofvalues, function ($a, $b) {
						return $b['valuation'] <=> $a['valuation'];
					});
					break;
			}
		} else {
			// Trier par valorisation décroissante par défaut
			usort($arrayofvalues, function ($a, $b) {
				return $b['valuation'] <=> $a['valuation'];
			});
		}

		// Afficher les résultats
		$total_valuation = 0;
		foreach ($arrayofvalues as $data) {
			$obj = $data['obj'];
			$stock_date = $data['stock_date'];
			$stock_actuel = $data['stock_actuel'];
			$supplier_unit_price = $data['supplier_unit_price'];
			$valuation = $data['valuation'];

			$total_valuation += $valuation;

			print '<tr class="oddeven">';

			// Référence
			$productstatic->id = $obj->rowid;
			$productstatic->ref = $obj->ref;
			$productstatic->label = $obj->label;
			print '<td class="nowraponall">';
			print $productstatic->getNomUrl(1);
			print '</td>';

			// Libellé
			print '<td class="tdoverflowmax200" title="' . dol_escape_htmltag($obj->label) . '">';
			print dol_escape_htmltag($obj->label);
			print '</td>';

			// Stock à date
			print '<td class="center">';
			print $stock_date;
			print '</td>';

			// Stock actuel
			print '<td class="center">';
			print '<strong>' . $stock_actuel . '</strong>';
			print '</td>';

			// Prix unitaire fournisseur
			print '<td class="right">';
			if ($supplier_unit_price > 0) {
				print '<strong>' . price($supplier_unit_price) . '</strong>';
			} else {
				print '<span class="opacitymedium">' . $langs->trans("N/A") . '</span>';
			}
			print '</td>';

			// Valorisation
			print '<td class="right">';
			if ($valuation > 0) {
				print '<strong>' . price($valuation) . '</strong>';
			} else {
				print price($valuation);
			}
			print '</td>';

			// Actions
			print '<td class="center">';
			print '</td>';

			print "</tr>\n";
		}

		// Ligne de total
		if (count($arrayofvalues) > 0) {
			print '<tr class="liste_total">';
			print '<td></td>';
			print '<td></td>';
			print '<td></td>';
			print '<td></td>';
			print '<td class="right"><strong>' . $langs->trans("Total") . '</strong></td>';
			print '<td class="right"><strong>' . price($total_valuation) . '</strong></td>';
			print '<td></td>';
			print '</tr>';
		}

		if (count($arrayofvalues) == 0) {
			print '<tr class="oddeven"><td colspan="7" class="opacitymedium">' . $langs->trans("NoRecordFound") . '</td></tr>';
		}

		$db->free($resql);
	} else {
		dol_print_error($db);
	}

	print "</table>";

	// Pagination
	print '<div class="pagination" style="text-align:center; margin-top: 10px;">';

	$url = $_SERVER["PHP_SELF"];
	$params = array();
	if ($search_ref) $params[] = 'search_ref=' . urlencode($search_ref);
	if ($search_label) $params[] = 'search_label=' . urlencode($search_label);
	if ($search_warehouse) $params[] = 'search_warehouse=' . (int)$search_warehouse;
	if ($sortfield) $params[] = 'sortfield=' . urlencode($sortfield);
	if ($sortorder) $params[] = 'sortorder=' . urlencode($sortorder);
	if (GETPOST('search_dateday', 'int')) $params[] = 'search_dateday=' . (int)GETPOST('search_dateday', 'int');
	if (GETPOST('search_datemonth', 'int')) $params[] = 'search_datemonth=' . (int)GETPOST('search_datemonth', 'int');
	if (GETPOST('search_dateyear', 'int')) $params[] = 'search_dateyear=' . (int)GETPOST('search_dateyear', 'int');
	$params[] = 'action=search'; // Important pour maintenir l'affichage du tableau

	$baseurl = $url . (count($params) ? '?' . implode('&', $params) : '');

	// Page précédente
	if ($page > 0) {
		print '<a class="button" href="' . $baseurl . '&page=' . $pageprev . '">&laquo; Page précédente</a> ';
	}

	// Page suivante
	if ($num > $limit) {
		print '<a class="button" href="' . $baseurl . '&page=' . $pagenext . '">Page suivante &raquo;</a>';
	}

	print '</div>';
	print '</div>';
} else {
	// Message d'information si aucune date n'est sélectionnée
	print '<br><div class="info clearboth">';
	print '<div class="center">';
	print '<span class="opacitymedium">';
	print $langs->trans("Please select a date to display stock valuation results");
	print '</span>';
	print '</div>';
	print '</div>';
}

// CSS personnalisé
print '<style>
.info-box {
    background-color: #f9f9f9;
    padding: 10px;
    border-left: 4px solid #007cbb;
    margin: 5px 0;
}

.pagination a.button {
    display: inline-block;
    padding: 6px 12px;
    margin: 5px;
    background-color: #f0f0f0;
    border: 1px solid #999;
    text-decoration: none;
    color: #000;
}

.pagination a.button:hover {
    background-color: #e0e0e0;
}

.fichecenter .fichehalfleft,
.fichecenter .fichehalfright {
    width: 48%;
}

.fichecenter .fichehalfleft {
    float: left;
}

.fichecenter .fichehalfright {
    float: right;
}

.clearboth {
    clear: both;
}

.info {
    background-color: rgba(217, 237, 247, 0.3);
    border: 1px solid #bee5eb;
    padding: 15px;
    margin: 10px 0;
    border-radius: 4px;
}
</style>';

// End of page
llxFooter();
$db->close();
