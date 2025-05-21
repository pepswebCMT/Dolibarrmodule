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
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';

class InterfaceCommandeFieldsUpdate extends DolibarrTriggers
{
	public function __construct($db)
	{
		$this->db = $db;
		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "Trigger to auto-update 'activite' extrafield in customer orders when thirdparty is modified.";
		$this->version = 'development';
		$this->picto = 'mymodule@mymodule';
	}

	public function runTrigger($action, $object, $user, $langs, $conf)
	{

		dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id, LOG_DEBUG);

		if ($action !== 'COMPANY_MODIFY') {
			return 0;
		}

		if (!is_object($object)) {
			dol_syslog("Objet tiers invalide, arrêt du trigger.", LOG_WARNING);
			return 0;
		}

		$extrafields = new ExtraFields($this->db);
		$object->fetch_optionals();

		$activite_key = 'options_activite';

		if (!isset($object->array_options[$activite_key])) {
			dol_syslog("Extrafield 'activite' non trouvé.", LOG_DEBUG);
			return 0;
		}

		$new_activite = $object->array_options[$activite_key];

		$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "commande 
                WHERE fk_soc = " . $object->id;

		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog("Erreur lors de la récupération des commandes du tiers: " . $this->db->lasterror(), LOG_ERR);
			return -1;
		}

		$updated_count = 0;

		while ($row = $this->db->fetch_object($resql)) {

			$commande = new Commande($this->db);
			if ($commande->fetch($row->rowid) > 0) {

				$commande->fetch_optionals();

				$commande->array_options['options_activite'] = $new_activite;

				$result = $commande->insertExtraFields();
				if ($result < 0) {
					dol_syslog("Erreur lors de la mise à jour de l'extrafield 'activite' pour la commande ID " . $commande->id . ": " . $commande->error, LOG_ERR);
				} else {
					$updated_count++;
				}
			}
		}

		dol_syslog("Nombre de commandes mises à jour avec l'activité : " . $updated_count, LOG_DEBUG);

		return 1;
	}
}
