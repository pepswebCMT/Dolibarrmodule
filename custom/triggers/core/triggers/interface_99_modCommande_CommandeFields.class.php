<?php
/* Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

//triggers

require_once DOL_DOCUMENT_ROOT . '/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

class InterfaceCommandeFields extends DolibarrTriggers
{
	public function __construct($db)
	{
		$this->db = $db;
		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "Trigger to auto-fill extrafields in customer orders from thirdparty.";
		$this->version = 'development';
		$this->picto = 'mymodule@mymodule';
	}

	public function runTrigger($action, $object, $user, $langs, $conf)
	{
		global $db;

		dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id, LOG_DEBUG);

		if ($action !== 'ORDER_CREATE' && $action !== 'ORDER_MODIFY') {
			return 0;
		}

		if (!is_object($object) || empty($object->socid)) {
			dol_syslog("Commande sans tiers valide, arrêt du trigger.", LOG_WARNING);
			return 0;
		}


		$client = new Societe($db);
		if ($client->fetch($object->socid) <= 0) {
			dol_syslog("Impossible de récupérer les données du tiers ID " . $object->socid, LOG_ERR);
			return -1;
		}

		$extrafields = new ExtraFields($db);
		$client->fetch_optionals();

		$extrafields_to_transfer = [
			'departement' => 'departement',
			'fidelite' => 'fidelite',
			'provenance' => 'provenance',
			'activite' => 'activite',
			'silo' => 'silo',
			'canal' => 'canal'
		];

		$object->fetch_optionals();

		$updated = false;
		foreach ($extrafields_to_transfer as $thirdparty_field => $order_field) {
			$thirdparty_option_key = 'options_' . $thirdparty_field;
			$order_option_key = 'options_' . $order_field;

			if (empty($object->array_options[$order_option_key]) && !empty($client->array_options[$thirdparty_option_key])) {
				dol_syslog("Copie de l'extrafield $thirdparty_field vers $order_field avec la valeur : " . $client->array_options[$thirdparty_option_key], LOG_DEBUG);

				$object->array_options[$order_option_key] = $client->array_options[$thirdparty_option_key];
				$updated = true;
			} else {
				dol_syslog("Aucune valeur trouvée ou extrafield déjà modifié pour $thirdparty_field", LOG_DEBUG);
			}
		}

		// if (!empty($user->id)) {
		// 	$object->array_options['signature'] = $user->id;
		// 	$object->array_options['commercial'] = $user->id;
		// 	$updated = true;
		// }

		if ($updated) {
			$result = $object->insertExtraFields();
			if ($result < 0) {
				dol_syslog("Erreur lors de la mise à jour des extrafields: " . $object->error, LOG_ERR);
				return -1;
			}
			dol_syslog("Extrafields mis à jour avec succès pour la commande ID " . $object->id, LOG_DEBUG);
		}

		return 1;
	}
}
