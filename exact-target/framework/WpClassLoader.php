<?php
/**
 * Class Loader
 *
 * @author  Joe Sexton <joe.sexton@bigideas.com>
 */
if (!class_exists( 'WpClassLoader' )){
	class WpClassLoader {

		/**
		 * @var string
		 */
		protected $rootPath;

		/**
		 * @var string
		 */
		protected $rootUrl;

		/**
		 * @var string
		 */
		protected $fwPath;

		/**
		 * @var string
		 */
		protected $controllerPath;

		/**
		 * @var string
		 */
		protected $modelPath;

		/**
		 * @var string
		 */
		protected $viewPath;

		/**
		 * @var string
		 */
		protected $assetPath;

		/**
		 * @var array
		 */
		protected $objects = array();

		/**
		 * constructor
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   string $directory
		 */
		public function __construct( $rootPath = null, $pluginUrl = null )
		{
			if( !$pluginUrl )
				$pluginUrl = plugin_dir_url( __FILE__ );
			$this->rootUrl = trailingslashit( $pluginUrl );

			if ( !$rootPath )
				$rootPath = dirname( __FILE__ );
			$this->rootPath = trailingslashit( $rootPath );

			$this->fwPath         = $this->rootPath.'framework/';
			$this->controllerPath = $this->rootPath.'controller/';
			$this->modelPath      = $this->rootPath.'model/';
			$this->viewPath       = $this->rootPath.'view/';
			$this->assetPath      = $pluginUrl.'assets/';

			spl_autoload_register( array( $this, '_autoload' ) );

			$this->initControllers();
		}

		/**
		 * autoload
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   $class
		 */
		protected function _autoload( $class ){

			$this->_loadClassFile( $class );
		}

		/**
		 * load class file
		 *
		 * @author Joe Sexton <joe.sexton@bigideas.com>
		 * @param  string $class
		 * @param  string $dir
		 * @return bool
		 */
		protected function _loadClassFile( $class, $dir = null ) {

			if ( is_null( $dir ) )
				$dir = $this->rootPath;

			foreach ( scandir( $dir ) as $file ) {

				// directory?
				if ( is_dir( $dir.$file ) && substr( $file, 0, 1 ) !== '.' )
					$this->_loadClassFile( $class, $dir.$file.'/' );

				// php file?
				if ( substr( $file, 0, 2 ) !== '._' && preg_match( "/.php$/i" , $file ) ) {

					// filename matches class?
					if ( str_replace( '.php', '', $file ) == $class || str_replace( '.class.php', '', $file ) == $class ) {

						include $dir . $file;
					}
				}
			}
		}

		/**
		 * init controllers
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 */
		public function initControllers() {

			if ( !file_exists( $this->controllerPath ) )
				return false;

			foreach ( scandir( $this->controllerPath ) as $file ) {
				if( substr( $file, 0, 2 ) !== '._' && preg_match( "/.php$/i" , $file ) ) {

					$class = str_replace( '.php', '', $file );
					$this->objects[$class] = new $class( $this->rootPath, $this->rootUrl );
				}
			}

			return true;
		}

		/**
		 * get objects
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @return  array
		 */
		public function getObjects() {

			return $this->objects;
		}

		/**
		 * get object
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   string $class
		 * @return  object | false
		 */
		public function getObject( $class ) {

			if ( !empty( $this->objects[$class] ) ) {

				return $this->objects[$class];
			}

			return false;
		}
	}}