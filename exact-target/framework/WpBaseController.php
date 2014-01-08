<?php

/**
 * Base Controller
 *
 * @author  Joe Sexton <joe.sexton@bigideas.com>
 */
if (!class_exists( 'WpBaseController' )){
	class WpBaseController {

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
		 * constructor
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   string $rootPath
		 * @param   string $rootUrl
		 */
		public function __construct( $rootPath, $rootUrl )
		{
			$this->rootPath = $rootPath;

			$this->fwPath         = $this->rootPath.'framework/';
			$this->controllerPath = $this->rootPath.'controller/';
			$this->modelPath      = $this->rootPath.'model/';
			$this->viewPath       = $this->rootPath.'view/';
			$this->assetPath      = $rootUrl.'assets/';

			register_activation_hook( __FILE__, array( $this, 'onActivation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'onDeactivation' ) );

			$this->_init();
		}

		/**
		 * enqueueScript
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   string $handle
		 * @param   string $file
		 * @param   array $deps
		 * @param   string $ver
		 * @param   boolean $footer
		 * @param   array $localizedVars
		 * @return  boolean
		 */
		public function enqueueScript( $handle, $script, $deps = array( 'jquery' ), $ver = null, $footer = true, $localizedVars = array() ) {

			$script = str_replace( ':', '/', $script );
			$file = $this->assetPath . 'js/' . $script . '.js';

			wp_register_script( $handle, $file, $deps, $ver, $footer );
			wp_enqueue_script( $handle );

			foreach ( $localizedVars as $name => $vars ) {

				wp_localize_script( $handle, $name, $vars );
			}

			return true;
		}

		/**
		 * enqueueStyle
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   string $handle
		 * @param   string $file
		 * @param   array $deps
		 * @param   string $ver
		 * @param   boolean $media
		 * @return  boolean
		 */
		public function enqueueStyle( $handle, $style, $deps = array(), $ver = null, $media = 'screen' ) {

			$style = str_replace( ':', '/', $style );
			$file = $this->assetPath . 'css/' . $style . '.css';

			wp_register_style( $handle, $file, $deps, $ver, $media );
			wp_enqueue_style( $handle );

			return true;
		}

		/**
		 * render view
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   string $view
		 * @param   array $args
		 * @param   boolean $echo
		 * @return  string | boolean
		 */
		function render( $view, $args = array(), $echo = true ){

			$view = str_replace( ':', '/', $view );
			$file = $this->viewPath . $view . '.php';
			$assetPath = $this->assetPath;

			if( !empty( $args ) )
				extract( $args );

			if ( !file_exists( $file ) )
				return false;

			if ( $echo ) {
				include $file ;

				return true;

			} else {
				ob_start();
				include $file;
				$output = ob_get_contents();
				ob_end_clean();

				return $output;
			}
		}

		/**
		 * renderShortcode
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 * @param   string $file
		 * @param   array $atts
		 * @param   array $pairs
		 * @param   string $shortcode
		 * @return  string
		 */
		public function renderShortcode( $view, $atts, $pairs = array(), $shortcode = '' ) {

			$view = str_replace( ':', '/', $view );
			$file = $this->viewPath . $view . '.php';
			$assetPath = $this->assetPath;

			extract( shortcode_atts( $pairs, $atts, $shortcode ) );

			if ( !file_exists( $file ) )
				return false;

			ob_start();
			include $file;
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

		/**
		 * add actions
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 */
		protected function _init() {}

		/**
		 * on activation
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 */
		public function onActivation() {}

		/**
		 * on deactivation
		 *
		 * @author  Joe Sexton <joe.sexton@bigideas.com>
		 */
		public function onDeactivation() {}
	}}