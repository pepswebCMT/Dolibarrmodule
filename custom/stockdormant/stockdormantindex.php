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
 *	\file       stockdormant/stockdormantindex.php
 *	\ingroup    stockdormant
 *	\brief      Page d'accueil du module stock dormant avec filtres de dates
 */

// Load Dolibarr environment

$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
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
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';

$langs->loadLangs(array("stockdormant@stockdormant", "products", "stocks"));

$action = GETPOST('action', 'aZ09');
$search_date_start = dol_mktime(0, 0, 0, GETPOST('search_date_startmonth', 'int'), GETPOST('search_date_startday', 'int'), GETPOST('search_date_startyear', 'int'));
$search_date_end = dol_mktime(23, 59, 59, GETPOST('search_date_endmonth', 'int'), GETPOST('search_date_endday', 'int'), GETPOST('search_date_endyear', 'int'));
$search_ref = GETPOST('search_ref', 'alpha');
$search_label = GETPOST('search_label', 'alpha');
$search_warehouse = GETPOST('search_warehouse', 'int');
$default_warehouse_id = 7; // ID de l'entrepôt par défaut à définir ici
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');

if (!$sortfield) $sortfield = 'p.ref'; // tri par défaut
if (!$sortorder) $sortorder = 'ASC';

$search_warehouse = GETPOST('search_warehouse', 'int');
if (empty($search_warehouse)) {
	$search_warehouse = $default_warehouse_id;
}


if (empty($search_date_start) && empty($search_date_end)) {
	$search_date_end = dol_now();
	$search_date_start = dol_time_plus_duree($search_date_end, -3, 'm');
}

if (GETPOST('button_removefilter', 'alpha')) {
	$search_date_start = '';
	$search_date_end = '';
	$search_ref = '';
	$search_label = '';
	$search_warehouse = 0;
}

if (!$user->hasRight('produit', 'lire')) accessforbidden();

$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);

llxHeader('', $langs->trans("Stock dormant "));
print load_fiche_titre($langs->trans("Stock dormant"), '', 'stockdormant.png@stockdormant');

// === Formulaire de recherche ===
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="search">';
print '<div class="fichecenter"><table class="border centpercent">';
print '<tr class="liste_titre"><td colspan="4">' . $langs->trans("Filtres de recherche") . '</td></tr>';

print '<tr>';
print '<td class="fieldrequired">' . $langs->trans("DateStart") . '</td><td>';
print $form->selectDate($search_date_start, 'search_date_start', 0, 0, 0, "", 1, 1);
print '</td><td class="fieldrequired">' . $langs->trans("DateEnd") . '</td><td>';
print $form->selectDate($search_date_end, 'search_date_end', 0, 0, 0, "", 1, 1);
print '</td></tr>';

print '<tr><td>' . $langs->trans("Ref") . '</td><td><input type="text" name="search_ref" value="' . dol_escape_htmltag($search_ref) . '" size="20"></td>';
print '<td>' . $langs->trans("Label") . '</td><td><input type="text" name="search_label" value="' . dol_escape_htmltag($search_label) . '" size="20"></td></tr>';

print '<tr><td>' . $langs->trans("Warehouse") . '</td><td colspan="3">';
print $formproduct->selectWarehouses($search_warehouse, 'search_warehouse', '', 0, 0, 0, '', 0, 0, array(), 'maxwidth200');
print '</td></tr>';

print '<tr><td colspan="4" class="center">';
print '<input type="submit" class="button" value="' . $langs->trans("Search") . '">';
print '&nbsp;';
print '<input type="submit" class="button button-cancel" name="button_removefilter" value="' . $langs->trans("RemoveFilter") . '">';
print '</td></tr></table></div></form><br>';

// === Résultats ===
if ($search_date_start && $search_date_end) {
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<th><a href="' . $_SERVER["PHP_SELF"] . '?sortfield=p.ref&sortorder=' . (($sortfield == 'p.ref' && $sortorder == 'ASC') ? 'DESC' : 'ASC') . $param . '">' . $langs->trans("Ref") . '</a></th>';

	print '<th><a href="' . $_SERVER["PHP_SELF"] . '?sortfield=p.label&sortorder=' . (($sortfield == 'p.label' && $sortorder == 'ASC') ? 'DESC' : 'ASC') . $param . '">' . $langs->trans("Label") . '</a></th>';

	print '<th class="right"><a href="' . $_SERVER["PHP_SELF"] . '?sortfield=ps.reel&sortorder=' . (($sortfield == 'ps.reel' && $sortorder == 'ASC') ? 'DESC' : 'ASC') . $param . '">' . $langs->trans("Stock") . '</a></th>';

	print '<th class="right"><a href="' . $_SERVER["PHP_SELF"] . '?sortfield=last_purchase_price&sortorder=' . (($sortfield == 'last_purchase_price' && $sortorder == 'ASC') ? 'DESC' : 'ASC') . $param . '">' . $langs->trans("Dernier prix d\'achat") . ' (' . $langs->trans("HT") . ')</a></th>';

	print '<th class="right">' . $langs->trans("Valeur de stock") . ' (' . $langs->trans("HT") . ')</th>'; // pas de tri direct car champ calculé

	print '<th class="right"><a href="' . $_SERVER["PHP_SELF"] . '?sortfield=last_movement&sortorder=' . (($sortfield == 'last_movement' && $sortorder == 'ASC') ? 'DESC' : 'ASC') . $param . '">' . $langs->trans("Date du dernier mouvement") . '</a></th>';
	print '</tr>';

	$sql = "SELECT DISTINCT p.rowid, p.ref, p.label, p.price, p.pmp, ps.reel as stock_reel,";
	$sql .= " (SELECT MAX(sm.datem) FROM " . MAIN_DB_PREFIX . "stock_mouvement sm WHERE sm.fk_product = p.rowid) as last_movement,";
	$sql .= " (SELECT ff.pu_ht FROM " . MAIN_DB_PREFIX . "facture_fourn_det ff INNER JOIN " . MAIN_DB_PREFIX . "facture_fourn f ON f.rowid = ff.fk_facture_fourn";
	$sql .= " WHERE ff.fk_product = p.rowid AND f.fk_statut >= 1 ORDER BY f.datef DESC LIMIT 1) as last_purchase_price";
	$sql .= " FROM " . MAIN_DB_PREFIX . "product p";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_stock ps ON ps.fk_product = p.rowid";
	$sql .= " WHERE p.entity IN (" . getEntity('product') . ") AND p.fk_product_type = 0 AND ps.reel > 0";

	if ($search_warehouse > 0) {
		$sql .= " AND ps.fk_entrepot = " . ((int) $search_warehouse);
	}

	$sql .= " AND (SELECT MAX(sm.datem) FROM " . MAIN_DB_PREFIX . "stock_mouvement sm WHERE sm.fk_product = p.rowid) < '" . dol_print_date($search_date_start, '%Y-%m-%d') . "'";
	$sql .= " AND (";
	$sql .= "(SELECT MAX(sm.datem) FROM " . MAIN_DB_PREFIX . "stock_mouvement sm WHERE sm.fk_product = p.rowid) IS NULL";
	$sql .= " OR ";
	$sql .= "(SELECT MAX(sm.datem) FROM " . MAIN_DB_PREFIX . "stock_mouvement sm WHERE sm.fk_product = p.rowid) < '" . dol_print_date($search_date_start, '%Y-%m-%d') . "'";
	$sql .= ")";

	if ($search_ref) $sql .= " AND p.ref LIKE '%" . $db->escape($search_ref) . "%'";
	if ($search_label) $sql .= " AND p.label LIKE '%" . $db->escape($search_label) . "%'";
	$sql .= " ORDER BY $sortfield $sortorder";

	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		$total_valorisation = 0;
		while ($obj = $db->fetch_object($resql)) {
			$pu = $obj->last_purchase_price ?: $obj->pmp;
			$val = $pu * $obj->stock_reel;
			$total_valorisation += $val;

			$productstatic = new Product($db);
			$productstatic->id = $obj->rowid;
			$productstatic->ref = $obj->ref;

			print '<tr class="oddeven">';
			print '<td>' . $productstatic->getNomUrl(1) . '</td>';
			print '<td>' . dol_trunc($obj->label, 40) . '</td>';
			print '<td class="right">' . number_format($obj->stock_reel, 0, ',', ' ') . '</td>';

			print '<td class="right">' . ($pu > 0 ? price($pu) : '-') . '</td>';
			print '<td class="right">' . ($val > 0 ? price($val) : '-') . '</td>';
			print '<td class="right">' . ($obj->last_movement ? dol_print_date($db->jdate($obj->last_movement), 'day') : '-') . '</td>';
			print '</tr>';
		}

		if ($total_valorisation > 0) {
			print '<tr class="liste_total"><td colspan="4">' . $langs->trans("Total") . '</td>';
			print '<td class="right"><strong>' . price($total_valorisation) . '</strong></td><td></td></tr>';
		}

		$db->free($resql);
	} else {
		dol_print_error($db);
	}

	print '</table></div>';
}

llxFooter();
$db->close();
