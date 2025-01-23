<?php

llxHeader('', $langs->trans("OrdersList"));

print load_fiche_titre($langs->trans("OrdersList"));
?>
<script type="text/javascript">
	function processDistanceCalculations() {
		const orders = document.querySelectorAll('[data-needs-calculation="true"]');
		let index = 0;
		const maxRetries = 3; // Nombre maximal de tentatives

		function calculateNext() {
			if (index < orders.length) {
				const order = orders[index];
				const distanceCell = document.getElementById(`distance_${order.dataset.orderId}_${order.dataset.rowIndex}`);

				// Réinitialiser le nombre de tentatives pour chaque ordre
				let retryCount = 0;

				function attemptCalculation() {
					calculateDistance(
						order.dataset.orderId,
						order.dataset.clientAddress,
						order.dataset.supplierAddress,
						order.dataset.rowIndex
					).then(success => {
						if (success) {
							index++;
							setTimeout(calculateNext, 1000);
						} else if (retryCount < maxRetries) {
							retryCount++;
							setTimeout(attemptCalculation, 2000); // Délai entre les tentatives
						} else {
							// Échec après plusieurs tentatives
							distanceCell.textContent = 'Distance non calculable';
							index++;
							setTimeout(calculateNext, 1000);
						}
					});
				}

				attemptCalculation();
			}
		}

		calculateNext();
	}

	function calculateDistance(orderId, clientAddress, supplierAddress, rowIndex) {
		return new Promise((resolve) => {
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
					if (!response.ok) {
						throw new Error(`Erreur HTTP: ${response.status}`);
					}
					return response.json();
				})
				.then(data => {
					const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
					if (data.distance) {
						distanceCell.textContent = `${data.distance} km`;
						distanceCell.classList.remove('calculating');
						resolve(true);
					} else {
						distanceCell.textContent = 'Distance non calculée';
						distanceCell.classList.remove('calculating');
						resolve(false);
					}
				})
				.catch(error => {
					console.error('Erreur:', error);
					const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
					distanceCell.textContent = 'Erreur de calcul';
					distanceCell.classList.remove('calculating');
					resolve(false);
				});
		});
	}
	// function calculateDistance(orderId, clientAddress, supplierAddress, rowIndex) {
	// 	console.log('Calculating distance for:', {
	// 		orderId,
	// 		clientAddress,
	// 		supplierAddress,
	// 		rowIndex
	// 	});

	// 	fetch('ordersController.php', {
	// 			method: 'POST',
	// 			headers: {
	// 				'Content-Type': 'application/x-www-form-urlencoded',
	// 			},
	// 			body: new URLSearchParams({
	// 				'action': 'calculate_distance',
	// 				'order_id': orderId,
	// 				'client_address': clientAddress,
	// 				'supplier_address': supplierAddress
	// 			})
	// 		})
	// 		.then(response => {
	// 			console.log('Response status:', response.status);
	// 			if (!response.ok) {
	// 				throw new Error(`HTTP error! status: ${response.status}`);
	// 			}
	// 			return response.json();
	// 		})
	// 		.then(data => {
	// 			console.log('Response data:', data);
	// 			// Utiliser l'ID unique avec rowIndex
	// 			const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
	// 			if (data.distance) {
	// 				distanceCell.textContent = `${data.distance} km`;
	// 			} else {
	// 				distanceCell.textContent = 'Distance non calculée';
	// 			}
	// 			distanceCell.classList.remove('calculating');
	// 		})
	// 		.catch(error => {
	// 			console.error('Erreur:', error);
	// 			const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
	// 			distanceCell.textContent = 'Erreur de calcul';
	// 			distanceCell.classList.remove('calculating');


	// 		});
	// }

	// function processDistanceCalculations() {
	// 	const orders = document.querySelectorAll('[data-needs-calculation="true"]');
	// 	let index = 0;

	// 	function calculateNext() {
	// 		if (index < orders.length) {
	// 			const order = orders[index];
	// 			calculateDistance(
	// 				order.dataset.orderId,
	// 				order.dataset.clientAddress,
	// 				order.dataset.supplierAddress,
	// 				order.dataset.rowIndex
	// 			);
	// 			index++;
	// 			setTimeout(calculateNext, 1000);
	// 		}
	// 	}
	// 	calculateNext();
	// }
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
