<?php

	/**
	 * MemcacheClass is a class to work with memcache storage.
	 * @author Rashid Mohamad
	 */
	class Memcache_Class {

		/**
		 * Cache max size, bytes.
		 * If maxSize == -1 use cache driver specific value for cache max size.
		 * @var integer
		 */
		private $maxSize = 0;

		/**
		 * Cache max items count
		 * @var integer
		 */
		private $maxItemsCount = 0;

		/**
		 * Clean cache frequensy factor.
		 * Clean cache operation will start randomically with random factor N if cache overflow.
		 * @var integer
		 */
		private $cleanCacheFactor = 10;

		/**
		 * Memcache handler
		 * @var Memcache
		 */
		private $memcache = null;

		/**
		 * Stores memcache server stats.
		 * @var array Memcache statistics
		 */
		private $stats = null;

		/**
		 * Memcache host
		 * @var string
		 */
		private $host = '10.0.0.137';

		/**
		 * Memcache port
		 * @var integer
		 */
		private $port = 11211;

		/**
		 * Is memcached connection persistent or not
		 * @var boolean
		 */
		private $isPersistent = true;

		/**
		 * Class constructor. Setup primary parameters.
		 * @param array $params Primary properties
		 */
		public function __construct( $params = array() ) {
			$this->checkEnvironment();
			foreach( $params as $key => $value ) {
				if( isset( $this->$key ) ) {
					$this->$key = $value;
				}
			}
		}

		/**
		 * Get Memcache instance
		 * @return object Memcache instance
		 */
		private function getMemcache() {
			if( $this->memcache === null ) {
				$this->memcache = new Memcache();
				if( $this->isPersistent ) {
					if( !@$this->memcache->pconnect( $this->host, $this->port ) ) {
						throw new Exception( "Don't open memcached server persistent connection!" );
					}
				} else {
					if( !@$this->memcache->connect( $this->host, $this->port ) ) {
						throw new Exception( "Don't open memcached server connection!" );
					}
				}
			}
			return $this->memcache;
		}

		/**
		 * Class destructor. Close opened handlers.
		 */
		public function __destruct() {
			if( ( $memcache = $this->getMemcache() ) != null && !$this->isPersistent ) {
				$memcache->close();
			}
		}

		/**
		 * Get data
		 * @param mixed $key The key that will be associated with the item
		 * @param mixed $default Default value
		 * @return mixed Stored data
		 */
		public function get( $key, $default = null ) {
			$result = $this->getMemcache()->get( $key );
			return $result !== false ? $result : $default;
		}

		/**
		 * Store data
		 * @param string $key The key that will be associated with the item.
		 * @param mixed $value The variable to store.
		 * @param integer $expire Expiration time of the item. Unix timestamp or number of seconds.
		 * @return void
		 */
		public function set( $key, $value, $expire = 0 ) {
			// Check cache limits
			$err = null;
			if( ( $m = $this->getMaxItemsCount() ) > 0 && $this->getItemsCount() >= $m ) {
				$err = "Maximum items count attained!";
			}
			if( ( $m = $this->getMaxSize() ) > 0 && $this->getSize() >= $m ) {
				$err = "Maximum items count attained!";
			}
			// Check error
			if( $err != null ) {
				// Check clean cache factor
				if( $this->cleanCacheFactor > 0 && mt_rand( 0, $this->cleanCacheFactor - 1 ) == 0 ) {
					$this->clean();
					// Secondary check cache limits
					if( ( !( $m = $this->getMaxItemsCount() ) || $this->getItemsCount() < $m ) && ( !( $m = $this->getMaxSize() ) || $this->getSize() < $m ) ) {
						return;
					}
				}
				throw new Exception($err);
			}
			$this->getMemcache()->set( $key, $value, false, $expire );
		}

		/**
		* Get max items count
		* @return integer Maximum items count
		*/
		public function getMaxItemsCount() {
			return $this->maxItemsCount;
		}

		/**
		 * Remove data from the cache
		 * @param string $key The key that will be associated with the item
		 * @return void
		 */
		public function remove($key) {
			if( $this->getMemcache()->delete( $key ) && $this->stats && $this->stats[ 'curr_items' ] > 0) {
				$this->stats['curr_items']--;
			}
		}

		/**
		* Get cache max size. If maxSize == -1 use cache driver specific value of cache max size
		* @return integer Cache maximum size, bytes
		*/
		public function getMaxSize() {
			return $this->maxSize >= 0 ? $this->maxSize : $this->getTotalMaxSize();
		}

		/**
		 * Remove all cached data
		 * @return void
		 */
		public function removeAll() {
			if( !$items = $this->getStats( 'items' ) ) {
				return;
			}
			$memcache = $this->getMemcache();
			foreach ($items['items'] as $key => $item) {
				$dump = $memcache->getStats( 'cachedump', $key, $item['number'] * 2 );
				foreach( array_keys( $dump ) as $ckey ) {
					$memcache->delete( $ckey );
				}
			}
			$this->stats = null;
		}

		/**
		 * Clean expired cached data
		 * @return void
		 */
		public function clean() {
			if( !$items = $this->getStats( 'items' ) ) {
				return;
			}
			$memcache = $this->getMemcache();
			foreach( $items['items'] as $key => $item ) {
				$dump = $memcache->getStats( 'cachedump', $key, $item['number'] * 2 );
				foreach( array_keys( $dump ) as $ckey ) {
					$memcache->get( $ckey );
				}
			}
			$this->stats = null;
		}

		/**
		 * Get items count
		 * @return integer Items count
		 */
		public function getItemsCount() {
			return $this->getStats( 'curr_items' );
		}

		/**
		 * Get cached data size
		 * @return integer Cache size, bytes
		 */
		public function getSize() {
			return $this->getStats( 'bytes' );
		}

		/**
		 * Get total cache max size.
		 * @return integer Cache maximum size, bytes
		 */
		public function getTotalMaxSize() {
			return $this->getStats( 'limit_maxbytes' );
		}

		/**
		 * Get memcache statistics
		 * @param string $param Statistics paramater.
		 * @return array Memcache statistics.
		 */
		public function getStats( $param = null ) {
			if( $this->stats === null ) {
				$this->stats = $this->getMemcache()->getStats();
			}
			if( $param === null ) {
				return $this->stats;
			} else {
				if( isset( $this->stats[ $param] ) ) {
					return $this->stats[ $param];
				} else {
					$newStats = @$this->getMemcache()->getStats( $param );
					if( $newStats ) {
						$this->stats[ $param ] = $newStats;
						return $newStats;
					} else {
						return '';
					}
				}
			}
		}

		/**
		 * Check Memcache extension.
		 * @return void
		 */
		public function checkEnvironment() {
			if( !extension_loaded( 'memcache' ) ) {
				throw new Exception('Memcache extension not loaded!');
			}
		}
	}