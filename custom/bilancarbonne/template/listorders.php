<?php

// Chargement des headers et du titre
llxHeader('', $langs->trans("OrdersList"));

print load_fiche_titre($langs->trans("Calcul du Bilan Carbone"));

// Paramètres pour les résultats par page
$resultsPerPageOptions = [10, 25, 50, 100];
?>

<!-- Styles CSS -->
<style>
	.distance {
		color: lightblue !important;
		font-weight: bold;
	}

	.co2 {
		color: lightagreen !important;
		font-weight: bold;
	}

	.error {
		color: red !important;
		font-weight: bold;
	}

	.calculating {
		color: #666 !important;
		font-style: italic;
	}


	.pagination {
		margin: 20px 0;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.pagination .button {
		text-decoration: none;
		padding: 8px 16px;
		border: 1px solid #ccc;
		border-radius: 4px;
		background-color: #f5f5f5;
		color: #333;
		cursor: pointer;
	}
</style>

<!-- Facteur d'émission -->
<div class="emission-input-container">
	<label for="global_emission_factor">Facteur d'émission (g/tkm) :</label>
	<input type="number" id="global_emission_factor" value="80.8" step="0.1" min="0">
</div>

<!-- Pagination -->
<div class="pagination">
	<?php if ($page > 0): ?>
		<a href="?page=<?= $page - 1 ?>&year=<?= $year ?>&limit=<?= $limit ?>" class="button">Page précédente</a>
	<?php else: ?>
		<span class="button" style="opacity: 0.5; cursor: not-allowed;">Page précédente</span>
	<?php endif; ?>

	<span>Page <?= $page + 1 ?></span>

	<a href="?page=<?= $page + 1 ?>&year=<?= $year ?>&limit=<?= $limit ?>" class="button">Page suivante</a>
</div>

<!-- Sélection du nombre de résultats par page -->
<div class="results-per-page">
	<label for="results_per_page">Résultats par page :</label>
	<select id="results_per_page">
		<?php foreach ($resultsPerPageOptions as $option): ?>
			<option value="<?= $option ?>" <?= ($limit == $option) ? 'selected' : '' ?>><?= $option ?></option>
		<?php endforeach; ?>
	</select>
</div>

<!-- Table des commandes -->
<table class="noborder" width="100%">
	<tr class="liste_titre">
		<th>Numéro Commande</th>
		<th>Référence Produit</th>
		<th>Quantité</th>
		<th>Poids (kg)</th>
		<th>Client</th>
		<th>Adresse Client</th>
		<th>Adresse Fournisseur</th>
		<th>Distance (km)</th>
		<th>Bilan Carbone (kg CO₂)</th>
	</tr>
	<?php
	$rowIndex = 0;
	foreach ($orders as $order):
		$clientAddress = "{$order->address}, {$order->zip} {$order->town}";
		$supplierAddress = "{$order->fournisseur_address}, {$order->fournisseur_zip} {$order->fournisseur_town}";
		$transitAddress = $order->transit_address;
	?>
		<tr>
			<td><?= $order->ref ?></td>
			<td><?= $order->product_refs ?></td>
			<td><?= $order->total_qty ?></td>
			<td><?= round($order->total_weight, 2) ?></td>
			<td><?= $order->nom ?></td>
			<td><?= $clientAddress ?></td>
			<td><?= $supplierAddress ?></td>
			<?php if (!empty($order->distance) && is_numeric($order->distance) && !empty($order->co2) && is_numeric($order->co2)): ?>
				<!-- Afficher les distances calculées -->
				<td id="distance_<?= $order->rowid ?>_<?= $rowIndex ?>" class="success distance"><?= round($order->distance, 2) ?> km</td>
				<td id="co2_<?= $order->rowid ?>_<?= $rowIndex ?>" class="success co2"><?= round($order->co2, 2) ?> kg CO₂</td>

			<?php else: ?>
				<!-- Indiquer que le calcul est en attente -->
				<td id="distance_<?= $order->rowid ?>_<?= $rowIndex ?>" class="calculating"
					data-needs-calculation="true"
					data-order-id="<?= $order->rowid ?>"
					data-client-address="<?= htmlspecialchars($clientAddress) ?>"
					data-supplier-address="<?= htmlspecialchars($supplierAddress) ?>"
					data-transit-address="<?= htmlspecialchars($transitAddress) ?>"
					data-row-index="<?= $rowIndex ?>">
					En attente...
				</td>
				<td id="co2_<?= $order->rowid ?>_<?= $rowIndex ?>">En attente...</td>
			<?php endif; ?>
		</tr>
	<?php $rowIndex++;
	endforeach; ?>
</table>

<!-- Script JS -->
<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Initialisation des événements pour la pagination
		document.getElementById('results_per_page').addEventListener('change', function() {
			window.location.href = '?year=<?= $year ?>&limit=' + this.value;
		});

		// Lancer le calcul des distances
		processDistanceCalculations();
	});

	function processDistanceCalculations() {
		const orders = document.querySelectorAll('[data-needs-calculation="true"]');
		const emissionFactor = document.getElementById('global_emission_factor').value;

		if (orders.length === 0) {
			console.log('Aucun calcul nécessaire.');
			return;
		}

		orders.forEach(order => {
			const distanceCell = document.getElementById(`distance_${order.dataset.orderId}_${order.dataset.rowIndex}`);
			const co2Cell = document.getElementById(`co2_${order.dataset.orderId}_${order.dataset.rowIndex}`);

			calculateDistance(
					order.dataset.orderId,
					order.dataset.clientAddress,
					order.dataset.supplierAddress,
					order.dataset.transitAddress,
					emissionFactor
				)
				.then(data => {
					if (data.success && !isNaN(parseFloat(data.distance)) && !isNaN(parseFloat(data.co2))) {
						const distanceValue = parseFloat(data.distance);
						const co2Value = parseFloat(data.co2);

						// Mise à jour des cellules
						distanceCell.textContent = `${distanceValue.toFixed(2)} km`;
						co2Cell.textContent = `${co2Value.toFixed(2)} kg CO₂`;

						// Supprime les anciennes classes et ajoute les nouvelles
						distanceCell.classList.remove('calculating', 'error', 'co2');
						distanceCell.classList.add('success', 'distance'); // Distance en bleu

						co2Cell.classList.remove('calculating', 'error', 'distance');
						co2Cell.classList.add('success', 'co2'); // CO₂ en vert
					} else {
						console.error(`Erreur sur la commande ${order.dataset.orderId} :`, data);
						distanceCell.textContent = 'Erreur (données invalides)';
						co2Cell.textContent = 'Erreur (données invalides)';

						// En cas d'erreur, on applique la classe "error"
						distanceCell.classList.remove('success', 'distance', 'co2');
						distanceCell.classList.add('error');

						co2Cell.classList.remove('success', 'co2', 'distance');
						co2Cell.classList.add('error');
					}

				})
				.catch(error => {
					// Afficher des informations claires sur l'erreur dans la console et dans l'interface utilisateur
					console.error(`Erreur lors du calcul de la commande ${order.dataset.orderId}:`, error);
					distanceCell.textContent = 'Erreur de calcul';
					co2Cell.textContent = 'Erreur de calcul';
					distanceCell.classList.add('error');
				});
		});
	}

	function calculateDistance(orderId, clientAddress, supplierAddress, transitAddress, emissionFactor) {
		return new Promise((resolve, reject) => {
			console.log('Envoi des paramètres pour le calcul :', {
				orderId,
				clientAddress,
				supplierAddress,
				transitAddress,
				emissionFactor
			});

			fetch('ordersController.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: new URLSearchParams({
						action: 'calculate_distance',
						order_id: orderId,
						client_address: clientAddress,
						supplier_address: supplierAddress,
						transit_address: transitAddress,
						emission_factor: emissionFactor
					})
				})
				.then(response => {
					if (!response.ok) {
						throw new Error(`Erreur HTTP ${response.status} : ${response.statusText}`);
					}
					return response.json();
				})
				.then(data => {
					console.log(`Réponse du serveur pour la commande ${orderId} :`, data);
					resolve(data);
				})
				.catch(error => {
					console.error(`Erreur lors de la requête pour la commande ${orderId}:`, error);
					reject(error);
				});
		});
	}
</script>

<?php
// Afficher le footer
llxFooter();
