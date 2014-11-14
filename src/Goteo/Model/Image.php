<?php

namespace Goteo\Model {

    use Goteo\Library\Text,
        Goteo\Library\FileHandler\File,
        Intervention\Image\ImageManagerStatic as ImageManager,
        Goteo\Library\Cacher;

    class Image extends \Goteo\Core\Model {

        public
			$id,
            $name,
            $type,
            $tmp,
            $error,
            $size,
            $dir_originals = 'images/', //directorio archivos originales (relativo a GOTEO_DATA_PATH o al bucket s3)
            $dir_cache = 'cache/', //directorio archivos cache
            $fallback_image = 'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAC3ElEQVRYhb2XW1PiMBzF/f6POiMMBZO0KdBFZEBHnWGViKy7IIEtCmgvn+XsQ5tYLlIuZR8yfUib88v5X5qc+L6PLIfneQvPtHGStXgQBPr5XwA8z9tJMBMAtcvJZKKFkxDb2n+wA+2fj/j9Z7AAdXQHlND7dA7Oy7i9u4fnh3pu+d2jOSCenkEoA+dluJP3vcOwM4Ba+PLyCpyXQShDvd5YeSfDKgiX7A9wVW9ocUIsGMUS+v1XHZ6jJaHneRCiq8UpM2EYBLm8Ac7LmM0+di7HnQCGchyJEwuUchjFEnJ5A4RYsO0KHKeG6XS+E0QqgLJzOJJwnBooM0GIhYJxgVy+AEIscF6NoJiJVusGs9lH9g7c3t1H4pTFOy/oKlCOUMrBeRlXS0l5sANCdMFMC4QyFEsXMIolCNGFlGNQZibEqxqi0+lkA9DrvUTZHu98ueTUnG07X+GIE3Q6nx0O0Gxe62x3nNrCXBAEC2FQECpUj52ndIBNGeu6blTnBkEuX9AdL/mNbTsaglL+lQ/MXHFrowMq25OLu64LwyA4zxXgODXd85ONhjIzEo93rSA4r6Ja/bEdQFI0ubiUUtd5q3UDzw/jNpt8Z4zBYAApJYZyjKEcw7IqsO0KOC8flgNSyjjBosU+vSCG/dzwzRi2XdFh2Qsg+TNJtt2hHG8sV9/30ek8gTETlJloNq+3A0javpyUyQYUJVX4Laz7NlnIh9Ho7/YOfPcfV2GgzMTp2Tlu7+4xn3+uQAxHEvV6I2pMcWKmia8Nwbqy7IguTs/Oo07HTHBuo91+QLv9ACG6Cy5RykEoQ6/3sh/Ad/Fttx90bAmxdKmp9qtcIpRBCLHWzb0B1Oj1XnSMtbiCogzN5jX6g+FKYmYGEI0Q/f4rhOjiunWDer2B7vMvvL1NFwSPciZMu/XsczlJBVh33t+0u8yPZOt6RJrIUQ6lnuchDMO1c/tcyXYG2PfqlTb+AaY7ymbFQPTOAAAAAElFTkSuQmCC',
            $newstyle = false; // new style es no usar tabla image

        private $fp,
                $cache = null;

        public static $types = array('project', 'post', 'glossary', 'info');

        // decopilatorio con los tamaños habituales para imágenes de cada entidad
        // este es el tamaño que se usa en la página de gestión de la entidad (tabla)
        // sale de buscat getLink() en toda la aplicación
        public static $sizes = array(
            'user-avatar' => '56x56c', // cabecera en la página de proyecto y perfil (hay más tamaños)
            'banner' => '700x156c',
            'call-logo' => '250x124c',
            'call-image' => '',
            'call-backimage' => '', // sistintos tamaños segun dispositivo
            'call_banner' => '',
            'call_sponsor' => '',
            'project' => '580x580',
            'post' => '500x285',
            'glossary' => '500x285',
            'info' => '500x285',
            'story' => '940x385c',
            'sponsor' => '150x85'
        );

        /**
         * Constructor.
         *
         * @param type array	$file	Array $_FILES.
         */
        public function __construct ($file = null) {

            if(is_array($file)) {
                $this->name = $file['name'];
                $this->type = $file['type'];
                $this->tmp = $file['tmp_name'];
                $this->error = $file['error'];
                $this->size = $file['size'];
            }
            elseif(is_string($file)) {
				$this->name = basename($file);
				$this->tmp = $file;
			}

            $this->fp = File::factory(array('bucket' => AWS_S3_BUCKET_STATIC));
            $this->fp->setPath($this->dir_originals);
        }

        public function setCache(Cacher $cache = null) {
            if($cache instanceOf Cacher) {
                $this->cache = $cache;
                $this->cache->setCacheGroup($this->dir_originals);
            }
        }

        /**
         * Sobrecarga de métodos 'getter'.
         *
         * @param type string $name
         * @return type mixed
         */
        public function __get ($name) {
            if($name == "content") {
	            return $this->getContent();
	        }
            return $this->$name;
        }

        /**
         * (non-PHPdoc)
         * @see Goteo\Core.Model::save()
         *
         * FALTA!!!
         */
        public function save(&$errors = array()) {
            if($this->validate($errors)) {
                $this->original_name = $this->name;
                //nombre seguro
                $this->name = $this->fp->get_save_name($this->name);

                if(!empty($this->name)) {
                    $data[':name'] = $this->name;
                }

                if(!empty($this->type)) {
                    $data[':type'] = $this->type;
                }

                if(!empty($this->size)) {
                    $data[':size'] = $this->size;
                }

                try {

                    if(!empty($this->tmp)) {
                        $uploaded = $this->fp->upload($this->tmp, $this->name);

                        //@FIXME falta checkear que la imagen se ha subido correctamente
                        if (!$uploaded) {
                            $errors[] = 'fp->upload : <br />'.$this->tmp.' <br />dir: '.$this->dir_originals.' <br />file name: '.$this->name . '<br />from: '.$this->original_name . '<br />upload error: '.$this->fp->last_error;
                            return false;
                        }
                    }
                    else {
                        $errors[] = Text::get('image-upload-fail');
                        return false;
                    }

                    if ($this->newstyle) {

                        // no guardamos en tabla, id es el nombre del archivo
                        $this->id = $this->name;
                        //generamos hash
                        $this->hash = md5($this->id);

                    } else {

                        // @FIXME esto se podrá quitar cuando todas las entidades image estén modificadas


                        // Construye SQL.
                        $query = "REPLACE INTO image (";
                        foreach($data AS $key => $row) {
                            $query .= substr($key, 1) . ", ";
                        }
                        $query = substr($query, 0, -2) . ") VALUES (";
                        foreach($data AS $key => $row) {
                            $query .= $key . ", ";
                        }
                        $query = substr($query, 0, -2) . ")";
                        // Ejecuta SQL.
                        $result = self::query($query, $data);
                        if(empty($this->id)) $this->id = self::insertId();

                    }

                    return true;

            	} catch(\PDOException $e) {
                    $errors[] = "No se ha podido guardar la imagen: " . $e->getMessage();
                    return false;
    			}
            }
            return false;
		}

		/**
		* Returns a secure name to store in file system, if the generated filename exists returns a non-existing one
		* @param $name original name to be changed-sanitized
		* @param $dir if specified, generated name will be changed if exists in that dir
        * Esto ya lo hace la clase File con get_save_name
        */
        /*
		public static function check_filename($name='',$dir=null){
			$name = preg_replace("/[^a-z0-9_~\.-]+/","-",strtolower(self::idealiza($name, true)));
			if(is_dir($dir)) {
				while ( file_exists ( "$dir/$name" )) {
					$name = preg_replace ( "/^(.+?)(_?)(\d*)(\.[^.]+)?$/e", "'\$1_'.(\$3+1).'\$4'", $name );
				}
			}
			return $name;
		}
		*/

		/**
		 * (non-PHPdoc)
		 * @see Goteo\Core.Model::validate()
		 */
		public function validate(&$errors = array()) {

			if(empty($this->name)) {
                $errors['image'][] = Text::get('error-image-name');
            }

            // checkeo de errores de $_FILES
            if($this->error !== UPLOAD_ERR_OK) {
                switch($this->error) {
                    case UPLOAD_ERR_INI_SIZE:
                        $errors['image'][] = Text::get('error-image-size-too-large');
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $errors['image'][] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errors['image'][] = 'The uploaded file was only partially uploaded';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        if (isset($_POST['upload']))
                            $errors['image'][] = 'No file was uploaded';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errors['image'][] = 'Missing a temporary folder';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errors['image'][] = 'Failed to write file to disk';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errors['image'][] = 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions';
                        break;
                }
                return false;
            }

            if(!empty($this->type)) {
                $allowed_types = array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                    'image/svg+xml',
                );
                if(!in_array($this->type, $allowed_types)) {
                    $errors['image'][] = Text::get('error-image-type-not-allowed');
                }
            }
            else {
                $errors['image'][] = Text::get('error-image-type');
            }

            if(empty($this->tmp) || $this->tmp == "none") {
                $errors['image'][] = Text::get('error-image-tmp');
            }

            if(empty($this->size)) {
                $errors['image'][] = Text::get('error-image-size');
            }

            return empty($errors);
		}

        /**
         * Imagen.
         *
         * @param type int    $id
         * @return type object    Image
         */
        static public function get($id, $debug = false)
        {

            if ($debug) echo "Request image $id<br />";
            try {

                if (empty($id))
                    $id = 1;

                // imagenes especiales
                switch ($id) {
                    case '1':
                        $id = 'la_gota.png'; // imagen por defecto en toda la aplicación
                        break;
                    case '2':
                        $id = 'la_gota-wof.png'; // imagen por defecto en el wall of friends
                        break;
                }

                /*
                 *  No existe mas la tabla imágenes
                 *
                    $query = static::query("
                    SELECT
                        id,
                        name,
                        type,
                        size
                    FROM image
                    WHERE id = :id
                    ", array(':id' => $id));
                    $image = $query->fetchObject(__CLASS__);

                    if ($debug) echo "Numeric, from table: ".\trace($image);
                */

                $image = new Image;
                $image->name = $id;
                $image->id = $id;
                $image->hash = md5($id);

                if ($debug) echo "Not numeric, from name: <br />";
                if ($debug) echo \trace($image);
                if ($debug) echo $image->getLink(150, 85);
                if ($debug) die;

                return $image;
            } catch (\PDOException $e) {
                return false;
            }
        }

        /**
         * Galeria de imágenes de un usuario / proyecto
         *
         * @param  varchar(50)  $id    user id |project id
         * @param  string       $which    'user'|'project'
         * @return mixed        false|array de instancias de Image
         */
        public static function getAll ($id, $which) {

            if (!\is_string($which) || !\in_array($which, self::$types)) {
                return false;
            }

            $gallery = array();

            try {
                $sql = "SELECT image FROM {$which}_image WHERE {$which} = ?";
                $sql .= ($which == 'project') ? " ORDER BY section ASC, `order` ASC, image DESC" : " ORDER BY image ASC";
                $query = self::query($sql, array($id));
                foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $image) {
                    $gallery[] = self::get($image['image']);
                }

                return $gallery;
            } catch(\PDOException $e) {
                return false;
            }

        }

        /**
         * Lista de imágenes de galeria
         *
         *  Para proyecto hay que usar Project\Image::getList  por el tema de secciones y
         *
         *
         * @param  varchar(50)  $id  entity item id  user | project | post | info | glossary
         * @param  string       $which    entity
         * @return string       list of filenames
         */
        public static function getList ($id, $which) {

            if (!\is_string($which) || !\in_array($which, self::$types)) {
                // aquí debería grabar en un log de errores o mandar un mail a GOTEO_FAIL_MAIL
                return false;
            }

            $gallery = array();

            try {
                $sql = "SELECT image FROM {$which}_image WHERE {$which} = ?";
                if ($which == 'project') $sql .= " ORDER BY section ASC, `order` ASC";
                $query = self::query($sql, array($id));
                foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $image) {
                    $gallery[] = $image['image'];
                }

                return implode(', ', $gallery);
            } catch(\PDOException $e) {
                // aquí debería grabar en un log de errores o mandar un mail a GOTEO_FAIL_MAIL
                return false;
            }

        }

        /**
         * Quita una imagen de la tabla de relaciones y de la tabla de imagenes
         *
         * @param  string       $which    'project', 'post', 'glossary', 'info'
         * @return bool        true|false
         *
         */
        public function remove(&$errors = array(), $which = null) {

            /*
            NOTA: El borrado de archivos no debe hacerse aqui pues en casos de sistemas
                  distribuidos puede haber problemas porque las instancias web pueden no tener
                  el cache generado.
                  Otro problema (sobretodo si se usan CDN) es la cache de proxy sobre los archivos generados

            @FIXME: crear un script en cron que repase todas las tablas con imagenes y borre
                    del disco y el cache:

                    //borrado disco:
                    $this->fp->delete($this->id);

                    //borrado cache (hack horrible por mejorar):
                    $c = new Cache($this->dir_cache);
                    $c->rm('*\/' . $this->name);

             */
            // no borramos nunca la imagen de la gota
            if ($this->id == 'la_gota.png') return false;

            try {
                if (\is_string($which) && \in_array($which, self::$types)) {
                    $sql = "DELETE FROM {$which}_image WHERE image = ?";
                    $query = self::query($sql, array($this->id));
                }

                return true;
            } catch(\PDOException $e) {
                $errors[] = $e->getMessage();
                // aquí debería grabar en un log de errores o mandar un mail a GOTEO_FAIL_MAIL
                return false;
            }
        }


		/**
		 * Para montar la url de una imagen (porque las url con parametros no se cachean bien)
		 *  - Si el thumb está creado, montamos la url de /data/cache
         *  - Sino, monamos la url de /image/
         *
		 * @param type int $id
		 * @param type int $width
		 * @param type int $height
		 * @param type int $crop
		 * @return type string
		 */
		public function getLink ($width = 0, $height = 0, $crop = false) {

            if($crop === true) $crop = 'c';
            //metodos: c (crop)
            $crop = in_array($crop, array('c')) ? $crop : '';
            $path = (int)$width . 'x' . (int)$height . $crop . '/' .$this->name;

            //Si existe la constante GOTEO_DATA_URL la usaremos en vez de SITE_URL
            if(defined('GOTEO_DATA_URL')) return GOTEO_DATA_URL . '/' . $path;
            else                          return SITE_URL . '/img/' . $path;
        }

        /**
         * Muestra la imagen en pantalla.
         * @param type int  $width
         * @param type int  $height
         */
        public function display ($width, $height, $crop = false) {
            $width = (int) $width;
            $height = (int) $height;
            if($this->cache) {
                if($cache_file = $this->cache->getFile($this->name, $width . 'x' . $height . ($crop ? 'c' : ''))) {
                    //tries to flush the file and exit
                    if(Cacher::flushFile($cache_file))
                        return;
                }
            }
            $file = $this->dir_originals . $this->name;
            //By URL
            if(defined('GOTEO_DATA_URL')) $file =  GOTEO_DATA_URL . '/' . $file;
            //Get the file by filesystem
            else                          $file = GOTEO_DATA_PATH . $file;
            if(substr($file, 0, 2) === '//') {
                $file = (HTTPS_ON ? 'https:' : 'http:' ) . $file;
            }

            // die($file);
            // //Get the url file if is S3
            // if (defined('FILE_HANDLER') && FILE_HANDLER == 's3' && defined('AWS_SECRET') && defined('AWS_KEY')) {
            //     $file = SRC_URL . $file;
            //     if(substr($file, 0, 2) === '//') {
            //         $file = (HTTPS_ON ? 'https:' : 'http:' ) . $file;
            //     }
            // }
            // else {
            //     //Get the file by filesystem
            //     $file = GOTEO_DATA_PATH . $file;
            // }

            if($width <= 0) $width = null;
            if($height <= 0) $height = null;
            try {
                $img =  ImageManager::make($file);
                if($crop) {
                    $img->fit($width, $height, function ($constraint) {
                        $constraint->upsize();
                    });
                } else {
                    $img->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                //store in cache if enabled
                if($this->cache && $cache_file) {
                    $img->save($cache_file);
                }
                //flush data
                echo $img->response();

            }catch(\Exception $e) {
                //Shows a fallback image with the error message
                try {
                    $msg = $e->getMessage();
                    $w = $width ? $width : 32;
                    $h = $height ? $height : 32;

                    //flush data
                    echo $img =  ImageManager::canvas($w, $h, '#DCDCDC')
                                 ->insert($this->fallback_image, 'center')
                    // echo $img =  ImageManager::make($this->fallback_image)
                                 ->text($msg, round($w/2), round($h/2), function($font){
                                    $font->align('center');
                                    $font->valign('middle');
                                    $font->color('#666666');
                                 })
                                 ->response('png');
                }

                catch(\Exception $e) {
                    //if the fallback image fails, what can i do?
                    die($e->getMessage());
                }
            }
		}

        /**
         * Passthru a file with content-type, name
         * @param  [type] $file [description]
         * @return [type]       [description]
         */
        static function stream($file, $exit = true) {
            //redirection if is http stream
            if(substr($file,0,2) == '//') $file = (HTTPS_ON ? 'https:' : 'http:') . $file;
            if(substr($file, 0 , 7) == 'http://' || substr($file, 0 , 8) == 'https://') {
                header("Location: $file");
            }
            else {
                list($width, $height, $type, $attr) = @getimagesize( $file );
                if(!$type && function_exists( 'exif_imagetype' ) ) {
                    $type = exif_imagetype($file);
                }
                if($type) {
                     $type = image_type_to_mime_type($type);
                }
                else {
                    $type = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if($type == 'jpg') $type = "jpeg";
                    if(!in_array($type, array('jpeg', 'png', 'gif'))) die("file $type not image!");
                    $type = "image/$type";
                }

                header("Content-type: " . $type);
                header('Content-Disposition: inline; filename="' . str_replace("'", "\'", basename($file)) . '"');
                header("Content-Length: " . @filesize($file));
                readfile($file);
            }
            if($exit) exit;
        }

        private function getContent () {
            return file_get_contents($this->name);
    	}

        /**
         * Reemplaza la extensión de la imagen.
         *
         * @param type string	$src
         * @param type string	$new
         * @return type string
         */
    	static private function replace_extension($src, $new) {
    	    $pathinfo = pathinfo($src);
    	    unset($pathinfo["basename"]);
    	    unset($pathinfo["extension"]);
    	    return implode(DIRECTORY_SEPARATOR, $pathinfo) . '.' . $new;
    	}

        /**
         * Este método crea un array de objetod Image a partir de una lista de archivos
         *
         * @param $list string list of files separatd by commas
         * @return array of Image objects
         */
        public static function getGallery($list, $debug = false) {

            $gallery = array();

            if ($debug) echo $list.'<br />';

            if (empty($list))
                return $gallery;

            $items = explode(',', $list);

            foreach ($items as $item) {
                if ($debug) echo '*'.trim($item).'*';
                $gallery[] = static::get( trim($item) );
            }

            if ($debug) echo \trace($gallery);
            if ($debug) die;

                return $gallery;

        }

        /*
         * Recalcular galeria
         */
        public function setGallery ($which, $id, $section = '') {

            if (!\is_string($which) || !\in_array($which, self::$types)) {
                // aquí debería grabar en un log de errores o mandar un mail a GOTEO_FAIL_MAIL
                return array();
            }

            // sacar galeria de glossary_image
            $gallery = Image::getList($id, $which, $section);

            if (empty($gallery)) {
                $the_gallery = $gallery = 'empty';
            } else {
                // poner en la instancia
                $the_gallery = Image::getGallery($gallery);
            }

            // guardar serializado en la base de datos
            $sql = "UPDATE $which SET gallery = :gallery WHERE id = :id";
            self::query($sql, array(':gallery'=>$gallery, ':id'=>$id));

            return $the_gallery;
        }

        /*
         * Recalcular imagen principal
         */
        public function setImage ($which, $id, $gallery) {

            if (!\is_string($which) || !\in_array($which, self::$types)) {
                // aquí debería grabar en un log de errores o mandar un mail a GOTEO_FAIL_MAIL
                return false;
            }

            // sacar objeto imagen de la galeria
            $image = (empty($gallery) || $gallery === 'empty') ? 'empty' : $gallery[0];

            // poner en la instancia
            $the_image = ($image === 'empty') ? 'empty' : $image->id;

            // guardar en la base de datos
            $sql = "UPDATE $which SET image = :image WHERE id = :id";
            self::query($sql, array(':image'=>$the_image, ':id'=>$id));

            return $image;

        }



	}

}
