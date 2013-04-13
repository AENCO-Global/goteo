<?php

namespace Goteo\Model\Project {

    class Account extends \Goteo\Core\Model {

        public
            $project,
            $bank,
            $bank_owner,
            $paypal,
            $paypal_owner,
            $allowpp; // para permitir usar el boton paypal


        /**
         * Get the accounts for a project
         * @param varcahr(50) $id  Project identifier
         * @return array of accounts
         */
	 	public static function get ($id) {

            try {
                $query = static::query("SELECT * FROM project_account WHERE project = ?", array($id));
                $accounts = $query->fetchObject(__CLASS__);
                if (!empty($accounts)) {
                    return $accounts;
                } else {
                    $accounts = new Account();
                    $accounts->project = $id;
                    $accounts->allowpp = false;
                    return $accounts;
                }
            } catch(\PDOException $e) {
				throw new \Goteo\Core\Exception($e->getMessage());
            }
		}

		public function validate(&$errors = array()) {
            // Estos son errores que no permiten continuar
            if (empty($this->project)) {
                $errors[] = 'No hay ningun proyecto al que asignar cuentas';
                //Text::get('validate-account-noproject');
                return false;
            }

            return true;
        }

		public function save (&$errors = array()) {
            if (!$this->validate($errors)) return false;

			try {
	            $sql = "REPLACE INTO project_account (project, bank, bank_owner, paypal, paypal_owner, allowpp) VALUES(:project, :bank, :bank_owner, :paypal, :paypal_owner, :allowpp)";
                $values = array(':project'=>$this->project, ':bank'=>$this->bank, ':bank_owner'=>$this->bank_owner, ':paypal'=>$this->paypal, ':paypal_owner'=>$this->paypal_owner, ':allowpp'=>$this->allowpp);
				self::query($sql, $values);
				return true;
			} catch(\PDOException $e) {
				$errors[] = "Las cuentas no se han asignado correctamente. Por favor, revise los datos." . $e->getMessage();
                return false;
			}

		}

        // comprobar permiso para usar PayPal
        public static function getAllowpp ($id) {
            try {
                $query = static::query("SELECT allowpp FROM project_account WHERE project = ?", array($id));
                $allowpp = $query->fetchColumn(0);
                return $allowpp;
            } catch(\PDOException $e) {
				throw new \Goteo\Core\Exception($e->getMessage());
                return false;
            }
        }
        
        
	}
    
}