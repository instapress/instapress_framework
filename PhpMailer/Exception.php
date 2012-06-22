<?php

	class PhpMailer_Exception extends Exception {
		public function errorMessage() {
			$errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
			return $errorMsg;
		}
	}