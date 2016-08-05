<?php


	class paypal_class {
		var $last_error = null;
		var $ipn_log = null;
		var $ipn_log_file = null;
		var $ipn_response = null;
		var $ipn_data = array(  );
		var $fields = array(  );

		function paypal_class() {
			$this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
			$this->last_error = '';
			$this->ipn_log_file = LIB_PATH . 'ipn_log.txt';
			$this->ipn_log = true;
			$this->ipn_response = '';
			$this->add_field( 'rm', '2' );
			$this->add_field( 'cmd', '_xclick' );
		}

		function add_field($field, $value) {
			$this->fields['' . $field] = $value;
		}

		function submit_paypal_post() {
			echo '<form method="post" name="payment" action="' . $this->paypal_url . '">
';
			foreach ($this->fields as $name => $value) {
				echo '' . '<input type="hidden" name="' . $name . '" value="' . $value . '">';
			}

			echo '</form>
';
		}

		function validate_ipn() {
			$url_parsed = parse_url( $this->paypal_url );
			$post_string = '';
			foreach ($_POST as $field => $value) {
				$this->ipn_data['' . $field] = $value;
				$post_string .= $field . '=' . urlencode( $value ) . '&';
			}

			$post_string .= 'cmd=_notify-validate';
			$fp = fsockopen( $url_parsed[host], '80', $err_num, $err_str, 30 );

			if (!$fp) {
				$this->last_error = '' . 'fsockopen error no. ' . $errnum . ': ' . $errstr;
				$this->log_ipn_results( false );
				return false;
			}

			fputs( $fp, '' . 'POST ' . $url_parsed['path'] . ' HTTP/1.1
' );
			fputs( $fp, ( ( '' . 'Host: ' . $url_parsed['host'] . '' ) . '
' ) );
			fputs( $fp, 'Content-type: application/x-www-form-urlencoded
' );
			fputs( $fp, 'Content-length: ' . strlen( $post_string ) . '
' );
			fputs( $fp, 'Connection: close

' );
			fputs( $fp, $post_string . '

' );

			while (!feof( $fp )) {
				$this->ipn_response .= fgets( $fp, 1024 );
			}

			fclose( $fp );

			if (eregi( 'VERIFIED', $this->ipn_response )) {
				$this->log_ipn_results( true );
				return true;
			}

			$this->last_error = 'IPN Validation Failed.';
			$this->log_ipn_results( false );
			return false;
		}

		function log_ipn_results($success) {
			if (!$this->ipn_log) {
				return null;
			}

			$text = '[' . date( 'm/d/Y g:i A' ) . '] - ';

			if ($success) {
				$text .= 'SUCCESS!
';
			} 
else {
				$text .= 'FAIL: ' . $this->last_error . '
';
			}

			$text .= 'IPN POST Vars from Paypal:
';
			foreach ($this->ipn_data as $key => $value) {
				$text .= '' . $key . '=' . $value . ', ';
			}

			$text .= '
IPN Response from Paypal Server:
 ' . $this->ipn_response;
			$fp = fopen( $this->ipn_log_file, 'a' );
			fwrite( $fp, $text . '

' );
			fclose( $fp );
		}

		function dump_fields() {
			echo '<h3>paypal_class->dump_fields() Output:</h3>';
			echo '<table width="95%" border="1" cellpadding="2" cellspacing="0">
            <tr>
               <td bgcolor="black"><b><font color="white">Field Name</font></b></td>
               <td bgcolor="black"><b><font color="white">Value</font></b></td>
            </tr>';
			ksort( $this->fields );
			foreach ($this->fields as $key => $value) {
				echo '' . '<tr><td>' . $key . '</td><td>' . urldecode( $value ) . '&nbsp;</td></tr>';
			}

			echo '</table><br>';
		}
	}

?>

