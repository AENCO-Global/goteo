<?php

namespace Goteo\Library {

    use Goteo\Core\Model;

    class Worth {
		
        /*
         * Devuelve el nombre de un nivel por id
         */
		public static function get ($id) {

            //Obtenemos el idioma de soporte
            $lang=Model::default_lang_by_id($id, 'worthcracy_lang', \LANG);

            $values = array(':id'=>$id, ':lang' => $lang);
            $sql = "SELECT
                        worthcracy.id as id,
                        IFNULL(worthcracy_lang.name, worthcracy.name) as name
                    FROM worthcracy
                    LEFT JOIN worthcracy_lang
                        ON  worthcracy_lang.id = worthcracy.id
                        AND worthcracy_lang.lang = :lang
                    WHERE worthcracy.id = :id
                    ";

            $query = Model::query($sql, $values);
            $level = $query->fetchObject();
            if (!empty($level->name))
                return $level->name;

            return false;
		}

        /*
         * Devuelve datos apra gestionar
         */
		public static function getAdmin ($id) {

            $values = array(':id'=>$id);
            $sql = "SELECT
                        worthcracy.id as id,
                        worthcracy.name as name,
                        worthcracy.amount as amount
                    FROM worthcracy
                    WHERE worthcracy.id = :id
                    ";

            $query = Model::query($sql, $values);
            return $query->fetchObject();
		}

        /*
         * Devuelve los niveles de meritocracia
         */
		public static function getAll () {
            $array = array();
            $values = array(':lang' => \LANG);

            if(Model::default_lang(\LANG)=='es') {
                $different_select=" IFNULL(worthcracy_lang.name, worthcracy.name) as name";
                }
            else {
                    $different_select=" IFNULL(worthcracy_lang.name, IFNULL(eng.name, worthcracy.name)) as name";
                    $eng_join=" LEFT JOIN worthcracy_lang as eng
                                    ON  eng.id = worthcracy.id
                                    AND eng.lang = 'en'";
                }

            $sql = "SELECT
                        worthcracy.id as id,
                        $different_select,
                        worthcracy.amount as amount
                    FROM worthcracy
                    LEFT JOIN worthcracy_lang
                        ON  worthcracy_lang.id = worthcracy.id
                        AND worthcracy_lang.lang = :lang
                    $eng_join
                    ORDER BY worthcracy.amount ASC
                    ";

            $query = Model::query($sql, $values);
            foreach ( $query->fetchAll(\PDO::FETCH_CLASS) as $worth) {
                $array[$worth->id] = $worth;
            }
            return $array;
		}

		/*
		 *  Esto se usa para actualizar datos en cualquier tabla de contenido
		 */
		public static function save($data, &$errors = array()) {

            if (empty($data)) {
                $errors[] = "Sin datos";
                return false;
            }
            if (empty($data['name']) || empty($data['amount']) || empty($data['id'])) {
                $errors[] = "No se guardar sin nombre y cantidad";
                return false;
            }

  			try {
                $values = array(
                    ':id' => $data['id'],
                    ':name' => $data['name'],
                    ':amount' => $data['amount']
                );

				$sql = "REPLACE INTO worthcracy SET `id` = :id, `name` = :name, `amount` = :amount ";
				if (Model::query($sql, $values)) {
                    return true;
                } else {
                    $errors[] = "Ha fallado $sql con <pre>" . print_r($values, true) . "</pre>";
                    return false;
                }

			} catch(\PDOException $e) {
                $errors[] = 'Error sql al grabar el nivel de meritocracia. ' . $e->getMessage();
                return false;
			}

		}


        /*
         * Devuelve el importe para el siguiente nivel
         * @TODO tener en cuenta el nivel actual
         */
		public static function abitmore ($amount) {

            if (!is_numeric($amount))
                return $amount;

            $values = array(':amount'=>$amount, ':lang' => \LANG);

            if(Model::default_lang(\LANG)=='es') {
                $different_select=" IFNULL(worthcracy_lang.name, worthcracy.name) as name";
                }
            else {
                $different_select=" IFNULL(worthcracy_lang.name, IFNULL(eng.name, worthcracy.name)) as name";
                $eng_join=" LEFT JOIN worthcracy_lang as eng
                                   ON  eng.id = worthcracy.id
                                   AND eng.lang = 'en'";       
                }

            $sql = "SELECT
                        $different_select,
                        worthcracy.amount as amount
                    FROM worthcracy
                    LEFT JOIN worthcracy_lang
                        ON  worthcracy_lang.id = worthcracy.id
                        AND worthcracy_lang.lang = :lang
                    $eng_join
                    WHERE worthcracy.amount > :amount
                    ";

            $query = Model::query($sql, $values);
			$next = $query->fetchObject();
            $abit = $next->amount - $amount; //cuanto para el siguiente nivel
            
			return array('amount'=>$abit, 'name'=>$next->name);
		}

        /*
         * Devuelve el nombre de un nivel por importe acumulado
         */
		public static function reach ($amount) {
            if (!is_numeric($amount))
                return false;
            
            $values = array(':amount'=>$amount, ':lang' => \LANG);

             if(Model::default_lang(\LANG)=='es') {
                $different_select=" IFNULL(worthcracy_lang.name, worthcracy.name) as name";
                }
            else {
                $different_select=" IFNULL(worthcracy_lang.name, IFNULL(eng.name, worthcracy.name)) as name";
                $eng_join=" LEFT JOIN worthcracy_lang as eng
                                   ON  eng.id = worthcracy.id
                                   AND eng.lang = 'en'";       
                }

            $sql = "SELECT
                        worthcracy.id as id,
                        $different_select
                    FROM worthcracy
                    LEFT JOIN worthcracy_lang
                        ON  worthcracy_lang.id = worthcracy.id
                        AND worthcracy_lang.lang = :lang
                    $eng_join
                    WHERE worthcracy.amount <= :amount
                    ORDER BY worthcracy.amount DESC
                    LIMIT 1
                    ";

            $query = Model::query($sql, $values);
            return $query->fetchObject();
		}

	}
	
}
