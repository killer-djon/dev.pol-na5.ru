<?php

class Pager
{
    /**
     * @param int $current
     * @param int $limit
     * @param int $count
     * @param int $url
     * @param int $num
     * @return string
     */
    public static function render($current, $limit, $count, $url = '', $num = 1)
    {
        if( $count <= 1 ) return false;
        
        $html = '<ul class="pager-select">'."\n";
        
        if ( $current != 1 )
            $html .= '<li class="page-arrow"  id="arrow-left"><a href="'.$url.($current-1)*$num.'">&#8592;</a></li>'."\n";

        if ( $limit >= $count - 2  )
            for ($i=1; $i<=$count; $i++) {
				if ( $i != $current ) {						
					$html .= '<li><a href="'.$url.($i)*$num.'">'.$i.'</a></li>'."\n";						
				}
				else
					$html .= '<li class="current-page">'.$i.'</li>'."\n";
            }
        else if ( $limit < $count ) {	
            if ( $current < $limit + 1 ) {
				for ($i=1; $i<= $limit + 1; $i++) {
					if ( $i != $current )
						$html .= '<li><a href="'.$url.($i)*$num.'">'.$i.'</a></li>'."\n";
					else
						$html .= '<li class="current-page">'.$i.'</li>'."\n";
				}
				
				$html .= '<li class="pager-space">...</li>'."\n";
				$html .= '<li><a href="'.$url.$count*$num.'">'.$count.'</a></li>'."\n";
			}
            else if ( $current > $count - $limit ) {
				$html .= '<li><a href="'.$url.$num.'">1</a></li>'."\n";
				
				
				$html .= '<li class="pager-space">...</li>'."\n";
				for ($i = $count - $limit; $i<=$count; $i++) {
					if ( $i != $current )
						$html .= '<li><a href="'.$url.($i)*$num.'">'.$i.'</a></li>'."\n";
					else
						$html .= '<li class="current-page">'.$i.'</li>'."\n";
				}
			}
            else {
				$html .= '<li><a href="'.$url.$num.'">1</a></li>'."\n";
				$html .= '<li class="pager-space">...</li>'."\n";
				for ($i = $current - 1; $i <= $current + 1; $i++) {
					if ( $i != $current )
						$html .= '<li><a href="'.$url.($i)*$num.'">'.$i.'</a></li>'."\n";
					else
						$html .= '<li class="current-page">'.$i.'</li>'."\n";
				}
				$html .= '<li class="pager-space">...</li>'."\n";
				$html .= '<li><a href="'.$url.$count*$num.'">'.$count.'</a></li>'."\n";
			}
        }
        
        if ( $current != $count )
			$html .= '<li class="page-arrow"  id="arrow-right"><a href="'.$url.($current+1)*$num.'">&#8594;</a></li>'."\n";
			
		$html .= '</ul>';	
		
    	return $html;
        
        
    }
	
    	/*var limit = param.limit || 0;
		var count = param.count || 0;
		var current = param.current || 1;
		
		if(count == 0) return false;
		
		var pagegoto = function (num) {
			alert('goto page '+num);
		};
		pagegoto = param.pagegoto || pagegoto;
		
		return this.each(function() {
			
			
			if( count <= 1 ) return false;
			
			var $ul = $('<ul class="pager-select"/>')
			
			if ( current != 1 )
			$('<li class="page-arrow">&#8592;</li>').appendTo($ul).click(function(){pagegoto( current - 1 )});

			if ( limit >= parseInt(count) - 2  )
				
				for (var i=1; i<=count; i++) {
					
					if ( i != current ) {						
						$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));						
					}
					else
						$ul.append('<li class="current-page>'+i+'</li>');
					
				}
				
			else if ( limit < count ) {				
				if ( current < parseInt(limit) + 1 ) {
					for (var i=1; i<= parseInt(limit) + 1; i++) {
						if ( i != current )
							$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));
						else
							$ul.append('<li class="current-page>'+i+'</li>');
					}
					
					$ul.append('<li class="pager-space">...</li>');
					$('<li>'+count+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(count));
				}
				else if ( current > count - limit ) {
					$('<li>1</li>').appendTo($ul).click(function(){pagegoto( 1)});
					
					
					$ul.append('<li class="pager-space">...</li>');
					for (var i=(count - limit); i<=count; i++) {
						if ( i != current )
							$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));
						else
							$ul.append('<li class="current-page>'+i+'</li>');
					}
				}
				else {
					$('<li>1</li>').appendTo($ul).click(function(){pagegoto( 1 )});
					$ul.append('<li class="pager-space">...</li>');
					for (var i=(current - 1); i<=(current + 1); i++) {
						if ( i != current )
							$('<li>'+i+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(i));
						else
							$ul.append('<li class="current-page">'+i+'</li>');
					}
					$ul.append('<li class="pager-space">...</li>');
					$('<li>'+count+'</li>').appendTo($ul).click(function(){pagegoto( this )}.bind(count));
				}
				
				
				
			}
				
			if ( current != count )
			$('<li class="page-arrow">&#8594;</li>').appendTo($ul).click(function(){pagegoto( current + 1 )});
			
			$(this).append($ul);*/
    
    
}

?>