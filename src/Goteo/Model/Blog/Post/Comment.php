<?php

namespace Goteo\Model\Blog\Post {

    use Goteo\Library\Text,
        Goteo\Library\Feed,
        Goteo\Model\User,
        Goteo\Model\Image;

    class Comment extends \Goteo\Core\Model {

        public
            $id,
            $post,
            $date,
            $text,
            $user,
            $timeago;

        /*
         *  Devuelve datos de una comentario
         */
        public static function get ($id) {
                $query = static::query("
                    SELECT
                        id,
                        post,
                        date,
                        text,
                        user
                    FROM    comment
                    WHERE id = :id
                    ", array(':id' => $id));

                $comment = $query->fetchObject(__CLASS__);

                // reconocimiento de enlaces y saltos de linea
                $comment->text = nl2br(Text::urlink($comment->text));

                return $comment;
        }

        /*
         * Lista de comentarios
         */
        public static function getAll ($post) {

            $list = array();

            $sql = "
                SELECT
                    comment.id,
                    comment.post,
                    DATE_FORMAT(comment.date, '%d | %m | %Y') as date,
                    comment.date as timer,
                    comment.text,
                    comment.user,
                    user.id as user_id,
                    user.name as user_name,
                    user.email as user_email,
                    user.avatar as user_avatar
                FROM    comment
                INNER JOIN user
                    ON  user.id = comment.user
                    AND (user.hide = 0 OR user.hide IS NULL)
                WHERE comment.post = ?
                ORDER BY comment.date ASC, comment.id ASC
                ";
            
            $query = static::query($sql, array($post));
                
            foreach ($query->fetchAll(\PDO::FETCH_CLASS, __CLASS__) as $comment) {
                
                 // owner
                    $user = new User;
                    $user->id = $comment->user_id;
                    $user->name = $comment->user_name;
                    $user->email = $comment->user_email;
                    $user->avatar = Image::get($comment->user_avatar);

                    $comment->user = $user;

                // reconocimiento de enlaces y saltos de linea
                $comment->text = nl2br(Text::urlink($comment->text));

                //hace tanto
                $comment->timeago = Feed::time_ago($comment->timer);


                $list[$comment->id] = $comment;
            }

            return $list;
        }

        /*
         * Lista de comentarios en el blog
         */
        public static function getList($blog, $limit = null) {

            $list = array();

            $sql = "
                SELECT
                    comment.id,
                    comment.post,
                    DATE_FORMAT(comment.date, '%d | %m | %Y') as date,
                    comment.text,
                    comment.user,
                    user.name as user_name,
                    user.avatar as user_avatar
                FROM    comment
                INNER JOIN post ON post.id = comment.post AND post.blog = ?
                INNER JOIN user
                    ON  user.id = comment.user
                    AND (user.hide = 0 OR user.hide IS NULL)
                ORDER BY comment.date DESC, comment.id DESC
                ";
            if (!empty($limit)) {
                $sql .= "LIMIT $limit";
            }

            $query = static::query($sql, array($blog));

            foreach ($query->fetchAll(\PDO::FETCH_CLASS, __CLASS__) as $comment) {

                $user = new User;
                $user->id = $comment->user;
                $user->name = $comment->user_name;
                $user->avatar = Image::get($comment->user_avatar);

                // reconocimiento de enlaces y saltos de linea
                $comment->text = nl2br(Text::urlink($comment->text));

                $list[$comment->id] = $comment;
            }

            return $list;
        }

        /*
         *  Devuelve cuantos comentarios tiene una entrada
         */
        public static function getCount ($post) {

                $sql="
                    SELECT
                        COUNT(comment.id) as comments,
                        post.num_comments as num
                    FROM    comment
                    INNER JOIN post
                        ON  post.id = comment.post
                    INNER JOIN user
                        ON  user.id = comment.user
                        AND (user.hide = 0 OR user.hide IS NULL)
                    WHERE comment.post = :post";

                $values=array(':post' => $post);

                $query = static::query($sql, $values);
                if($got = $query->fetchObject()) {
                    // si ha cambiado, actualiza el numero de comentarios en un post
                    if ($got->comments != $got->num) {
                        static::query("UPDATE post SET num_comments = :num WHERE id = :post", array(':num' => (int) $got->comments, ':post' => $post));
                    }
                }

                return (int) $got->comments;

        }

        public function validate (&$errors = array()) { 
            if (empty($this->text))
                $errors[] = 'Falta texto';
                //Text::get('mandatory-comment-text');

            if (empty($errors))
                return true;
            else
                return false;
        }

        public function save (&$errors = array()) {
            if (!$this->validate($errors)) return false;

            $fields = array(
                'id',
                'post',
                'date',
                'text',
                'user'
                );

            $set = '';
            $values = array();

            foreach ($fields as $field) {
                if ($set != '') $set .= ", ";
                $set .= "`$field` = :$field ";
                $values[":$field"] = $this->$field;
            }

            //eliminamos etiquetas script,iframe..
            $values[':text']=Text::tags_filter($values[':text']);

            try {
                $sql = "REPLACE INTO comment SET " . $set;
                self::query($sql, $values);
                if (empty($this->id)) $this->id = self::insertId();

                // actualizar campo calculado
                self::getCount($this->post);

                return true;
            } catch(\PDOException $e) {
                $errors[] = "HA FALLADO!!! " . $e->getMessage();
                return false;
            }
        }

        /*
         * Para quitar un comentario
         */
        public static function delete ($id) {
            
            $sql = "DELETE FROM comment WHERE id = :id";
            if (self::query($sql, array(':id'=>$id))) {
                return true;

                // actualizar campo calculado
                self::getCount($this->post);

            } else {
                return false;
            }

        }

    }
    
}