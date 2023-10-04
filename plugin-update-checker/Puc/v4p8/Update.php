<?php
if ( !class_exists('Puc_v4p8_Update', false) ):

	/**
	 * A simple container class for holding information about an available update.
	 *
	 * @author Janis Elsts
	 * @access public
	 */
	abstract class Puc_v4p8_Update extends Puc_v4p8_Metadata {
		public $slug;
		public $version;
		public $download_url;
		public $translations = array();

		/**
		 * @return string[]
		 */
		protected function getFieldNames() {
			return array('slug', 'version', 'download_url', 'translations');
		}

		public function toWpFormat() {
			$update = new stdClass();

			$this->download_url = $this->return_latest_plugin_zip_file();
			//echo $this->download_url;exit;
			// $this->download_url = 'https://raw.githubusercontent.com/ttisi/tti-platform/master/tti-platform.zip?token=API_TOKEN_HERE';
			
			$update->slug = $this->slug;
			$update->new_version = $this->version;
			$update->package = $this->download_url;

			return $update;
		}

		/**
		* Function get latest plugin details file from Github.
		*/
		public function return_latest_plugin_zip_file () {
			$url = 'https://api.github.com/repos/ttisi/tti-platform/contents?access_token=API_TOKEN_HERE';
			$data = wp_remote_get( $url );
			$request = wp_remote_retrieve_body($data);
			$request = json_decode($request);
			foreach ($request as $key => $value) {
			    if($value->name == 'tti-platform.zip') {
			        return $value->download_url;
			    }
			}

			return null;
		}
	}

endif;
