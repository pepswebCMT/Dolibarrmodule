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
 *	\file       bilancarbonne/bilancarbonneindex.php
 *	\ingroup    bilancarbonne
 *	\brief      Home page of bilancarbonne top menu
 */

// Load Dolibarr environment
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
// 	$res = require_once substr($tmp, 0, ($i + 1)) . "/main.inc.php";
// }
// if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
// 	$res = require_once dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// }
// // Try main.inc.php using relative path
// if (!$res && file_exists("../main.inc.php")) {
// 	$res = require_once "../main.inc.php";
// }
// if (!$res && file_exists("../../main.inc.php")) {
// 	$res = require_once "../../main.inc.php";
// }
// if (!$res && file_exists("../../../main.inc.php")) {
// 	$res = require_once "../../../main.inc.php";
// }
// if (!$res) {
// 	die("Include of main fails");
// }

// require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// // Load translation files required by the page
// $langs->loadLangs(array("bilancarbonne@bilancarbonne"));

// $action = GETPOST('action', 'aZ09');

// $max = 5;
// $now = dol_now();

// // Security check - Protection if external user
// $socid = GETPOST('socid', 'int');
// if (isset($user->socid) && $user->socid > 0) {
// 	$action = '';
// 	$socid = $user->socid;
// }

// if ($user->socid > 0) {
// 	accessforbidden();
// }



// $form = new Form($db);
// $formfile = new FormFile($db);

// llxHeader("", $langs->trans("BilanCarbonneArea"), '', '', 0, 0, '', '', '', 'mod-bilancarbonne page-index');

// print load_fiche_titre($langs->trans("BilanCarbonneArea"), '', 'bilancarbonne.png@bilancarbonne');


// $NBMAX = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');
// $max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');

// // Titre de la page
// $title = "Liste des commandes avec détails produits";
// print load_fiche_titre($title);

// // Affichage des commandes sous forme de tableau
// print '<table class="noborder" width="100%">';

// print '<form method="GET" action="">';
// print '<input type="hidden" name="action" value="list">';
// print '<input type="hidden" name="year" value="' . htmlspecialchars($year) . '">';
// print '<input type="hidden" name="page" value="' . $page . '">';
// print '<label for="limit">Lignes par page :</label>';
// print '<select name="limit" id="limit" onchange="this.form.submit();">';
// print '<option value="10"' . ($limit == 10 ? ' selected' : '') . '>10</option>';
// print '<option value="25"' . ($limit == 25 ? ' selected' : '') . '>25</option>';
// print '<option value="50"' . ($limit == 50 ? ' selected' : '') . '>50</option>';
// print '<option value="100"' . ($limit == 100 ? ' selected' : '') . '>100</option>';
// print '<option value="125"' . ($limit == 125 ? ' selected' : '') . '>125</option>';
// print '</select>';
// print '</form>';



// print '<table class="noborder" width="100%">';
// print '<tr class="liste_titre">';
// print '<th>ID Commande</th><th>Référence Produit</th><th>Quantité</th><th>Poids (kg)</th>';
// print '<th>Client</th><th>Adresse client</th><th>Adresse fournisseur</th><th>Distance (km)</th>';
// print '</tr>';

// foreach ($orders as $order) {
// 	$clientAddress = "{$order->address}, {$order->zip} {$order->town}";
// 	$supplierAddress = "{$order->fournisseur_address}, {$order->fournisseur_zip} {$order->fournisseur_town}";

// 	// Calculer la distance
// 	$distance = $orderModel->calculateDistance($clientAddress, $supplierAddress);

// 	print '<tr>';
// 	print '<td>' . $order->ref . '</td>';
// 	print '<td>' . $order->product_ref . '</td>';
// 	print '<td>' . $order->qty . '</td>';
// 	print '<td>' . $order->weight . '</td>';
// 	print '<td>' . $order->nom . '</td>';
// 	print '<td>' . $clientAddress . '</td>';
// 	print '<td>' . $supplierAddress . '</td>';
// 	print '<td>' . ($distance ? round($distance, 2) . ' km' : 'Distance non calculée') . '</td>';
// 	print '</tr>';
// }


// print 'Page: ' . $page . '<br>';
// print 'Year: ' . $year . '<br>';
// print 'Offset: ' . $offset . '<br>';
// print 'Total Pages: ' . $total_pages . '<br>';


// // Pagination
// print '<div class="pagination">';
// // Formulaire pour la page précédente
// if ($page > 0) {
// 	print '<form method="POST" action="" style="display: inline;">';
// 	print '<input type="hidden" name="action" value="list">';
// 	print '<input type="hidden" name="page" value="' . ($page - 1) . '">';
// 	print '<button type="submit">&laquo; Précédent</button>';
// 	print '</form>';
// }

// // Affichage de la page actuelle
// print '<span>Page ' . ($page + 1) . ' / ' . $total_pages . '</span>';

// // Formulaire pour la page suivante
// if ($page < $total_pages - 1) {
// 	print '<form method="POST" action="" style="display: inline;">';
// 	print '<input type="hidden" name="action" value="list">';
// 	print '<input type="hidden" name="page" value="' . ($page + 1) . '">';
// 	print '<button type="submit">Suivant &raquo;</button>';
// 	print '</form>';
// }

// print '</div>';
// print '</div>';


// llxFooter();

// template/listorders.php

llxHeader('', $langs->trans("OrdersList"));

print load_fiche_titre($langs->trans("OrdersList"));
?>
<script type="text/javascript">
	function calculateDistance(orderId, clientAddress, supplierAddress, rowIndex) {
		console.log('Calculating distance for:', {
			orderId,
			clientAddress,
			supplierAddress,
			rowIndex
		});

		fetch('ordersController.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					'action': 'calculate_distance',
					'order_id': orderId,
					'client_address': clientAddress,
					'supplier_address': supplierAddress
				})
			})
			.then(response => {
				console.log('Response status:', response.status);
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then(data => {
				console.log('Response data:', data);
				// Utiliser l'ID unique avec rowIndex
				const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
				if (data.distance) {
					distanceCell.textContent = `${data.distance} km`;
				} else {
					distanceCell.textContent = 'Distance non calculée';
				}
				distanceCell.classList.remove('calculating');
			})
			.catch(error => {
				console.error('Erreur:', error);
				const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
				distanceCell.textContent = 'Erreur de calcul';
				distanceCell.classList.remove('calculating');


			});
	}

	function processDistanceCalculations() {
		const orders = document.querySelectorAll('[data-needs-calculation="true"]');
		let index = 0;

		function calculateNext() {
			if (index < orders.length) {
				const order = orders[index];
				calculateDistance(
					order.dataset.orderId,
					order.dataset.clientAddress,
					order.dataset.supplierAddress,
					order.dataset.rowIndex
				);
				index++;
				setTimeout(calculateNext, 1000);
			}
		}
		calculateNext();
	}
</script>

<style>
	.calculating {
		position: relative;
	}

	.calculating:after {
		content: 'Calcul en cours...';
		font-style: italic;
		color: #666;
	}
</style>

<?php
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<th>ID Commande</th><th>Référence Produit</th><th>Quantité</th><th>Poids (kg)</th>';
print '<th>Client</th><th>Adresse client</th><th>Adresse fournisseur</th><th>Distance (km)</th>';
print '</tr>';

$rowIndex = 0;
foreach ($orders as $order) {
	$clientAddress = "{$order->address}, {$order->zip} {$order->town}";
	$supplierAddress = "{$order->fournisseur_address}, {$order->fournisseur_zip} {$order->fournisseur_town}";

	print '<tr>';
	print '<td>' . $order->ref . '</td>';
	print '<td>' . $order->product_ref . '</td>';
	print '<td>' . $order->qty . '</td>';
	print '<td>' . $order->weight . '</td>';
	print '<td>' . $order->nom . '</td>';
	print '<td>' . $clientAddress . '</td>';
	print '<td>' . $supplierAddress . '</td>';
	// Utiliser un id unique avec rowIndex
	print '<td id="distance_' . $order->rowid . '_' . $rowIndex . '" class="calculating" 
        data-needs-calculation="true"
        data-order-id="' . $order->rowid . '"
        data-client-address="' . htmlspecialchars($clientAddress) . '"
        data-supplier-address="' . htmlspecialchars($supplierAddress) . '"
        data-row-index="' . $rowIndex . '">
     
    </td>';
	print '</tr>';

	$rowIndex++;
}

print '</table>';

print '<script>document.addEventListener("DOMContentLoaded", processDistanceCalculations);</script>';

llxFooter();
