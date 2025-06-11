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
 *	\file       ecartsurcommande/ecartsurcommandeindex.php
 *	\ingroup    ecartsurcommande
 *	\brief      Home page of ecartsurcommande top menu
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
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';

// Load translation files required by the page
$langs->loadLangs(array("ecartsurcommande@ecartsurcommande", "orders", "bills", "suppliers"));

$action = GETPOST('action', 'aZ09');

// Récupération des paramètres de filtre par date et pagination
$date_start = GETPOST('date_start', 'alpha');
$date_end = GETPOST('date_end', 'alpha');
$page = GETPOSTINT('page') ? GETPOSTINT('page') : 0;
$limit = 250; // Nombre de lignes par page
$offset = $limit * $page;

$date_start_dt = '';
$date_end_dt = '';

if ($date_start) {
	$date_start_dt = dol_stringtotime($date_start, 1);
}
if ($date_end) {
	$date_end_dt = dol_stringtotime($date_end, 1);
}

$max = 250; // Nombre maximum d'écarts à afficher (sera remplacé par la pagination)
$now = dol_now();

// Security check - Protection if external user
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//if (!isModEnabled('ecartsurcommande')) {
//	accessforbidden('Module not enabled');
//}
//if (! $user->hasRight('ecartsurcommande', 'myobject', 'read')) {
//	accessforbidden();
//}

/*
 * Actions
 */

// None

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("Écarts entre commandes et factures",), '', '', 0, 0, '', '', '', 'mod-ecartsurcommande page-index');

print load_fiche_titre($langs->trans("Écarts entre commandes et factures"), '', 'ecartsurcommande.png@ecartsurcommande');

// Ajout de styles CSS personnalisés pour les écarts
print '<style>
.ecart-positif {
    color: #c62828 !important;
    font-weight: bold !important;
}
.ecart-negatif {
    color: #2e7d32 !important;
    font-weight: bold !important;
}
.ecart-nul {
    color: #666 !important;
    font-weight: bold !important;
}
.factures-details {
    font-size: 0.9em;
    color: #666;
    margin-top: 3px;
}
.filter-simple {
    
    border: 1px solid #dee2e6;
    border-radius: 3px;
    padding: 8px 12px;
    margin-bottom: 15px;
    font-size: 0.9em;
}
.filter-simple table {
    margin: 0;
}
.filter-simple td {
    padding: 2px 8px;
    vertical-align: middle;
}
.filter-simple .button {
    font-size: 0.85em;
    padding: 4px 8px;
}
</style>';

print '<div class="fichecenter">';

// Reset des filtres si demandé
if (GETPOST('button_removefilter', 'alpha')) {
	$date_start = '';
	$date_end = '';
	$date_start_dt = '';
	$date_end_dt = '';
	$page = 0; // Reset de la page
}

// Formulaire de filtre par date - VERSION SIMPLIFIÉE
print '<div class="filter-simple">';
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '" style="margin: 0;">';
print '<input type="hidden" name="page" value="' . $page . '">';
print '<table style="width: 100%;">';
print '<tr>';
print '<td style="width: auto;"><strong>Filtrer par date des factures :</strong></td>';
print '<td style="width: 120px;">Du : ' . $form->selectDate($date_start_dt, 'date_start', 0, 0, 1, '', 1, 0) . '</td>';
print '<td style="width: 120px;">Au : ' . $form->selectDate($date_end_dt, 'date_end', 0, 0, 1, '', 1, 0) . '</td>';
print '<td style="width: auto;">';
print '<input type="submit" class="button" value="' . $langs->trans("Filter") . '">';
print ' <input type="submit" class="button button-cancel" name="button_removefilter" value="' . $langs->trans("RemoveFilter") . '">';
print '</td>';
print '</tr>';
print '</table>';
print '</form>';
print '</div>';

// Affichage du tableau des écarts sur commandes fournisseur
if (isModEnabled('supplier_order') && isModEnabled('supplier_invoice')) {

	// Requête pour récupérer les commandes fournisseur avec toutes leurs factures associées
	$sql = "SELECT";
	$sql .= " cf.rowid as commande_id,";
	$sql .= " cf.ref as commande_ref,";
	$sql .= " cf.date_commande,";
	$sql .= " cf.total_ht as commande_ht,";
	$sql .= " cf.total_ttc as commande_ttc,";
	$sql .= " cf.billed as commande_facturee,";  // Ajout du champ facturé
	$sql .= " ff.rowid as facture_id,";
	$sql .= " ff.ref as facture_ref,";
	$sql .= " ff.datef as facture_date,";
	$sql .= " ff.total_ht as facture_ht,";
	$sql .= " ff.total_ttc as facture_ttc,";
	$sql .= " s.nom as fournisseur_nom,";
	$sql .= " s.rowid as fournisseur_id";
	$sql .= " FROM " . MAIN_DB_PREFIX . "commande_fournisseur as cf";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "element_element as ee ON (ee.fk_source = cf.rowid AND ee.sourcetype = 'order_supplier' AND ee.targettype = 'invoice_supplier')";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "facture_fourn as ff ON ee.fk_target = ff.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON cf.fk_soc = s.rowid";
	$sql .= " WHERE cf.entity IN (" . getEntity('supplier_order') . ")";
	$sql .= " AND cf.fk_statut IN (3, 4, 5)"; // 3=Commandé, 4=Reçue partiellement, 5=Produits reçus
	$sql .= " AND cf.billed = 1"; // Seulement les commandes marquées comme facturées

	// Si on filtre par date, on n'affiche que les commandes qui ont des factures dans cette période
	$has_date_filter = false;
	if ($date_start_dt || $date_end_dt) {
		$has_date_filter = true;
		$sql .= " AND ff.rowid IS NOT NULL"; // Exclure les commandes sans factures quand on filtre par date
	}

	// Filtre par date des factures si spécifié
	if ($date_start_dt) {
		$sql .= " AND ff.datef >= '" . $db->idate($date_start_dt) . "'";
	}
	if ($date_end_dt) {
		$sql .= " AND ff.datef <= '" . $db->idate($date_end_dt) . "'";
	}

	if ($socid > 0) {
		$sql .= " AND cf.fk_soc = " . ((int) $socid);
	}
	// Tri conditionnel : si filtre par date, trier par date de facture croissante
	if ($date_start_dt || $date_end_dt) {
		$sql .= " ORDER BY ff.datef ASC, cf.date_commande ASC, cf.ref ASC";
	} else {
		$sql .= " ORDER BY cf.date_commande DESC, cf.ref DESC";
	}

	// Pas de limite dans la requête SQL car on va faire la pagination après regroupement

	$resql = $db->query($sql);

	if ($resql) {
		$num = $db->num_rows($resql);

		// Regroupement des données par commande
		$commandes_data = array();
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resql);

			$commande_id = $obj->commande_id;

			// Si la commande n'existe pas encore dans notre tableau, on l'initialise
			if (!isset($commandes_data[$commande_id])) {
				$commandes_data[$commande_id] = array(
					'commande_id' => $obj->commande_id,
					'commande_ref' => $obj->commande_ref,
					'date_commande' => $obj->date_commande,
					'commande_ht' => $obj->commande_ht,
					'commande_ttc' => $obj->commande_ttc,
					'commande_facturee' => $obj->commande_facturee,
					'fournisseur_nom' => $obj->fournisseur_nom,
					'fournisseur_id' => $obj->fournisseur_id,
					'factures' => array(),
					'total_factures_ht' => 0,
					'date_facturation_max' => null // Pour stocker la date de facturation la plus récente
				);
			}

			// Ajout de la facture au tableau des factures de cette commande (si elle existe)
			if ($obj->facture_id) {
				$commandes_data[$commande_id]['factures'][] = array(
					'facture_id' => $obj->facture_id,
					'facture_ref' => $obj->facture_ref,
					'facture_date' => $obj->facture_date,
					'facture_ht' => $obj->facture_ht,
					'facture_ttc' => $obj->facture_ttc
				);

				// Cumul du total des factures
				$commandes_data[$commande_id]['total_factures_ht'] += $obj->facture_ht;

				// Mise à jour de la date de facturation la plus récente
				if (
					!$commandes_data[$commande_id]['date_facturation_max'] ||
					$db->jdate($obj->facture_date) > $db->jdate($commandes_data[$commande_id]['date_facturation_max'])
				) {
					$commandes_data[$commande_id]['date_facturation_max'] = $obj->facture_date;
				}
			}

			$i++;
		}

		// Limitation au nombre max après regroupement - PAGINATION
		$total_commandes = count($commandes_data);
		$commandes_data = array_slice($commandes_data, $offset, $limit, true);

		// Calcul du nombre de pages
		$nbtotalofpages = ceil($total_commandes / $limit);

		// Affichage des informations de pagination
		if ($total_commandes > $limit) {
			print '<div class="center">';
			print '<strong>Page ' . ($page + 1) . ' sur ' . $nbtotalofpages . ' (' . $total_commandes . ' commandes au total)</strong>';
			print '</div><br>';
		}

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th>' . $langs->trans("SupplierOrder") . '</th>';
		print '<th>' . $langs->trans("Supplier") . '</th>';
		print '<th>' . $langs->trans("Date") . '</th>';
		print '<th>Date Facturation</th>'; // NOUVELLE COLONNE

		print '<th class="right">' . $langs->trans("AmountHT") . '</th>';
		print '<th>' . $langs->trans("SupplierInvoices") . '</th>';
		print '<th class="right">Total Factures HT</th>';
		print '<th class="right">Écart</th>';
		print '<th class="right">%</th>';
		print '</tr>';

		if (count($commandes_data) > 0) {
			$total_ecart = 0;
			$nb_ecarts = 0;

			foreach ($commandes_data as $commande) {
				// Calcul de l'écart entre commande et total des factures
				$ecart_ht = $commande['total_factures_ht'] - $commande['commande_ht'];
				$ecart_percent = ($commande['commande_ht'] != 0) ? round(($ecart_ht / $commande['commande_ht']) * 100, 2) : 0;

				// Couleur selon l'écart
				if ($ecart_ht > 0) {
					$css_class = 'ecart-positif'; // Rouge pour écart positif (plus cher)
				} elseif ($ecart_ht < 0) {
					$css_class = 'ecart-negatif'; // Vert pour écart négatif (moins cher)
				} else {
					$css_class = 'ecart-nul'; // Gris pour écart nul
				}

				$total_ecart += abs($ecart_ht);
				$nb_ecarts++;

				print '<tr class="oddeven">';

				// Commande fournisseur
				print '<td>';
				print '<a href="' . DOL_URL_ROOT . '/fourn/commande/card.php?id=' . $commande['commande_id'] . '">';
				print $commande['commande_ref'];
				print '</a>';
				print '</td>';

				// Fournisseur
				print '<td>';
				if ($commande['fournisseur_id']) {
					print '<a href="' . DOL_URL_ROOT . '/societe/card.php?socid=' . $commande['fournisseur_id'] . '">';
					print dol_trunc($commande['fournisseur_nom'], 25);
					print '</a>';
				}
				print '</td>';

				// Date commande
				print '<td>' . dol_print_date($db->jdate($commande['date_commande']), 'day') . '</td>';

				// Date de facturation (NOUVELLE COLONNE)
				print '<td>';
				if ($commande['date_facturation_max']) {
					print dol_print_date($db->jdate($commande['date_facturation_max']), 'day');
				} else {
					print '<span class="opacitymedium">-</span>';
				}
				print '</td>';


				// Montant HT commande
				print '<td class="right">' . price($commande['commande_ht']) . '</td>';

				// Factures fournisseur (affichage de toutes les factures)
				print '<td>';
				if (count($commande['factures']) > 0) {
					$factures_html = '';
					foreach ($commande['factures'] as $facture) {
						if (!empty($factures_html)) $factures_html .= '<br>';
						$factures_html .= '<a href="' . DOL_URL_ROOT . '/fourn/facture/card.php?facid=' . $facture['facture_id'] . '">';
						$factures_html .= $facture['facture_ref'];
						$factures_html .= '</a>';
						$factures_html .= ' (' . dol_print_date($db->jdate($facture['facture_date']), 'day') . ')';
						$factures_html .= ' - ' . price($facture['facture_ht']);
					}
					print '<div class="factures-details">' . $factures_html . '</div>';
					if (count($commande['factures']) > 1) {
						print '<small>(' . count($commande['factures']) . ' factures)</small>';
					}
				} else {
					print '<span class="opacitymedium">Aucune facture liée</span>';
				}
				print '</td>';

				// Total des factures HT
				print '<td class="right">';
				if ($commande['total_factures_ht'] > 0) {
					print '<strong>' . price($commande['total_factures_ht']) . '</strong>';
				} else {
					print '<span class="opacitymedium">0,00 €</span>';
				}
				print '</td>';

				// Écart
				print '<td class="right ' . $css_class . '">';
				print price($ecart_ht);
				print '</td>';

				// Pourcentage d'écart
				print '<td class="right ' . $css_class . '">';
				print $ecart_percent . '%';
				print '</td>';

				print '</tr>';
			}

			// Ligne de total
			if ($nb_ecarts > 0) {
				print '<tr class="liste_total">';
				print '<td colspan="8">Écart moyen</td>';
				print '<td class="right"><strong>' . price($total_ecart / $nb_ecarts) . '</strong></td>';
				print '<td></td>';
				print '</tr>';
			}
		} else {
			print '<tr class="oddeven">';
			print '<td colspan="10" class="opacitymedium center">';
			if ($has_date_filter) {
				print 'Aucune commande facturée avec des factures trouvée pour la période sélectionnée';
			} else {
				print 'Aucune commande facturée trouvée';
			}
			print '</td>';
			print '</tr>';
		}

		print "</table>";

		// Navigation par pages
		if ($total_commandes > $limit) {
			print '<div class="center" style="margin-top: 20px;">';

			// Lien vers la première page
			if ($page > 0) {
				print '<a class="button" href="' . $_SERVER["PHP_SELF"] . '?page=0';
				if ($date_start) print '&date_start=' . urlencode($date_start);
				if ($date_end) print '&date_end=' . urlencode($date_end);
				print '">« Première</a> ';
			}

			// Lien vers la page précédente
			if ($page > 0) {
				print '<a class="button" href="' . $_SERVER["PHP_SELF"] . '?page=' . ($page - 1);
				if ($date_start) print '&date_start=' . urlencode($date_start);
				if ($date_end) print '&date_end=' . urlencode($date_end);
				print '">‹ Précédente</a> ';
			}

			// Pages autour de la page courante
			$start_page = max(0, $page - 2);
			$end_page = min($nbtotalofpages - 1, $page + 2);

			for ($i = $start_page; $i <= $end_page; $i++) {
				if ($i == $page) {
					print '<strong class="button button-primary">' . ($i + 1) . '</strong> ';
				} else {
					print '<a class="button" href="' . $_SERVER["PHP_SELF"] . '?page=' . $i;
					if ($date_start) print '&date_start=' . urlencode($date_start);
					if ($date_end) print '&date_end=' . urlencode($date_end);
					print '">' . ($i + 1) . '</a> ';
				}
			}

			// Lien vers la page suivante
			if ($page < ($nbtotalofpages - 1)) {
				print '<a class="button" href="' . $_SERVER["PHP_SELF"] . '?page=' . ($page + 1);
				if ($date_start) print '&date_start=' . urlencode($date_start);
				if ($date_end) print '&date_end=' . urlencode($date_end);
				print '">Suivante ›</a> ';
			}

			// Lien vers la dernière page
			if ($page < ($nbtotalofpages - 1)) {
				print '<a class="button" href="' . $_SERVER["PHP_SELF"] . '?page=' . ($nbtotalofpages - 1);
				if ($date_start) print '&date_start=' . urlencode($date_start);
				if ($date_end) print '&date_end=' . urlencode($date_end);
				print '">Dernière »</a>';
			}

			print '</div>';
		}

		$db->free($resql);
	} else {
		dol_print_error($db);
	}
} else {
	print '<div class="warning">';
	print $langs->trans("ModulesSupplierOrderAndInvoiceRequired");
	print '</div>';
}

print '</div>';

// End of page
llxFooter();
$db->close();
