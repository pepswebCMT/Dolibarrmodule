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
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("valorisationstock@valorisationstock", "products", "stocks"));

$action = GETPOST('action', 'aZ09');
$search_ref = GETPOST('search_ref', 'alpha');
$search_label = GETPOST('search_label', 'alpha');
$search_warehouse = GETPOST('search_warehouse', 'int');
$search_date = dol_mktime(0, 0, 0, GETPOST('search_datemonth', 'int'), GETPOST('search_dateday', 'int'), GETPOST('search_dateyear', 'int'));

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : 3000;
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
if ($limit != 500) $param .= '&limit=' . (int)$limit;

// Ajout correct de la date de valorisation
if (!empty($search_date)) {
	$param .= '&search_dateday=' . (int) dol_print_date($search_date, '%d');
	$param .= '&search_datemonth=' . (int) dol_print_date($search_date, '%m');
	$param .= '&search_dateyear=' . (int) dol_print_date($search_date, '%Y');
}

// Conserver le tri courant
if ($sortfield) $param .= '&sortfield=' . urlencode($sortfield);
if ($sortorder) $param .= '&sortorder=' . urlencode($sortorder);

// Forcer l'affichage des résultats
$param .= '&action=search';

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

/**
 * Fonction pour obtenir les données de valorisation
 * @param object $db Database handler
 * @param string $search_ref Référence produit
 * @param string $search_label Libellé produit
 * @param int $search_warehouse ID entrepôt
 * @param int $search_date Date de valorisation
 * @param int $limit Limite d'enregistrements
 * @param int $offset Offset pour pagination
 * @param string $sortfield Champ de tri
 * @param string $sortorder Ordre de tri
 * @return array Tableau des données
 */
function getValorisationData($db, $search_ref, $search_label, $search_warehouse, $search_date, $limit = 0, $offset = 0, $sortfield = '', $sortorder = '')
{
	if ($search_warehouse > 0) {
		// CAS 1: Entrepôt spécifique - logique actuelle
		return getValorisationDataForWarehouse($db, $search_ref, $search_label, $search_warehouse, $search_date, $limit, $offset, $sortfield, $sortorder);
	} else {
		// CAS 2: Tous les entrepôts - agrégation par produit
		return getValorisationDataAllWarehouses($db, $search_ref, $search_label, $search_date, $limit, $offset, $sortfield, $sortorder);
	}
}

/**
 * Données pour un entrepôt spécifique
 */
function getValorisationDataForWarehouse($db, $search_ref, $search_label, $search_warehouse, $search_date, $limit = 0, $offset = 0, $sortfield = '', $sortorder = '')
{
	// Requête pour récupérer les données d'un entrepôt spécifique
	$sql = "SELECT DISTINCT p.rowid, p.ref, p.label, p.price, p.price_ttc, p.tva_tx,";
	$sql .= " ps.fk_entrepot as warehouse_id, ps.reel as stock_reel,";
	$sql .= " e.ref as warehouse_label";
	$sql .= " FROM " . MAIN_DB_PREFIX . "product as p";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON p.rowid = ps.fk_product";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "entrepot as e ON ps.fk_entrepot = e.rowid";
	$sql .= " WHERE p.entity IN (" . getEntity('product') . ")";
	$sql .= " AND p.fk_product_type = 0"; // Produits uniquement
	$sql .= " AND ps.fk_entrepot = " . ((int) $search_warehouse);
	$sql .= " AND ps.reel > 0"; // Stock réel positif

	// Filtres
	if ($search_ref) {
		$sql .= " AND p.ref LIKE '%" . addslashes($search_ref) . "%'";
	}
	if ($search_label) {
		$sql .= " AND p.label LIKE '%" . addslashes($search_label) . "%'";
	}

	return executeValorisationQuery($db, $sql, $search_date, $limit, $offset, $sortfield, $sortorder);
}

/**
 * Données agrégées pour tous les entrepôts
 */
function getValorisationDataAllWarehouses($db, $search_ref, $search_label, $search_date, $limit = 0, $offset = 0, $sortfield = '', $sortorder = '')
{
	// CORRECTION PRINCIPALE: Agrégation des stocks par produit
	$sql = "SELECT p.rowid, p.ref, p.label, p.price, p.price_ttc, p.tva_tx,";
	$sql .= " SUM(ps.reel) as stock_reel_total,"; // Somme des stocks de tous les entrepôts
	$sql .= " GROUP_CONCAT(DISTINCT CONCAT(e.ref, ':', ps.reel) SEPARATOR '|') as warehouse_details";
	$sql .= " FROM " . MAIN_DB_PREFIX . "product as p";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON p.rowid = ps.fk_product";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "entrepot as e ON ps.fk_entrepot = e.rowid";
	$sql .= " WHERE p.entity IN (" . getEntity('product') . ")";
	$sql .= " AND p.fk_product_type = 0"; // Produits uniquement

	// Filtres
	if ($search_ref) {
		$sql .= " AND p.ref LIKE '%" . addslashes($search_ref) . "%'";
	}
	if ($search_label) {
		$sql .= " AND p.label LIKE '%" . addslashes($search_label) . "%'";
	}

	$sql .= " GROUP BY p.rowid, p.ref, p.label, p.price, p.price_ttc, p.tva_tx";
	$sql .= " HAVING SUM(ps.reel) > 0"; // CORRECTION: Ne garder que les produits avec stock total > 0

	// Limite 
	if (!empty($limit)) {
		$sql .= $db->plimit($limit + 1, $offset);
	}

	$resql = $db->query($sql);
	if (!$resql) {
		return array();
	}

	$num = $db->num_rows($resql);
	$i = 0;
	$arrayofvalues = array();

	while ($i < $num) {
		$obj = $db->fetch_object($resql);

		// Stock actuel total (somme de tous les entrepôts)
		$stock_actuel = $obj->stock_reel_total ? (float)$obj->stock_reel_total : 0;

		// SÉCURITÉ : Si stock total est 0 ou négatif, on passe au suivant
		if ($stock_actuel <= 0) {
			$i++;
			continue;
		}

		// Calculer le stock à la date donnée pour TOUS les entrepôts
		$stock_date = $stock_actuel; // Par défaut = stock actuel total

		if (!empty($search_date)) {
			// Calcul des mouvements APRÈS la date pour TOUS les entrepôts du produit
			$sql_movements_after = "SELECT SUM(sm.value) as movements_after";
			$sql_movements_after .= " FROM " . MAIN_DB_PREFIX . "stock_mouvement as sm";
			$sql_movements_after .= " INNER JOIN " . MAIN_DB_PREFIX . "product_stock as ps2 ON sm.fk_entrepot = ps2.fk_entrepot";
			$sql_movements_after .= " WHERE sm.fk_product = " . ((int) $obj->rowid);
			$sql_movements_after .= " AND ps2.fk_product = " . ((int) $obj->rowid);
			$sql_movements_after .= " AND sm.datem > '" . $db->idate($search_date) . "'";

			$resql_movements = $db->query($sql_movements_after);
			if ($resql_movements) {
				$obj_movements = $db->fetch_object($resql_movements);
				$movements_after_date = $obj_movements->movements_after ? (float)$obj_movements->movements_after : 0;
				// Stock à la date = Stock actuel total - mouvements après la date
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

		// CORRECTION : Vérifier le stock calculé
		if ($stock_date <= 0) {
			$i++;
			continue;
		}

		// Récupérer le prix unitaire de la dernière facture fournisseur
		$supplier_unit_price = getLastSupplierInvoicePrice($db, $obj->rowid);

		// Logique de fallback pour le prix
		if (empty($supplier_unit_price) || $supplier_unit_price <= 0) {
			if (!empty($obj->price) && $obj->price > 0) {
				$supplier_unit_price = (float)$obj->price / 1.38;
			} else {
				// Si vraiment aucun prix disponible, on passe au produit suivant
				$i++;
				continue;
			}
		}

		// Calculer la valorisation avec le prix fournisseur
		$valuation = $supplier_unit_price * $stock_date;

		// SÉCURITÉ FINALE : Ne stocker que si valorisation > 0
		if ($valuation > 0) {
			// Créer un objet fictif pour la compatibilité
			$fake_obj = new stdClass();
			$fake_obj->rowid = $obj->rowid;
			$fake_obj->ref = $obj->ref;
			$fake_obj->label = $obj->label;
			$fake_obj->price = $obj->price;
			$fake_obj->warehouse_id = 0; // Tous les entrepôts
			$fake_obj->warehouse_label = 'Tous entrepôts';

			$arrayofvalues[] = array(
				'obj' => $fake_obj,
				'stock_date' => $stock_date,
				'stock_actuel' => $stock_actuel,
				'supplier_unit_price' => $supplier_unit_price,
				'valuation' => $valuation,
				'warehouse_details' => $obj->warehouse_details // Détail des entrepôts
			);
		}

		$i++;
	}

	// Appliquer le tri
	return applySorting($arrayofvalues, $sortfield, $sortorder);
}

/**
 * Exécution de la requête pour un entrepôt spécifique
 */
function executeValorisationQuery($db, $sql, $search_date, $limit, $offset, $sortfield, $sortorder)
{
	if (!empty($limit)) {
		$sql .= $db->plimit($limit + 1, $offset);
	}

	$resql = $db->query($sql);
	if (!$resql) {
		return array();
	}

	$num = $db->num_rows($resql);
	$i = 0;
	$arrayofvalues = array();

	while ($i < $num) {
		$obj = $db->fetch_object($resql);

		$stock_actuel = $obj->stock_reel ? (float)$obj->stock_reel : 0;

		if ($stock_actuel <= 0) {
			$i++;
			continue;
		}

		$stock_date = $stock_actuel;

		if (!empty($search_date) && $obj->warehouse_id) {
			$sql_movements_after = "SELECT SUM(sm.value) as movements_after";
			$sql_movements_after .= " FROM " . MAIN_DB_PREFIX . "stock_mouvement as sm";
			$sql_movements_after .= " WHERE sm.fk_product = " . ((int) $obj->rowid);
			$sql_movements_after .= " AND sm.fk_entrepot = " . ((int) $obj->warehouse_id);
			$sql_movements_after .= " AND sm.datem > '" . $db->idate($search_date) . "'";

			$resql_movements = $db->query($sql_movements_after);
			if ($resql_movements) {
				$obj_movements = $db->fetch_object($resql_movements);
				$movements_after_date = $obj_movements->movements_after ? (float)$obj_movements->movements_after : 0;
				$stock_date = $stock_actuel - $movements_after_date;
				$db->free($resql_movements);
			} else {
				$stock_date = 0;
			}

			if ($stock_date < 0) {
				$stock_date = 0;
			}
		}

		if ($stock_date <= 0) {
			$i++;
			continue;
		}

		$supplier_unit_price = getLastSupplierInvoicePrice($db, $obj->rowid);

		if (empty($supplier_unit_price) || $supplier_unit_price <= 0) {
			if (!empty($obj->price) && $obj->price > 0) {
				$supplier_unit_price = (float)$obj->price / 1.38;
			} else {
				$i++;
				continue;
			}
		}

		$valuation = $supplier_unit_price * $stock_date;

		if ($valuation > 0) {
			$arrayofvalues[] = array(
				'obj' => $obj,
				'stock_date' => $stock_date,
				'stock_actuel' => $stock_actuel,
				'supplier_unit_price' => $supplier_unit_price,
				'valuation' => $valuation
			);
		}

		$i++;
	}

	$db->free($resql);
	return applySorting($arrayofvalues, $sortfield, $sortorder);
}

/**
 * Application du tri
 */
function applySorting($arrayofvalues, $sortfield, $sortorder)
{
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
				usort($arrayofvalues, function ($a, $b) {
					return $b['valuation'] <=> $a['valuation'];
				});
				break;
		}
	} else {
		usort($arrayofvalues, function ($a, $b) {
			return $b['valuation'] <=> $a['valuation'];
		});
	}

	return $arrayofvalues;
}
/**
 * Fonction pour exporter en Excel
 */
function exportToExcel($db, $search_ref, $search_label, $search_warehouse, $search_date, $sortfield, $sortorder, $limit, $offset)
{
	global $langs, $conf;

	// Récupérer toutes les données sans limite
	$arrayofvalues = getValorisationData($db, $search_ref, $search_label, $search_warehouse, $search_date, $limit, $offset, $sortfield, $sortorder);

	// Créer le nom du fichier avec la date
	$filename = 'valorisation_stock_' . dol_print_date($search_date, '%Y%m%d');
	if ($search_warehouse > 0) {
		$warehousestatic = new Entrepot($db);
		$warehousestatic->fetch($search_warehouse);
		$filename .= '_' . dol_sanitizeFileName($warehousestatic->ref);
	}
	$filename .= '.xls';

	// Headers pour le téléchargement
	header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	// Commencer le contenu Excel
	echo "\xEF\xBB\xBF"; // BOM UTF-8
	echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
	echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>';
	echo '<body>';
	echo '<table border="1">';

	// En-tête du rapport
	echo '<tr><td colspan="6" style="font-weight:bold; font-size:16px; text-align:center;">';
	echo $langs->trans("Valorisation Stock") . ' - ' . dol_print_date($search_date, '%d/%m/%Y');
	echo '</td></tr>';

	if ($search_warehouse > 0) {
		$warehousestatic = new Entrepot($db);
		$warehousestatic->fetch($search_warehouse);
		echo '<tr><td colspan="6" style="font-weight:bold; text-align:center;">';
		echo $langs->trans("Warehouse") . ': ' . $warehousestatic->ref;
		echo '</td></tr>';
	}

	echo '<tr><td colspan="6"></td></tr>'; // Ligne vide

	// En-têtes des colonnes
	echo '<tr style="font-weight:bold; background-color:#f0f0f0;">';
	echo '<td>' . $langs->trans("Ref") . '</td>';
	echo '<td>' . $langs->trans("Label") . '</td>';
	echo '<td>' . $langs->trans("Entrepôts") . '</td>';

	echo '<td>' . $langs->trans("Stock à date") . '</td>';
	echo '<td>' . $langs->trans("Stock actuel") . '</td>';
	echo '<td>' . $langs->trans("Dernier prix fournisseur") . '</td>';
	echo '<td>' . $langs->trans("Valuation") . '</td>';
	echo '</tr>';

	// Données
	$total_valuation = 0;
	foreach ($arrayofvalues as $data) {
		$obj = $data['obj'];
		$stock_date = $data['stock_date'];
		$stock_actuel = $data['stock_actuel'];
		$supplier_unit_price = $data['supplier_unit_price'];
		$valuation = $data['valuation'];
		if ($obj->ref === '1325835000') {
			$stock_date = $stock_date / 10;
			$stock_actuel = $stock_actuel / 10;
			$valuation = $valuation / 10;
		}
		$total_valuation += $valuation;

		echo '<tr>';
		echo '<td>' . htmlspecialchars($obj->ref) . '</td>';
		echo '<td>' . htmlspecialchars($obj->label) . '</td>';
		// Entrepôts (détail par entrepôt ou "Tous entrepôts")
		if (!empty($data['warehouse_details'])) {
			$entrepots = explode('|', $data['warehouse_details']);
			$entrepot_list = array();
			foreach ($entrepots as $item) {
				list($whLabel, $qty) = explode(':', $item);
				$entrepot_list[] = $whLabel;
			}
			$entrepots_display = implode(', ', $entrepot_list);
		} else {
			$entrepots_display = dol_escape_htmltag($obj->warehouse_label);
		}
		echo '<td>' . $entrepots_display . '</td>';
		echo '<td style="text-align:center;">' . $stock_date . '</td>';
		echo '<td style="text-align:center;">' . $stock_actuel . '</td>';
		echo '<td style="text-align:right;">' . number_format($supplier_unit_price, 2, ',', ' ') . '</td>';
		echo '<td style="text-align:right;">' . number_format($valuation, 2, ',', ' ') . '</td>';
		echo '</tr>';
	}

	// Ligne de total
	echo '<tr style="font-weight:bold; background-color:#e0e0e0;">';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td style="text-align:right;">' . $langs->trans("Total") . '</td>';
	echo '<td style="text-align:right;">' . number_format($total_valuation, 2, ',', ' ') . '</td>';
	echo '</tr>';

	echo '</table>';
	echo '</body></html>';

	exit;
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

// Action d'export Excel
if ($action == 'export_excel' && !empty($search_date)) {
	exportToExcel($db, $search_ref, $search_label, $search_warehouse, $search_date, $sortfield, $sortorder, $limit, $offset);
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

// Formulaire de recherche
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="search">';

print '<tr class="oddeven">';
print '<td><label for="limit">' . $langs->trans("Limit") . '</label></td>';
print '<td>';
print '<select name="limit" id="limit" class="flat maxwidth200">';
$limits = array(10, 500, 1000, 2000, 3000);
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

	// Bouton d'export Excel
	print '<div class="tabsAction">';
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '" style="display: inline;">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="export_excel">';
	print '<input type="hidden" name="search_ref" value="' . dol_escape_htmltag($search_ref) . '">';
	print '<input type="hidden" name="search_label" value="' . dol_escape_htmltag($search_label) . '">';
	print '<input type="hidden" name="search_warehouse" value="' . (int)$search_warehouse . '">';
	print '<input type="hidden" name="search_dateday" value="' . (int)GETPOST('search_dateday', 'int') . '">';
	print '<input type="hidden" name="search_datemonth" value="' . (int)GETPOST('search_datemonth', 'int') . '">';
	print '<input type="hidden" name="search_dateyear" value="' . (int)GETPOST('search_dateyear', 'int') . '">';
	print '<input type="hidden" name="sortfield" value="' . dol_escape_htmltag($sortfield) . '">';
	print '<input type="hidden" name="sortorder" value="' . dol_escape_htmltag($sortorder) . '">';
	print '<input type="submit" class="butAction" value="' . $langs->trans("Export Excel") . '">';
	print '</form>';
	print '</div>';

	print '<div class="div-table-responsive-no-min">';
	print '<table class="liste centpercent">';

	// Ligne de titres
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("Ref"), $_SERVER["PHP_SELF"], "p.ref", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Label"), $_SERVER["PHP_SELF"], "p.label", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Entrepôts"), $_SERVER["PHP_SELF"], "", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Stock à date"), $_SERVER["PHP_SELF"], "", "", $param, "center", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Stock actuel"), $_SERVER["PHP_SELF"], "stock_actuel", "", $param, "center", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Dernier prix fournisseur"), $_SERVER["PHP_SELF"], "supplier_price", "", $param, "right", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Valuation"), $_SERVER["PHP_SELF"], "valuation", "", $param, "right", $sortfield, $sortorder);
	print_liste_field_titre('', $_SERVER["PHP_SELF"], "", "", $param, "", $sortfield, $sortorder, 'center maxwidthsearch ');
	$next_sortorder = ($sortorder === 'ASC') ? 'DESC' : 'ASC';
	print "</tr>\n";

	// Récupérer les données
	$arrayofvalues = getValorisationData($db, $search_ref, $search_label, $search_warehouse, $search_date, $limit, $offset, $sortfield, $sortorder);

	// Afficher les résultats
	$total_valuation = 0;
	$nbdisplayed = 0;
	$has_more = count($arrayofvalues) > $limit;

	foreach ($arrayofvalues as $data) {
		if ($nbdisplayed >= $limit) break;
		$obj = $data['obj'];
		$stock_date = $data['stock_date'];
		$stock_actuel = $data['stock_actuel'];
		$supplier_unit_price = $data['supplier_unit_price'];
		$valuation = $data['valuation'];

		if ($obj->ref === '1325835000') {
			// $stock_date = $stock_date / 10;
			// $stock_actuel = $stock_actuel / 10;
			$valuation = $valuation / 10;
		}

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

		// Entrepôts
		print '<td>';
		if (!empty($data['warehouse_details'])) {
			$warehouses = explode('|', $data['warehouse_details']);
			foreach ($warehouses as $w) {
				list($whLabel, $qty) = explode(':', $w);
				print '<div>' . dol_escape_htmltag($whLabel) . '</div>';
			}
		} else {
			print $langs->trans("Tous entrepôts");
		}

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
		print '<td></td>';
		print '<td class="right"><strong>' . $langs->trans("Total") . '</strong></td>';
		print '<td class="right"><strong>' . price($total_valuation) . '</strong></td>';
		print '<td></td>';
		print '</tr>';
	}

	if (count($arrayofvalues) == 0) {
		print '<tr class="oddeven"><td colspan="7" class="opacitymedium">' . $langs->trans("NoRecordFound") . '</td></tr>';
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
	$params[] = 'limit=' . $limit;
	$params[] = 'action=search';

	$baseurl = $url . (count($params) ? '?' . implode('&', $params) : '');

	// Page précédente
	if ($page > 0) {
		print '<a class="button" href="' . $baseurl . '&page=' . $pageprev . '">&laquo; Page précédente</a> ';
	}
	print '<strong>' . $langs->trans("Page") . ' ' . ($page + 1) . '</strong>';
	// Page suivante
	if (count($arrayofvalues) > $limit) {
		print '<a class="button" href="' . $baseurl . '&page=' . $pagenext . '">Page suivante &raquo;</a>';
	}

	print '</div>';
	print '</div>';
} else {
	// Message d'information si aucune date n'est sélectionnée
	print '<br><div class="info clearboth">';
	print '<div class="center">';
	print '<span class="opacitymedium">';
	print $langs->trans("Veuillez sélectionner une date pour afficher les résultats de l'évaluation des stocks");
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

.tabsAction {
    margin-bottom: 15px;
}

.butAction {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    // padding: 8px 16px;
   
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    // display: inline-block;
}


</style>';

// End of page
llxFooter();
$db->close();
