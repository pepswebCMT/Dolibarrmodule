<?php
// /* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
//  * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
//  * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
//  * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
//  *
//  * This program is free software; you can redistribute it and/or modify
//  * it under the terms of the GNU General Public License as published by
//  * the Free Software Foundation; either version 3 of the License, or
//  * (at your option) any later version.
//  *
//  * This program is distributed in the hope that it will be useful,
//  * but WITHOUT ANY WARRANTY; without even the implied warranty of
//  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  * GNU General Public License for more details.
//  *
//  * You should have received a copy of the GNU General Public License
//  * along with this program. If not, see <https://www.gnu.org/licenses/>.
//  */

// /**
//  *	\file       stockalerte/stockalerteindex.php
//  *	\ingroup    stockalerte
//  *	\brief      Home page of stockalerte top menu
//  */

// // Load Dolibarr environment
// $res = 0;
// // Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
// if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
// 	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// }
// // Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
// $tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
// $tmp2 = realpath(__FILE__);
// $i = strlen($tmp) - 1;
// $j = strlen($tmp2) - 1;
// while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
// 	$i--;
// 	$j--;
// }
// if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
// 	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
// }
// if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
// 	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// }
// // Try main.inc.php using relative path
// if (!$res && file_exists("../main.inc.php")) {
// 	$res = @include "../main.inc.php";
// }
// if (!$res && file_exists("../../main.inc.php")) {
// 	$res = @include "../../main.inc.php";
// }
// if (!$res && file_exists("../../../main.inc.php")) {
// 	$res = @include "../../../main.inc.php";
// }
// if (!$res) {
// 	die("Include of main fails");
// }

// require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
// require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
// require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.class.php';
// require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
// require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';

// // Load translation files required by the page
// $langs->loadLangs(array("stockalerte@stockalerte", "products", "stocks", "suppliers"));

// $action = GETPOST('action', 'aZ09');

// // Paramètre pour l'affichage AJAX
// $mode = GETPOST('mode', 'alpha');

// // Paramètres pour les filtres
// $fk_entrepot = GETPOST('fk_entrepot', 'int');
// $fk_fournisseur = GETPOST('fk_fournisseur', 'int');

// $max = 5;
// $now = dol_now();

// // Security check - Protection if external user
// $socid = GETPOST('socid', 'int');
// if (isset($user->socid) && $user->socid > 0) {
// 	$action = '';
// 	$socid = $user->socid;
// }

// /*
//  * View
//  */

// // Si on est en mode AJAX, on retourne uniquement le tableau des produits
// if ($mode == 'ajax') {
// 	displayStockAlert($db, $langs, $fk_entrepot, $fk_fournisseur);
// 	exit;
// }

// $form = new Form($db);
// $formfile = new FormFile($db);
// $entrepot = new Entrepot($db);
// $fournisseur = new Fournisseur($db);
// $formproduct = new FormProduct($db);

// llxHeader("", $langs->trans("StockalerteArea"), '', '', 0, 0, '', '', '', 'mod-stockalerte page-index');

// print load_fiche_titre($langs->trans("Produits en alerte de stock"), '', 'product');

// // Légende simple pour les couleurs
// print '<div style="margin-bottom: 10px;">';
// print '<span style="margin-right: 15px;"><span style="color: #cc0000; font-weight: bold;">■</span> ' . $langs->trans("Stock critique") . ' (< 50% du seuil)</span>';
// print '<span><span style="color: #cc7a00; font-weight: bold;">■</span> ' . $langs->trans("Stock en alerte") . ' (≥ 50% du seuil)</span>';
// print '</div>';

// // Ajout de styles simples - uniquement des couleurs de texte, sans fond
// print '<style type="text/css">
//     .stock-critical {
//         color: #cc0000;
//         font-weight: bold;
//     }
//     .stock-warning {
//         color: #cc7a00;
//         font-weight: bold;
//     }
// </style>';

// // Formulaire de filtre
// print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
// print '<input type="hidden" name="token" value="' . newToken() . '">';
// print '<div class="inline-block opacitymedium marginrightonly">' . $langs->trans("Filtres") . '</div>';
// print '<div class="inline-block marginrightonly">';
// print $langs->trans("Entrepôt") . ': ';
// print $formproduct->selectWarehouses($fk_entrepot, 'fk_entrepot', '', 1);
// print '</div>';
// print '<div class="inline-block marginrightonly">';
// print $langs->trans("Fournisseur") . ': ';
// print $form->select_company($fk_fournisseur, 'fk_fournisseur', 's.fournisseur = 1', 1);
// print '</div>';
// print '<div class="inline-block">';
// print '<input type="submit" class="button" name="search" value="' . $langs->trans("Filtrer") . '">';
// print '</div>';
// print '</form>';

// // Div pour contenir la liste des produits en alerte
// print '<div id="stock-alert-container">';
// displayStockAlert($db, $langs, $fk_entrepot, $fk_fournisseur);
// print '</div>';

// // Ajout d'un bouton pour rafraîchir
// print '<div class="tabsAction" style="text-align: right; margin-top: 10px;">';
// print '<a class="butAction" href="javascript:void(0);" onclick="refreshStockAlerts();">' . $langs->trans("Rafraîchir") . '</a>';
// print '</div>';

// // Script JavaScript pour l'actualisation automatique
// print '<script type="text/javascript">
// function refreshStockAlerts() {
//     fetch("' . $_SERVER["PHP_SELF"] . '?mode=ajax&fk_entrepot=' . $fk_entrepot . '&fk_fournisseur=' . $fk_fournisseur . '")
//         .then(response => response.text())
//         .then(data => {
//             document.getElementById("stock-alert-container").innerHTML = data;
//         })
//         .catch(error => console.error("Erreur lors du rafraîchissement:", error));
// }

// // Auto-refresh toutes les 60 secondes
// setInterval(refreshStockAlerts, 60000);
// </script>';

// // End of page
// llxFooter();
// $db->close();

// /**
//  * Fonction pour afficher la liste des produits en alerte de stock
//  *
//  * @param DoliDB $db               Database handler
//  * @param Translate $langs         Language object
//  * @param int $fk_entrepot         ID de l'entrepôt pour filtrer
//  * @param int $fk_fournisseur      ID du fournisseur pour filtrer
//  * @return void
//  */
// function displayStockAlert($db, $langs, $fk_entrepot = 0, $fk_fournisseur = 0)
// {
// 	$sql = "SELECT p.rowid, p.ref, p.label, p.seuil_stock_alerte, ps.reel,";
// 	$sql .= " e.rowid as entrepot_id, e.ref as entrepot_ref, e.lieu as entrepot_lieu,";
// 	$sql .= " s.rowid as fournisseur_id, s.nom as fournisseur_nom";
// 	$sql .= " FROM " . MAIN_DB_PREFIX . "product as p";
// 	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON ps.fk_product = p.rowid";
// 	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "entrepot as e ON e.rowid = ps.fk_entrepot";
// 	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price as pfp ON pfp.fk_product = p.rowid";
// 	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid = pfp.fk_soc";
// 	$sql .= " WHERE p.tosell = 1";
// 	$sql .= " AND p.seuil_stock_alerte IS NOT NULL";
// 	$sql .= " AND ps.reel <= p.seuil_stock_alerte";

// 	// Ajout des filtres
// 	if ($fk_entrepot > 0) {
// 		$sql .= " AND ps.fk_entrepot = " . (int) $fk_entrepot;
// 	}

// 	if ($fk_fournisseur > 0) {
// 		$sql .= " AND pfp.fk_soc = " . (int) $fk_fournisseur;
// 	}

// 	$sql .= " GROUP BY p.rowid, ps.rowid"; // Pour éviter les doublons si un produit a plusieurs fournisseurs
// 	$sql .= " ORDER BY ps.reel ASC";

// 	$resql = $db->query($sql);

// 	if ($resql) {
// 		$num = $db->num_rows($resql);

// 		print '<table class="liste centpercent">';
// 		print '<tr class="liste_titre">';
// 		print '<th>' . $langs->trans("Réf.") . '</th>';
// 		print '<th>' . $langs->trans("Nom") . '</th>';
// 		print '<th>' . $langs->trans("Entrepôt") . '</th>';
// 		print '<th>' . $langs->trans("Fournisseur") . '</th>';
// 		print '<th>' . $langs->trans("Stock") . '</th>';
// 		print '<th>' . $langs->trans("Seuil alerte") . '</th>';
// 		print '</tr>';

// 		if ($num > 0) {
// 			while ($obj = $db->fetch_object($resql)) {
// 				// Détermination simple de la classe pour la coloration
// 				$stockClass = '';
// 				if ($obj->reel <= $obj->seuil_stock_alerte * 0.5) {
// 					// Stock inférieur à 50% du seuil = critique (rouge)
// 					$stockClass = 'stock-critical';
// 				} else {
// 					// Stock entre 50% et 100% du seuil = avertissement (jaune)
// 					$stockClass = 'stock-warning';
// 				}

// 				print '<tr class="oddeven">';
// 				print '<td><a href="' . DOL_URL_ROOT . '/product/card.php?id=' . $obj->rowid . '">' . dol_escape_htmltag($obj->ref) . '</a></td>';
// 				print '<td>' . dol_escape_htmltag($obj->label) . '</td>';

// 				// Colonne entrepôt
// 				print '<td>';
// 				if (!empty($obj->entrepot_id)) {
// 					print '<a href="' . DOL_URL_ROOT . '/product/stock/card.php?id=' . $obj->entrepot_id . '">' .
// 						dol_escape_htmltag($obj->entrepot_ref) . (!empty($obj->entrepot_lieu) ? ' - ' . $obj->entrepot_lieu : '') . '</a>';
// 				} else {
// 					print $langs->trans("NonDéfini");
// 				}
// 				print '</td>';

// 				// Colonne fournisseur
// 				print '<td>';
// 				if (!empty($obj->fournisseur_id)) {
// 					print '<a href="' . DOL_URL_ROOT . '/fourn/card.php?socid=' . $obj->fournisseur_id . '">' .
// 						dol_escape_htmltag($obj->fournisseur_nom) . '</a>';
// 				} else {
// 					print $langs->trans("NonDéfini");
// 				}
// 				print '</td>';

// 				print '<td class="' . $stockClass . '">' . $obj->reel . '</td>';
// 				print '<td>' . $obj->seuil_stock_alerte . '</td>';
// 				print '</tr>';
// 			}
// 		} else {
// 			print '<tr><td colspan="6" class="opacitymedium">' . $langs->trans("AucunProduitEnAlerte") . '</td></tr>';
// 		}

// 		print '</table>';

// 		// Date de dernière mise à jour
// 		print '<div class="right" style="margin-top: 5px; font-style: italic; font-size: 0.9em;">';
// 		print $langs->trans("Dernière mise à jour") . ': ' . dol_print_date(dol_now(), 'dayhourtext');
// 		print '</div>';
// 	} else {
// 		dol_print_error($db);
// 	}
// }



require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.commande.class.php';

$langs->loadLangs(["stockalerte@stockalerte", "products", "stocks", "suppliers", "orders"]);

$action = GETPOST('action', 'aZ09');
$fk_entrepot = GETPOST('fk_entrepot', 'int');
$fk_fournisseur = GETPOST('fk_fournisseur', 'int');
$show_all = GETPOST('show_all', 'int');
$selected_products = GETPOST('select_product', 'array');

// Création de la commande fournisseur
if ($action == 'create_order' && $fk_fournisseur && !empty($selected_products)) {
	$db->begin();
	$order = new CommandeFournisseur($db);
	$order->socid = $fk_fournisseur;
	$order->date_commande = dol_now();
	$order->ref_supplier = '';
	$order->note_private = $langs->trans("CommandeAutoStockAlerte");
	$order_id = $order->create($user);

	if ($order_id > 0) {
		foreach ($selected_products as $product_id) {
			$sql = "SELECT p.rowid, p.ref, pfp.price, pe.conditionnementpalette as cond_pal"
				. " FROM " . MAIN_DB_PREFIX . "product as p"
				. " LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price as pfp ON pfp.fk_product = p.rowid AND pfp.fk_soc = " . ((int) $fk_fournisseur)
				. " LEFT JOIN " . MAIN_DB_PREFIX . "product_extrafields as pe ON pe.fk_object = p.rowid"
				. " WHERE p.rowid = " . ((int) $product_id)
				. " ORDER BY pfp.quantity ASC LIMIT 1";

			$resql = $db->query($sql);
			if ($resql && ($obj = $db->fetch_object($resql))) {
				$qty_to_order = (!empty($obj->cond_pal) && is_numeric($obj->cond_pal)) ? (int)$obj->cond_pal : 1;
				$order->addline('', $obj->price, $qty_to_order, 20, 0, 0, $product_id, 0, '', '', 'HT');
			}
		}
		$db->commit();
		header("Location: " . DOL_URL_ROOT . "/fourn/commande/card.php?id=" . $order_id . "&action=edit");
		exit;
	} else {
		$db->rollback();
		setEventMessages($order->error, $order->errors, 'errors');
	}
}

if (!isset($_POST['search'])) {
	$_POST['search'] = 1;
}

$form = new Form($db);
$formproduct = new FormProduct($db);

// Titre de la page
$title = $show_all ? $langs->trans("Produits en stock") : $langs->trans("Produits en alerte de stock");
llxHeader("", $title);
print load_fiche_titre($title, '', 'product');

// Légende pour les couleurs d'alerte
print '<style>.stock-critical{color:#cc0000;font-weight:bold}.stock-warning{color:#cc7a00;font-weight:bold}</style>';
print '<div style="margin-bottom:10px;"><span style="margin-right:15px;"><span style="color:#cc0000;font-weight:bold;">■</span> ' . $langs->trans("Stock critique") . ' (< 50% du seuil)</span><span><span style="color:#cc7a00;font-weight:bold;">■</span> ' . $langs->trans("Stock en alerte") . ' (≥ 50% du seuil)</span></div>';

// Formulaire de filtrage
print '<form method="POST">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<div class="inline-block">' . $langs->trans("Entrepôt") . ': ' . $formproduct->selectWarehouses($fk_entrepot, 'fk_entrepot', '', 1) . '</div> ';
print '<div class="inline-block">' . $langs->trans("Fournisseur") . ': ' . $form->select_company($fk_fournisseur, 'fk_fournisseur', 's.fournisseur = 1', 1) . '</div> ';
print '<div class="inline-block"><label><input type="checkbox" name="show_all" value="1" ' . ($show_all ? 'checked' : '') . '> ' . $langs->trans("Afficher tous les produits") . '</label></div> ';
print '<input type="submit" name="search" class="button" value="' . $langs->trans("Filtrer") . '">';
print '</form><br>';

// Construction de la requête SQL
$sql = "SELECT p.rowid, p.ref, p.label, p.seuil_stock_alerte, ps.reel, e.ref as entrepot_ref, 
               e.rowid as entrepot_id, e.lieu as entrepot_lieu, s.nom as fournisseur_nom, s.rowid as fournisseur_id";

if ($show_all) {
	// Si on affiche tous les produits
	$sql .= " FROM " . MAIN_DB_PREFIX . "product as p";

	// Jointure avec les stocks
	if ($fk_entrepot > 0) {
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON ps.fk_product = p.rowid AND ps.fk_entrepot = " . $fk_entrepot;
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "entrepot as e ON e.rowid = ps.fk_entrepot";
	} else {
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON ps.fk_product = p.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "entrepot as e ON e.rowid = ps.fk_entrepot";
	}

	// Jointure avec les fournisseurs
	if ($fk_fournisseur > 0) {
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price as pfp ON pfp.fk_product = p.rowid AND pfp.fk_soc = " . $fk_fournisseur;
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid = pfp.fk_soc";
	} else {
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price as pfp ON pfp.fk_product = p.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid = pfp.fk_soc";
	}

	$sql .= " WHERE p.tosell = 1";
} else {
	// Si on n'affiche que les produits en alerte
	$sql .= " FROM " . MAIN_DB_PREFIX . "product as p";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "product_stock as ps ON ps.fk_product = p.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "entrepot as e ON e.rowid = ps.fk_entrepot";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price as pfp ON pfp.fk_product = p.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid = pfp.fk_soc";

	$sql .= " WHERE p.tosell = 1 AND p.seuil_stock_alerte IS NOT NULL AND ps.reel <= p.seuil_stock_alerte";

	if ($fk_entrepot > 0) {
		$sql .= " AND ps.fk_entrepot = " . $fk_entrepot;
	}

	if ($fk_fournisseur > 0) {
		$sql .= " AND pfp.fk_soc = " . $fk_fournisseur;
	}
}

$sql .= " GROUP BY p.rowid";

// Tri des résultats
if ($show_all) {
	$sql .= " ORDER BY p.ref ASC";
} else {
	$sql .= " ORDER BY ps.reel ASC";
}

// Exécution de la requête et affichage des résultats
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);
	if ($num > 0) {
		print '<form method="POST">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="create_order">';
		print '<input type="hidden" name="fk_fournisseur" value="' . $fk_fournisseur . '">';
		print '<input type="hidden" name="show_all" value="' . $show_all . '">';
		print '<table class="liste centpercent">';
		print '<tr class="liste_titre">';
		print '<th class="center"><input type="checkbox" class="checkall"></th>';
		print '<th>' . $langs->trans("Réf.") . '</th><th>' . $langs->trans("Nom") . '</th><th>' . $langs->trans("Entrepôt") . '</th><th>' . $langs->trans("Fournisseur") . '</th><th>' . $langs->trans("Stock") . '</th><th>' . $langs->trans("Seuil alerte") . '</th>';
		print '</tr>';

		while ($obj = $db->fetch_object($resql)) {
			$stockClass = '';
			// Colorier uniquement les produits en alerte
			if (!empty($obj->seuil_stock_alerte) && $obj->reel !== null && $obj->reel <= $obj->seuil_stock_alerte) {
				$stockClass = ($obj->reel <= $obj->seuil_stock_alerte * 0.5) ? 'stock-critical' : 'stock-warning';
			}

			print '<tr class="oddeven">';
			print '<td class="center"><input type="checkbox" name="select_product[]" class="checkforselect" value="' . $obj->rowid . '"></td>';
			print '<td><a href="' . DOL_URL_ROOT . '/product/card.php?id=' . $obj->rowid . '">' . dol_escape_htmltag($obj->ref) . '</a></td>';
			print '<td>' . dol_escape_htmltag($obj->label) . '</td>';
			print '<td>' . dol_escape_htmltag($obj->entrepot_ref . ($obj->entrepot_lieu ? ' - ' . $obj->entrepot_lieu : '')) . '</td>';
			print '<td>' . dol_escape_htmltag($obj->fournisseur_nom) . '</td>';
			print '<td class="' . $stockClass . '">' . (isset($obj->reel) ? $obj->reel : '') . '</td>';
			print '<td>' . (isset($obj->seuil_stock_alerte) ? $obj->seuil_stock_alerte : '') . '</td>';
			print '</tr>';
		}

		print '</table>';
		if ($fk_fournisseur) {
			print '<div class="tabsAction right"><input type="submit" class="butAction" value="' . $langs->trans("Créer une commande fournisseur") . '"></div>';
		}
		print '</form>';
		print '<script>document.querySelector(".checkall").addEventListener("click",function(){document.querySelectorAll(".checkforselect").forEach(cb=>cb.checked=this.checked);});</script>';
	} else {
		$message = $show_all ? $langs->trans("AucunProduit") : $langs->trans("Aucun produit en alerte");
		print '<div class="opacitymedium">' . $message . '</div>';
	}
} else {
	dol_print_error($db);
}

llxFooter();
$db->close();
