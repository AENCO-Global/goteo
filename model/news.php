<?php

namespace Goteo\Model {

    use Goteo\Library\Check,
        Goteo\Library\Text,
        Goteo\Model\Image;;

    class News extends \Goteo\Core\Model {

        public
            $id,
            $title,
            $url,
            $image,
            $media_name,
            $order
            ;

        /*
         *  Devuelve datos de un destacado
         */
        public static function get ($id) {
                $sql = static::query("
                    SELECT
                        news.id as id,
                        IFNULL(news_lang.title, news.title) as title,
                        IFNULL(news_lang.description, news.description) as description,
                        news.url as url,
                        news.order as `order`,
                        news.press_banner as `press_banner`,
                        news.media_name as `media_name`,
                        news.image as `image`
                    FROM news
                    LEFT JOIN news_lang
                        ON  news_lang.id = news.id
                        AND news_lang.lang = :lang
                    WHERE news.id = :id
                    ", array(':id' => $id, ':lang'=>\LANG));
                $news = $sql->fetchObject(__CLASS__);

                return $news;
        }

        /*
         * Lista de noticias
         */
        public static function getAll ($highlights = false) {

            $list = array();

            $sql = static::query("
                SELECT
                    news.id as id,
                    IFNULL(news_lang.title, news.title) as title,
                    IFNULL(news_lang.description, news.description) as description,
                    news.url as url,
                    news.order as `order`,
                    news.press_banner as `press_banner`,
                    news.image as `image`
                FROM news
                LEFT JOIN news_lang
                    ON  news_lang.id = news.id
                    AND news_lang.lang = :lang
                ORDER BY `order` ASC, title ASC
                ", array(':lang'=>\LANG));
            
            foreach ($sql->fetchAll(\PDO::FETCH_CLASS, __CLASS__) as $item) {
                if ($highlights) {
                    $item->title = Text::recorta($item->title, 80);
                }
                if (!empty($item->image)) {
                    $item->image = Image::get($item->image);
                }
                $list[] = $item;
            }

            return $list;
        }

        public function validate (&$errors = array()) { 
            if (empty($this->title))
                $errors[] = 'Falta título';
                //Text::get('mandatory-news-title');

            if (empty($this->url))
                $errors[] = 'Falta url';
                //Text::get('mandatory-news-url');

            if (empty($errors))
                return true;
            else
                return false;
        }

        public function save (&$errors = array()) {
            if (!$this->validate($errors)) return false;

            // Primero la imagenImagen
            if (is_array($this->image) && !empty($this->image['name'])) {
                $image = new Image($this->image);
                if ($image->save($errors)) {
                    $this->image = $image->id;
                } else {
                    \Goteo\Library\Message::Error(Text::get('image-upload-fail') . implode(', ', $errors));
                    $this->image = '';
                }
            }

            $fields = array(
                'id',
                'title',
                'description',
                'url',
                'image',
                'media_name',
                'order',
                'press_banner'
                );

            $set = '';
            $values = array();

            foreach ($fields as $field) {
                if ($set != '') $set .= ", ";
                $set .= "`$field` = :$field ";
                $values[":$field"] = $this->$field;
            }

            try {
                $sql = "REPLACE INTO news SET " . $set;
                self::query($sql, $values);
                if (empty($this->id)) $this->id = self::insertId();

                Check::reorder($this->id, 'up', 'news');

                return true;
            } catch(\PDOException $e) {
                $errors[] = "HA FALLADO!!! " . $e->getMessage();
                return false;
            }
        }

        /*
         * Para quitar una pregunta
         */
        public static function delete ($id) {
            
            $sql = "DELETE FROM news WHERE id = :id";
            if (self::query($sql, array(':id'=>$id))) {
                return true;
            } else {
                return false;
            }

        }

        /*
         * Para poner una micronoticia en banner prensa
         */
        public static function add_press_banner ($id) {
            
            if(!self::in_press_banner($id))
            
            {
                $order=self::next();

                $sql = "UPDATE news SET `press_banner`=1 WHERE id = :id";
                if (self::query($sql, array(':id'=>$id))) {
                    return true;
                } else {
                    return false;
                }
            }

            else
                return true;
        }


        /*
         * Para quitar una micronoticia de banner prensa
         */

        public static function remove_press_banner ($id) {
            
            $sql = "UPDATE news SET `press_banner`=0 WHERE id = :id";
            if (self::query($sql, array(':id'=>$id))) {
                return true;
            } else {
                return false;
            }

        }

         // comprobar si una micronoticia está en el banner de prensa
        public static function in_press_banner ($id) {

         $query = self::query('SELECT `press_banner` FROM news WHERE id = :id'
                    , array(':id'=>$id));
        
        if($order=$query->fetchColumn(0)) 
            return $order;
        
        else return 0;    
        }

        /*
         * Para que una pregunta salga antes  (disminuir el order)
         */
        public static function up ($id) {
            return Check::reorder($id, 'up', 'news');
        }

        /*
         * Para que un proyecto salga despues  (aumentar el order)
         */
        public static function down ($id) {
            return Check::reorder($id, 'down', 'news');
        }

        /*
         * Orden para añadirlo al final
         */
        public static function next () {
            $sql = self::query('SELECT MAX(`order`) FROM news');
            $order = $sql->fetchColumn(0);
            return ++$order;

        }

    }
    
}