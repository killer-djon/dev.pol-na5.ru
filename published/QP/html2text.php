<?php
	function html2text( $string, $otherTags = true )
	//
	// Converts HTML to string
	//
	//		Parameters:
	//			$string - source string
	//
	//		Returns string
	//
	{
		global $silentMode;

		$silentMode = true;
		$prevSilentModeValue = $silentMode;

		// Remove PHP if it exists
		//
		while( substr_count( $string, '<'.'?' ) && substr_count( $string, '?'.'>' ) && strpos( $string, '?'.'>', strpos( $string, '<'.'?' ) ) > strpos( $string, '<'.'?' ) )
			$string = substr( $string, 0, strpos( $string, '<'.'?' ) ) . substr( $string, strpos( $string, '?'.'>', strpos( $string, '<'.'?' ) ) + 2 );

		// Remove comments
		//
		while( substr_count( $string, '<!--' ) && substr_count( $string, '-->' ) && strpos( $string, '-->', strpos( $string, '<!--' ) ) > strpos( $string, '<!--' ) )
			$string = substr( $string, 0, strpos( $string, '<!--' ) ) . substr( $string, strpos( $string, '-->', strpos( $string, '<!--' ) ) + 3 );

		// Now make sure all HTML tags are correctly written (> not in between quotes)
		//
		for( $x = 0, $goodStr = '', $is_open_tb = false, $is_open_sq = false, $is_open_dq = false; strlen( $chr = $string{$x} ); $x++ ) {

			// Take each letter in turn and check if that character is permitted there
			//
			switch( $chr ) {

				case '<':
					if( !$is_open_tb && strtolower( substr( $string, $x + 1, 5 ) ) == 'style' ) {
						$string = substr( $string, 0, $x ) . substr( $string, strpos( strtolower( $string ), '</style>', $x ) + 7 ); $chr = '';
					} elseif( !$is_open_tb && strtolower( substr( $string, $x + 1, 6 ) ) == 'script' ) {
						$string = substr( $string, 0, $x ) . substr( $string, strpos( strtolower( $string ), '</script>', $x ) + 8 ); $chr = '';
					} elseif( !$is_open_tb ) { $is_open_tb = true; } else { $chr = '&lt;'; }

					break;
				case '>':
					if( !$is_open_tb || $is_open_dq || $is_open_sq ) { $chr = '&gt;'; } else { $is_open_tb = false; }

					break;

				case '"':
					if( $is_open_tb && !$is_open_dq && !$is_open_sq ) { $is_open_dq = true; }
					elseif( $is_open_tb && $is_open_dq && !$is_open_sq ) { $is_open_dq = false; }
					else { $chr = '&quot;'; }
					break;

				case "'":
					if( $is_open_tb && !$is_open_dq && !$is_open_sq ) { $is_open_sq = true; }
					elseif( $is_open_tb && !$is_open_dq && $is_open_sq ) { $is_open_sq = false; }
			} $goodStr .= $chr;
		}

		// Now that the page is valid for strip_tags, strip all unwanted tags
		//
		$goodStr = strip_tags( $goodStr, '<title><hr><h1><h2><h3><h4><h5><h6><div><p><pre><sup><ul><ol><br><dl><dt><table><caption><tr><li><dd><th><td><a><area><img><form><input><textarea><button><select><option>' );

		//strip extra whitespace except between <pre> and <textarea> tags
		//
		$string = preg_split( "/<\/?pre[^>]*>/ui", $goodStr );

		for( $x = 0; is_string( $string[$x] ); $x++ ) {

			if( $x % 2 ) { $string[$x] = '<pre>'.$string[$x].'</pre>'; } else {

				$goodStr = preg_split( "/<\/?textarea[^>]*>/ui", $string[$x] );

				for( $z = 0; is_string( $goodStr[$z] ); $z++ ) {

					if( $z % 2 ) { $goodStr[$z] = '<textarea>'.$goodStr[$z].'</textarea>'; } else {

						$goodStr[$z] = preg_replace( "/\s+/u", ' ', $goodStr[$z] );

				} }

				$string[$x] = implode('',$goodStr);
		} }

		$goodStr = implode('',$string);

		// Remove all options from select inputs
		//
		$goodStr = preg_replace( "/<option[^>]*>[^<]*/ui", '', $goodStr );

		// Replace all tags with their text equivalents
		//
		$goodStr = preg_replace( "/<(h|div|p)[^>]*>/ui", "\n\n", $goodStr );
		$goodStr = preg_replace( "/<sup[^>]*>/ui", '^', $goodStr );
		$goodStr = preg_replace( "/<(ul|ol|br|dl|dt|table|caption|\/textarea|tr[^>]*>\s*<(td|th))[^>]*>/ui", "\n", $goodStr );
		$goodStr = preg_replace( "/<li[^>]*>/ui", "\n- ", $goodStr );
		$goodStr = preg_replace( "/<dd[^>]*>/ui", "\n\t", $goodStr );
		$goodStr = preg_replace( "/<(th|td)[^>]*>/ui", "\t", $goodStr );
		$goodStr = preg_replace( "/<a[^>]* href=(\"((?!\"|#|javascript:)[^\"#]*)(\"|#)|'((?!'|#|javascript:)[^'#]*)('|#)|((?!'|\"|>|#|javascript:)[^#\"'> ]*))[^>]*>/ui", "$2$4$6 ", $goodStr );

		if ( $otherTags )
		{
			$goodStr = preg_replace( "/<(\/title|hr)[^>]*>/ui", "\n          --------------------\n", $goodStr );
			$goodStr = preg_replace( "/<img[^>]* alt=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/ui", "[IMAGE: $2$3$4] ", $goodStr );
			$goodStr = preg_replace( "/<form[^>]* action=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/ui", "\n[FORM: $2$3$4] ", $goodStr );
			$goodStr = preg_replace( "/<(input|textarea|button|select)[^>]*>/ui", "[INPUT] ", $goodStr );
		}

		// Strip all remaining tags (mostly closing tags)
		//
		$goodStr = strip_tags( $goodStr );

		// Convert HTML entities
		//
		$goodStr = strtr( $goodStr, array_flip( get_html_translation_table( HTML_ENTITIES ) ) );

		preg_replace( "/&#(\d+);/me", "chr('$1')", $goodStr );

		$silentMode = $prevSilentModeValue;

		// Make sure there are no more than 3 linebreaks in a row and trim whitespace
		//
		return preg_replace( "/^\n*|\n*$/", '', preg_replace( "/[ \t]+(\n|$)/", "$1", preg_replace( "/\n(\s*\n){2}/", "\n\n", preg_replace( "/\r\n?|\f/", "\n", str_replace( chr(160), ' ', $goodStr ) ) ) ) );
	}
?>