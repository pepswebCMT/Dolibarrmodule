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

/*
 *	\file       stockalerte/stockalerteindex.php
 *	\ingroup    stockalerte
 *	\brief      Home page of stockalerte top menu
 */


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
			$sql = "SELECT p.rowid, p.ref, p.label, pfp.price, pe.conditionnementpalette as cond_pal,
                           pfp.ref_fourn, pfp.desc_fourn
                    FROM " . MAIN_DB_PREFIX . "product as p
                    LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price as pfp ON pfp.fk_product = p.rowid AND pfp.fk_soc = " . ((int) $fk_fournisseur) . "
                    LEFT JOIN " . MAIN_DB_PREFIX . "product_extrafields as pe ON pe.fk_object = p.rowid
                    WHERE p.rowid = " . ((int) $product_id) . "
                    ORDER BY pfp.quantity ASC LIMIT 1";

			$resql = $db->query($sql);
			if ($resql && ($obj = $db->fetch_object($resql))) {
				$qty_to_order = (!empty($obj->cond_pal) && is_numeric($obj->cond_pal)) ? (int)$obj->cond_pal : 1;

				// Construction de la description pour la ligne de commande
				$description = $obj->ref . ' - ' . $obj->label;
				if (!empty($obj->desc_fourn)) {
					$description .= ' - ' . $obj->desc_fourn;
				}

				// Ajout de la ligne avec description complète
				$order->addline(
					$description,           // Description avec référence produit
					$obj->price,           // Prix unitaire
					$qty_to_order,         // Quantité
					20,                    // Taux TVA
					0,                     // Remise ligne
					0,                     // Remise ligne 2
					$product_id,           // ID produit
					0,                     // ID variante
					$obj->ref_fourn,       // Référence fournisseur (Dolibarr l'affiche automatiquement)
					'',                    // Date de livraison
					'HT'                   // Type de prix
				);
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

$title = $show_all ? $langs->trans("Produits en stock") : $langs->trans("Produits en alerte de stock");
llxHeader("", $title);
print load_fiche_titre($title, '', 'product');

print '<style>.stock-critical{color:#cc0000;font-weight:bold}.stock-warning{color:#cc7a00;font-weight:bold}.stock-negative{color:#b50000;font-weight:bold;}</style>';
print '<div style="margin-bottom:10px;"><span style="margin-right:15px;"><span style="color:#cc0000;font-weight:bold;">■</span> ' . $langs->trans("Stock critique") . ' (< 50% du seuil)</span><span><span style="color:#cc7a00;font-weight:bold;">■</span> ' . $langs->trans("Stock en alerte") . ' (≥ 50% du seuil)</span></div>';

print '<form method="POST">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<div class="inline-block">' . $langs->trans("Entrepôt") . ': ' . $formproduct->selectWarehouses($fk_entrepot, 'fk_entrepot', '', 1) . '</div> ';
print '<div class="inline-block">' . $langs->trans("Fournisseur") . ': ' . $form->select_company($fk_fournisseur, 'fk_fournisseur', 's.fournisseur = 1', 1) . '</div> ';
print '<div class="inline-block"><label><input type="checkbox" name="show_all" value="1" ' . ($show_all ? 'checked' : '') . '> ' . $langs->trans("Afficher tous les produits") . '</label></div> ';
print '<input type="submit" name="search" class="button" value="' . $langs->trans("Filtrer") . '">';
print '</form><br>';


/**
 * Fonction pour calculer le stock virtuel d'un produit
 * @param int $product_id ID du produit
 * @param int $warehouse_id ID de l'entrepôt (0 pour tous)
 * @param object $db Connexion à la base de données
 * @return float Stock virtuel
 */
function calculateVirtualStockDetails($product_id, $db)
{
	$stocks = [];
	$stock_global = 0;

	// 1. Stock physique par entrepôt
	$sql_stock = "SELECT fk_entrepot, SUM(reel) as stock_reel
                  FROM " . MAIN_DB_PREFIX . "product_stock
                  WHERE fk_product = " . ((int)$product_id) . "
                  GROUP BY fk_entrepot";
	$res_stock = $db->query($sql_stock);

	if ($res_stock) {
		while ($obj = $db->fetch_object($res_stock)) {
			$stocks[(int)$obj->fk_entrepot] = [
				'stock_reel' => (float)$obj->stock_reel,
				'stock_virtuel' => 0,
			];
		}
	}

	// 2. Commandes clients (à déduire)
	$sql_cmd_client = "SELECT SUM(cd.qty) as qty_cmd_client
                       FROM " . MAIN_DB_PREFIX . "commandedet as cd
                       INNER JOIN " . MAIN_DB_PREFIX . "commande as c ON c.rowid = cd.fk_commande
                       WHERE cd.fk_product = " . ((int)$product_id) . "
                       AND c.fk_statut IN (1,2)";
	$res_client = $db->query($sql_cmd_client);
	$qty_client = ($res_client && ($obj = $db->fetch_object($res_client))) ? (float)$obj->qty_cmd_client : 0;

	// 3. Commandes fournisseurs (à ajouter)
	$sql_cmd_fourn = "SELECT SUM(cfd.qty) as qty_cmd_fourn
                      FROM " . MAIN_DB_PREFIX . "commande_fournisseurdet as cfd
                      INNER JOIN " . MAIN_DB_PREFIX . "commande_fournisseur as cf ON cf.rowid = cfd.fk_commande
                      WHERE cfd.fk_product = " . ((int)$product_id) . "
                      AND cf.fk_statut IN (3,4)";
	$res_fourn = $db->query($sql_cmd_fourn);
	$qty_fourn = ($res_fourn && ($obj = $db->fetch_object($res_fourn))) ? (float)$obj->qty_cmd_fourn : 0;

	// 4. Calcul du stock virtuel par entrepôt
	foreach ($stocks as $id_entrepot => &$data) {
		$data['stock_virtuel'] = $data['stock_reel'] - $qty_client + $qty_fourn;
		$stock_global += $data['stock_virtuel'];
	}

	return [
		'global' => $stock_global,
		'by_warehouse' => $stocks,
	];
}

// Construction de la requête SQL simplifiée
$sql = "SELECT 
    p.rowid, 
    p.ref, 
    p.label, 
    p.seuil_stock_alerte, 
    e.ref as entrepot_ref, 
    e.rowid as entrepot_id, 
    e.lieu as entrepot_lieu, 
    s.nom as fournisseur_nom, 
    s.rowid as fournisseur_id
FROM " . MAIN_DB_PREFIX . "product AS p

LEFT JOIN " . MAIN_DB_PREFIX . "product_stock AS ps ON ps.fk_product = p.rowid
LEFT JOIN " . MAIN_DB_PREFIX . "entrepot AS e ON e.rowid = ps.fk_entrepot
LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price AS pfp ON pfp.fk_product = p.rowid
LEFT JOIN " . MAIN_DB_PREFIX . "societe AS s ON s.rowid = pfp.fk_soc

WHERE p.tosell = 1
  AND p.seuil_stock_alerte IS NOT NULL
";

// Appliquer les filtres si définis
if ($fk_entrepot > 0) {
	$sql .= " AND ps.fk_entrepot = " . (int)$fk_entrepot;
}

if ($fk_fournisseur > 0) {
	$sql .= " AND pfp.fk_soc = " . (int)$fk_fournisseur;
}

$sql .= " GROUP BY p.rowid";
$sql .= " ORDER BY p.ref ASC";


// Exécution de la requête et affichage des résultats
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);

	// Tableau pour stocker les produits avec leur stock virtuel
	$products_data = array();

	while ($obj = $db->fetch_object($resql)) {
		// Calculer le stock virtuel
		$stock_details = calculateVirtualStockDetails($obj->rowid, $db);
		$stock_virtuel = ($fk_entrepot > 0 && isset($stock_details['by_warehouse'][$fk_entrepot]))
			? $stock_details['by_warehouse'][$fk_entrepot]['stock_virtuel']
			: $stock_details['global'];

		// Si on n'affiche que les alertes, filtrer selon le stock virtuel
		// Ne pas afficher les produits dont le stock virtuel total dépasse le seuil
		if (!$show_all && $obj->seuil_stock_alerte !== null) {
			if ($stock_virtuel > $obj->seuil_stock_alerte) {
				continue; // produit pas en alerte
			}

			// Exclure aussi seuil=0 et stock=0
			if ((float)$stock_virtuel === 0.0 && (float)$obj->seuil_stock_alerte === 0.0) {
				continue;
			}
		}


		// Ajouter les données du produit au tableau
		$products_data[] = array(
			'rowid' => $obj->rowid,
			'ref' => $obj->ref,
			'label' => $obj->label,
			'seuil_stock_alerte' => $obj->seuil_stock_alerte,
			'stock_reel' => $obj->reel,
			'stock_virtuel' => $stock_virtuel,
			'entrepot_ref' => $obj->entrepot_ref,
			'entrepot_lieu' => $obj->entrepot_lieu,
			'fournisseur_nom' => $obj->fournisseur_nom,
			'fournisseur_id' => $obj->fournisseur_id
		);
	}

	// Trier les produits par stock virtuel croissant si on affiche les alertes
	if (!$show_all) {
		usort($products_data, function ($a, $b) {
			return $a['stock_virtuel'] <=> $b['stock_virtuel'];
		});
	}

	if (count($products_data) > 0) {
		print '<form method="POST">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="create_order">';
		print '<input type="hidden" name="fk_entrepot" value="' . $fk_entrepot . '">';
		print '<input type="hidden" name="fk_fournisseur" value="' . $fk_fournisseur . '">';
		print '<input type="hidden" name="show_all" value="' . $show_all . '">';
		print '<table class="liste centpercent">';
		print '<tr class="liste_titre">';
		print '<th class="center"><input type="checkbox" class="checkall"></th>';
		print '<th>' . $langs->trans("Réf.") . '</th><th>' . $langs->trans("Nom") . '</th><th>' . $langs->trans("Entrepôt") . '</th><th>' . $langs->trans("Fournisseur") . '</th><th>' . $langs->trans("Stock virtuel") . '</th><th>' . $langs->trans("Seuil alerte") . '</th>';
		print '</tr>';

		foreach ($products_data as $product_data) {
			// Déterminer les classes CSS selon le stock virtuel
			$stockClass = '';
			$negativeClass = '';

			if ($product_data['seuil_stock_alerte'] !== null) {
				if ($product_data['stock_virtuel'] <= $product_data['seuil_stock_alerte'] * 0.5) {
					$stockClass = 'stock-critical';
				} elseif ($product_data['stock_virtuel'] <= $product_data['seuil_stock_alerte']) {
					$stockClass = 'stock-warning';
				}
			}

			if ($product_data['stock_virtuel'] < 0) {
				$negativeClass = 'stock-negative';
			} else {
				$negativeClass = $stockClass;
			}

			print '<tr class="oddeven">';
			print '<td class="center"><input type="checkbox" name="select_product[]" class="checkforselect" value="' . $product_data['rowid'] . '"></td>';
			print '<td><a href="' . DOL_URL_ROOT . '/product/card.php?id=' . $product_data['rowid'] . '">' . dol_escape_htmltag($product_data['ref']) . '</a></td>';
			print '<td>' . dol_escape_htmltag($product_data['label']) . '</td>';
			print '<td>' . dol_escape_htmltag($product_data['entrepot_ref'] . ($product_data['entrepot_lieu'] ? ' - ' . $product_data['entrepot_lieu'] : '')) . '</td>';
			print '<td>' . dol_escape_htmltag($product_data['fournisseur_nom']) . '</td>';
			print '<td class="' . $negativeClass . '" title="Stock physique: ' . $product_data['stock_reel'] . ' | Stock virtuel: ' . $product_data['stock_virtuel'] . '">' . $product_data['stock_virtuel'] . '</td>';
			print '<td>' . $product_data['seuil_stock_alerte'] . '</td>';
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







// require '../../main.inc.php';
// require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
// require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

// $langs->load("products");

// llxHeader("", "Alertes de stock virtuel");

// print load_fiche_titre("Produits avec alerte de stock virtuel");

// $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "product";
// $sql .= " WHERE entity = " . (int) $conf->entity;
// $sql .= " AND fk_product_type = 0"; // Produits uniquement (pas services)

// $resql = $db->query($sql);

// if ($resql) {
// 	print '<table class="liste centpercent">';
// 	print '<tr class="liste_titre">';
// 	print '<th>Réf</th>
// 		<th>Nom</th>
// 		<th>Stock réel</th>
// 		<th>Stock virtuel</th>
// 		<th>Seuil alerte</th>
// 		<th></th>';
// 	print '
// 		</tr>';

// 	while ($obj = $db->fetch_object($resql)) {
// 		$product = new Product($db);
// 		if ($product->fetch($obj->rowid) > 0) {
// 			$product->load_stock(); // stock réel
// 			$product->load_virtual_stock(); // stock virtuel

// 			if ($product->stock_theorique < $product->seuil_stock_alerte) {
// 				print '<tr>';
// 				print '<td>' . $product->ref . '</td>';
// 				print '<td>' . $product->label . '</td>';
// 				print '<td>' . $product->stock_reel . '</td>';
// 				print '<td>' . $product->stock_theorique . '</td>';
// 				print '<td>' . $product->seuil_stock_alerte . '</td>';
// 				print '<td><a href="' . DOL_URL_ROOT . '/product/card.php?id=' . $product->id . '">Voir</a></td>';
// 				print '</tr>';
// 			}
// 		}
// 	}

// 	print '</table>';
// } else {
// 	print "Erreur SQL : " . $db->lasterror();
// }

// llxFooter();
// $db->close();
