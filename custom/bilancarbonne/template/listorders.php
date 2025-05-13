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
		color: lightgreen !important;
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

	.module-description {
		background: #f9f9f9;
		padding: 15px;
		border-radius: 8px;
		border-left: 5px solid #0078D7;
		font-size: 14px;
		color: #333;
		line-height: 1.6;
		max-width: 100%;
		margin-bottom: 1rem;
	}

	.module-description p {
		margin-bottom: 10px;
	}

	.module-description code {
		background: #eef2ff;
		padding: 3px 6px;
		border-radius: 4px;
		font-weight: bold;
	}

	.warning-box {
		background: #fff3cd;
		padding: 10px;
		border-radius: 6px;
		border-left: 5px solid #ff9800;
		margin-top: 15px;
	}

	.warning-box h3 {
		margin-top: 0;
		color: #d9534f;
	}

	.btn-edit {
		background: #0078D7;
		color: white;
		border: none;
		padding: 6px 12px;
		font-size: 14px;
		border-radius: 4px;
		cursor: pointer;
		transition: all 0.3s ease-in-out;
	}

	.btn-save {
		background: #28a745;
		color: white;
		border: none;
		padding: 6px 12px;
		font-size: 14px;
		border-radius: 4px;
		cursor: pointer;
		transition: all 0.3s ease-in-out;
	}
</style>

<!-- Facteur d'émission -->
<!-- <div class="emission-input-container">
	<label for="global_emission_factor">Facteur d'émission (g/tkm) :</label>
	<input type="number" id="global_emission_factor" value="80.8" step="0.1" min="0">
</div> -->

<div class="module-description">
	<p>
		Ce module permet de <strong>calculer automatiquement le bilan carbone</strong> de vos commandes en fonction des distances de transport et du facteur d’émission sélectionné.
		Vous pouvez ajuster ce facteur à tout moment afin d'obtenir des calculs précis basés sur les données les plus récentes.
	</p>

	<p>
		<strong>Comment calculer le facteur d’émission ?</strong><br>
		Il suffit d’appliquer la formule suivante : <br>
		<code>Total CO₂ (tonnes) / Total Ton-Km (tkm)</code>
	</p>

	<p>
		<em>Les données utilisées proviennent directement des transporteurs.</em>
	</p>

	<div class="warning-box">
		<h3>⚠️ ATTENTION :</h3>
		<p>
			Si vous modifiez le facteur d’émission, <strong>toutes les commandes seront recalculées une par une.</strong>
			Cette opération peut prendre un certain temps en fonction du volume des commandes.
		</p>
	</div>
</div>

<div id="progress-container" style="display: none; margin-top: 20px;">
	<p>Progression : <span id="progress-text">0%</span></p>
	<progress id="progress-bar" value="0" max="100" style="width: 100%;"></progress>
</div>

<button id="start-calculation" class="btn-edit">Lancer le calcul en arrière-plan</button>

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

<!-- ✅ Conteneur des filtres -->
<div class="filters-container">
	<!-- ✅ Facteur d'émission -->
	<div class="filter-item">
		<label for="global_emission_factor_text"><strong>Facteur d'émission (g/tkm) :</strong></label>
		<span id="global_emission_factor_text"><?php echo $emissionFactor; ?></span>
		<button id="edit_emission_factor" class="btn-edit">Modifier</button>

		<!-- Input caché pour modification -->
		<input type="number" id="global_emission_factor_input" value="<?php echo $emissionFactor; ?>" step="0.1" min="0" style="display:none;">
		<button id="save_emission_factor" class="btn-save" style="display:none;">Enregistrer</button>
	</div>

	<!-- ✅ Sélecteur d'année -->
	<div class="filter-item">
		<label for="year_select"><strong>Année :</strong></label>
		<select name="year" id="year_select" onchange="this.form.submit()">
			<?php
			$currentYear = date('Y');
			for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
				$selected = ($i == $year) ? 'selected' : '';
				echo "<option value='$i' $selected>$i</option>";
			}
			?>
		</select>
	</div>

	<!-- ✅ Sélection du nombre de résultats par page -->
	<div class="filter-item">
		<label for="results_per_page"><strong>Résultats par page :</strong></label>
		<select id="results_per_page">
			<?php foreach ($resultsPerPageOptions as $option): ?>
				<option value="<?= $option ?>" <?= ($limit == $option) ? 'selected' : '' ?>><?= $option ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<!-- ✅ Styles améliorés -->
<style>
	.filters-container {
		display: flex;
		justify-content: space-between;
		align-items: center;
		background: #f9f9f9;
		padding: 15px;
		border-radius: 8px;
		margin-bottom: 20px;
		box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.05);
	}

	.filter-item {
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 14px;
	}

	select,
	input[type="number"] {
		padding: 6px;
		border: 1px solid #ccc;
		border-radius: 5px;
		font-size: 14px;
	}

	.btn-edit,
	.btn-save {
		padding: 6px 12px;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		font-size: 14px;
		font-weight: bold;
		transition: all 0.3s ease-in-out;
	}

	.btn-edit {
		background: #0078D7;
		color: white;
	}

	.btn-save {
		background: #28a745;
		color: white;
	}

	.btn-edit:hover {
		background: #005cbf;
	}

	.btn-save:hover {
		background: #218838;
	}
</style>




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

	function processDistanceCalculations(newFactor) {
		console.log('📤 Envoi du recalcul avec facteur :', newFactor);
		const orders = document.querySelectorAll('[data-needs-calculation="true"]');
		const emissionFactor = parseFloat(document.getElementById('global_emission_factor_input').value) || 80.8;


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

	document.getElementById('start-calculation').addEventListener('click', function() {
		const progressContainer = document.getElementById('progress-container');
		const progressBar = document.getElementById('progress-bar');
		const progressText = document.getElementById('progress-text');

		// Afficher la barre de progression
		progressContainer.style.display = 'block';
		progressBar.value = 0;
		progressText.textContent = '0%';

		// Lancer le script de calcul en arrière-plan
		fetch('htdocs/custom/bilancarbone/controller/background/calculate_all_orders.php?year=2024')
			.then(response => response.json())
			.then(data => {
				if (!data.success) {
					alert('Erreur : ' + data.message);
					return;
				}

				// Vérifier la progression toutes les 2 secondes
				const interval = setInterval(() => {
					fetch('controller/background/progress.json')
						.then(response => response.json())
						.then(progress => {
							let percentage = Math.round((progress.processed / progress.total) * 100);
							progressBar.value = percentage;
							progressText.textContent = percentage + '%';

							// Si terminé, on arrête l'intervalle
							if (percentage >= 100) {
								clearInterval(interval);
								alert('Calcul terminé !');
							}
						})
						.catch(error => console.error('Erreur récupération progression:', error));
				}, 2000);
			})
			.catch(error => console.error('Erreur lors du lancement du calcul:', error));
	});

	// function calculateDistance(orderId, clientAddress, supplierAddress, transitAddress, emissionFactor) {
	// 	return new Promise((resolve, reject) => {
	// 		console.log('Envoi des paramètres pour le calcul :', {
	// 			orderId,
	// 			clientAddress,
	// 			supplierAddress,
	// 			transitAddress,
	// 			emissionFactor
	// 		});

	// 		fetch('ordersController.php', {
	// 				method: 'POST',
	// 				headers: {
	// 					'Content-Type': 'application/x-www-form-urlencoded'
	// 				},
	// 				body: new URLSearchParams({
	// 					action: 'calculate_distance',
	// 					order_id: orderId,
	// 					client_address: clientAddress,
	// 					supplier_address: supplierAddress,
	// 					transit_address: transitAddress,
	// 					emission_factor: emissionFactor
	// 				})
	// 			})
	// 			.then(response => {
	// 				if (!response.ok) {
	// 					throw new Error(`Erreur HTTP ${response.status} : ${response.statusText}`);
	// 				}
	// 				return response.json();
	// 			})
	// 			.then(data => {
	// 				// console.log(`Réponse du serveur pour la commande ${orderId} :`, data);
	// 				resolve(data);
	// 			})
	// 			.catch(error => {
	// 				console.error(`Erreur lors de la requête pour la commande ${orderId}:`, error);
	// 				reject(error);
	// 			});
	// 	});
	// }
	function calculateDistance(orderId, clientAddress, supplierAddress, transitAddress, emissionFactor) {
		console.log('Calcul avec nouveau facteur:', emissionFactor);


		return fetch('ordersController.php', {
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
					throw new Error(`Erreur HTTP: ${response.status}`);
				}
				return response.json();
			})
			.catch(error => {
				console.error('Erreur lors du calcul:', error);
				throw error;
			});
	}
	// // ✅ Vérification : Recalcul des valeurs
	// function recalculateAll(emissionFactor) {
	// 	console.log("🔄 Lancement de `recalculateAll()` avec le facteur :", emissionFactor);

	// 	const orders = document.querySelectorAll('[data-order-id]');
	// 	if (orders.length === 0) {
	// 		console.log("❌ Aucun élément trouvé pour recalculer !");
	// 		return;
	// 	}

	// 	orders.forEach(order => {
	// 		console.log("🔍 Recalcul de la commande :", order.dataset.orderId);

	// 		const orderId = order.dataset.orderId;
	// 		const rowIndex = order.dataset.rowIndex;

	// 		// Récupérer les cellules de distance et CO2
	// 		const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
	// 		const co2Cell = document.getElementById(`co2_${orderId}_${rowIndex}`);

	// 		if (!distanceCell || !co2Cell) {
	// 			console.error(`⚠️ Impossible de trouver les cellules distance et CO₂ pour la commande ${orderId}`);
	// 			return;
	// 		}

	// 		// Recalcul
	// 		calculateDistance(
	// 				orderId,
	// 				order.dataset.clientAddress,
	// 				order.dataset.supplierAddress,
	// 				order.dataset.transitAddress,
	// 				emissionFactor
	// 			)
	// 			.then(data => {
	// 				if (data.success) {
	// 					console.log(`✅ Mise à jour pour la commande ${orderId} : Distance = ${data.distance}, CO2 = ${data.co2}`);
	// 					distanceCell.textContent = `${parseFloat(data.distance).toFixed(2)} km`;
	// 					co2Cell.textContent = `${parseFloat(data.co2).toFixed(2)} kg CO₂`;
	// 				} else {
	// 					console.error(`❌ Erreur sur la commande ${orderId}:`, data);
	// 				}
	// 			})
	// 			.catch(error => {
	// 				console.error(`❌ Erreur lors du recalcul de la commande ${orderId}:`, error);
	// 			});
	// 	});
	// }
	// document.addEventListener('DOMContentLoaded', function() {
	// 	const factorText = document.getElementById('global_emission_factor_text');
	// 	const factorInput = document.getElementById('global_emission_factor_input');
	// 	const editButton = document.getElementById('edit_emission_factor');
	// 	const saveButton = document.getElementById('save_emission_factor');

	// 	// Événement pour le bouton Modifier
	// 	editButton.addEventListener('click', function() {
	// 		factorText.style.display = 'none';
	// 		factorInput.style.display = 'inline-block';
	// 		saveButton.style.display = 'inline-block';
	// 		editButton.style.display = 'none';
	// 		factorInput.focus();
	// 	});

	// 	// Événement pour le bouton Sauvegarder
	// 	saveButton.addEventListener('click', function() {
	// 		const newFactor = parseFloat(factorInput.value);

	// 		if (!isNaN(newFactor) && newFactor > 0) {
	// 			// Récupérer toutes les cellules qui contiennent des données
	// 			const cells = document.querySelectorAll('[data-order-id]');

	// 			// Stocker la nouvelle valeur et masquer l'input
	// 			factorText.textContent = newFactor.toFixed(1);
	// 			factorInput.style.display = 'none';
	// 			saveButton.style.display = 'none';
	// 			factorText.style.display = 'inline-block';
	// 			editButton.style.display = 'inline-block';

	// 			// Mettre à jour le facteur d'émission côté serveur
	// 			fetch('ordersController.php', {
	// 					method: 'POST',
	// 					headers: {
	// 						'Content-Type': 'application/x-www-form-urlencoded',
	// 					},
	// 					body: new URLSearchParams({
	// 						'action': 'update_emission_factor',
	// 						'emission_factor': newFactor
	// 					})
	// 				}).then(response => response.json())
	// 				.then(() => {
	// 					// Pour chaque cellule, recalculer avec la nouvelle valeur
	// 					cells.forEach(cell => {
	// 						const orderId = cell.dataset.orderId;
	// 						const rowIndex = cell.dataset.rowIndex;
	// 						const distanceCell = document.getElementById(`distance_${orderId}_${rowIndex}`);
	// 						const co2Cell = document.getElementById(`co2_${orderId}_${rowIndex}`);

	// 						// Réinitialiser les cellules en mode "calcul en cours"
	// 						distanceCell.textContent = 'Recalcul...';
	// 						co2Cell.textContent = 'Recalcul...';
	// 						distanceCell.className = 'calculating';
	// 						co2Cell.className = 'calculating';

	// 						// Lancer le nouveau calcul
	// 						calculateDistance(
	// 								orderId,
	// 								cell.dataset.clientAddress,
	// 								cell.dataset.supplierAddress,
	// 								cell.dataset.transitAddress,
	// 								newFactor
	// 							)
	// 							.then(data => {
	// 								if (data.success) {
	// 									const distanceValue = parseFloat(data.distance);
	// 									const co2Value = parseFloat(data.co2);

	// 									distanceCell.textContent = `${distanceValue.toFixed(2)} km`;
	// 									co2Cell.textContent = `${co2Value.toFixed(2)} kg CO₂`;

	// 									distanceCell.className = 'success distance';
	// 									co2Cell.className = 'success co2';
	// 								} else {
	// 									distanceCell.textContent = 'Erreur de recalcul';
	// 									co2Cell.textContent = 'Erreur de recalcul';
	// 									distanceCell.className = 'error';
	// 									co2Cell.className = 'error';
	// 								}
	// 							})
	// 							.catch(error => {
	// 								console.error('Erreur lors du recalcul:', error);
	// 								distanceCell.textContent = 'Erreur de recalcul';
	// 								co2Cell.textContent = 'Erreur de recalcul';
	// 								distanceCell.className = 'error';
	// 								co2Cell.className = 'error';
	// 							});
	// 					});
	// 				});
	// 		} else {
	// 			alert("Veuillez entrer un facteur d'émission valide (nombre positif).");
	// 		}
	// 	});
	// });

	document.addEventListener('DOMContentLoaded', function() {
		const factorText = document.getElementById('global_emission_factor_text');
		const factorInput = document.getElementById('global_emission_factor_input');
		const editButton = document.getElementById('edit_emission_factor');
		const saveButton = document.getElementById('save_emission_factor');

		// Événement pour le bouton Modifier
		editButton.addEventListener('click', function() {
			factorText.style.display = 'none';
			factorInput.style.display = 'inline-block';
			saveButton.style.display = 'inline-block';
			editButton.style.display = 'none';
			factorInput.focus();
		});

		// Événement pour le bouton Enregistrer
		saveButton.addEventListener('click', function() {
			const newFactor = parseFloat(factorInput.value);

			if (!isNaN(newFactor) && newFactor > 0) {
				factorText.textContent = newFactor.toFixed(1);
				factorInput.style.display = 'none';
				saveButton.style.display = 'none';
				factorText.style.display = 'inline-block';
				editButton.style.display = 'inline-block';

				console.log("✅ Nouveau facteur enregistré :", newFactor);

				// Mettre à jour le facteur côté serveur
				// fetch('ordersController.php', {
				// 		method: 'POST',
				// 		headers: {
				// 			'Content-Type': 'application/x-www-form-urlencoded'
				// 		},
				// 		body: new URLSearchParams({
				// 			'action': 'update_emission_factor',
				// 			'emission_factor': newFactor
				// 		})
				// 	})
				// 	.then(response => response.json())
				// 	.then(() => {
				// 		console.log("✅ Facteur d'émission mis à jour sur le serveur");

				// 		// 🔄 Relancer le calcul immédiatement après la mise à jour
				// 		processDistanceCalculations();
				// 	})
				// 	.catch(error => console.error("❌ Erreur lors de la mise à jour du facteur :", error));
				fetch('ordersController.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						},
						body: new URLSearchParams({
							'action': 'update_emission_factor',
							'emission_factor': newFactor
						})
					})
					.then(response => response.json())
					.then(data => {
						console.log("✅  Facteur reçu du serveur  :", data.new_factor);

						// 🔄 Relancer immédiatement le recalcul avec la nouvelle valeur
						processDistanceCalculations(data.new_factor);
					})
					.catch(error => console.error("❌ Erreur lors de la mise à jour du facteur :", error));
			} else {
				alert("Veuillez entrer un facteur d'émission valide.");
			}
		});
	});
</script>

<?php
// Afficher le footer
llxFooter();
