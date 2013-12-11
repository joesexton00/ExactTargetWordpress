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

		if ( !$rootPath )
			$rootPath = dirname( __FILE__ );
		$this->rootPath = trailingslashit( $rootPath );

		$this->fwPath         = $this->rootPath.'framework/';
		$this->controllerPath = $this->rootPath.'controller/';
		$this->modelPath      = $this->rootPath.'model/';
		$this->viewPath       = $this->rootPath.'view/';
		$this->assetPath      = $pluginUrl.'assets/';

		$this->loadDirectory( $this->fwPath );
		$this->loadDirectory( $this->modelPath );
		$this->loadDirectory( $this->controllerPath );
	}

	/**
	 * on activation
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @param   string $dir
	 * @param   boolean $inst
	 * @return  boolean
	 */
	public function loadDirectory( $dir, $inst = false ){
		if ( !file_exists( $dir ) )
			return false;

		foreach ( scandir( $dir ) as $file ) {
			if( substr( $file, 0, 2 ) !== '._' && preg_match( "/.php$/i" , $file ) ) {
				require_once $dir . $file;

				if ( $inst ) {
					$class = str_replace( '.php', '', $file );
					$this->objects[$class] = new $class();
				}
			}
		}

		return true;
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
				$this->objects[$class] = new $class();
			}
		}

		return true;
	}

	/**
	 * get rootPath
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  string
	 */
	public function getRootPath() {

	    return $this->rootPath;
	}

	/**
	 * get fwPath
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  string
	 */
	public function getFwPath() {

	    return $this->fwPath;
	}

	/**
	 * get controllerPath
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  string
	 */
	public function getControllerPath() {

	    return $this->controllerPath;
	}

	/**
	 * get modelPath
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  string
	 */
	public function getModelPath() {

	    return $this->modelPath;
	}

	/**
	 * get viewPath
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  string
	 */
	public function getViewPath() {

	    return $this->viewPath;
	}

	/**
	 * get assetPath
	 *
	 * @author  Joe Sexton <joe.sexton@bigideas.com>
	 * @return  string
	 */
	public function getAssetPath() {

	    return $this->assetPath;
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