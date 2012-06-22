<?php

	class Instapress_Core_Mail extends PhpMailer {
		// Aggregate the server access details. For example Gmail account to send emails.
		/*
		private $_smtpServer = 'smtp.gmail.com'; // Testing using gmail server.

		// SMTP credentials.
		*/

		private $_smtpServer = 'localhost';

		// SMTP credentials.
		private $_username = '';
		private $_password = '';

		private $_priority = 3;
		private $_smtpPort = 25;

		/**
		 * Instapress_Core_Mail class constructor.
		 * @param array $config
		 * @return Instapress_Core_Mail
		 */
		function __construct( $config = null ) {
			if( $config !== null and is_array( $config ) ) {
				foreach( $config as $key => $value ) {
					$varName = '_' . $key;
					if( isset( $this->$varName ) ) {
						$this->$varName = $value;
					}
				}
			}
			$this->configureSettings();
		}

		function configureSettings() {
			$this->Host = $this->_smtpServer;
			$this->Port = $this->_smtpPort;
			if( $this->_username !== '' ) {
				$this->SMTPAuth  = true;
				$this->Username  = $this->_username;
				$this->Password  =  $this->_password;
			}
			$this->Mailer = "smtp";
			$this->Priority = $this->_priority;
		}

		/**
		 * Set sending user's credentials.
		 * @param string $userEmail
		 * @param string $userName
		 * @return Instapress_Core_Mail
		 */
		function setFrom( $userEmail, $userName='' ) {
			$this->From = $userEmail;
			$this-> FromName = $userName;
			return $this;
		}

		/**
		 * Adds mail recipient.
		 * @param string $userEmail
		 * @param string $userName optional
		 * @return Instapress_Core_Mail
		 */
		function addRecipient( $userEmail, $userName='' ) {
			$this->AddAddress( $userEmail, $userName );
			return $this;
		}

		/**
		 * Adds carbon copy mail receiver.
		 * @param string $userEmail
		 * @param string $userName
		 * @return Instapress_Core_Mail
		 */
		function cc( $userEmail, $userName='' ) {
			$this->AddCC( $userEmail, $userName );
			return $this;
		}

		/**
		 * Adds blind carbon copy mail receiver.
		 * @param string $userEmail
		 * @param string $userName
		 * @return Instapress_Core_Mail
		 */
		function bcc( $userEmail, $userName='' ) {
			$this->AddBCC( $userEmail, $userName );
			return $this;
		}

		/**
		 * Constructs mail body( subject as wekk as body ).
		 * @param string $subject
		 * @param string $body
		 * @param boolean $htmlFormat
		 * @return Instapress_Core_Mail
		 */
		function buildMail( $subject, $bodyText, $bodyHtml ) {
			$this->Subject = $subject;
			$this->isHTML( true );
			$this->AltBody = $bodyText;
			$this->Body = $bodyHtml;
			return $this;
		}
		
		/**
		 * Adds an attachment to the mail.
		 * @param string $filePath
		 * @return Instapress_Core_Mail
		 */
		function attachFile( $filePath, $fileName = '' ) {
			$this->AddAttachment( $filePath, $fileName );
			return $this;	
		}
	
		/**
		 * Sends the mail to the user(s).
		 * @return void
		 */
		function sendMail() {
			$sent = $this->send();
			if( $sent ) {
				$this->ClearAddresses();
				$this->ClearAttachments();
			}
			return $sent;
		}
	}
