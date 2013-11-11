<?php
/**
 * nggNavigation - PHP class for the pagination
 * 
 * @package NextGEN Gallery
 * @author Alex Rabe
 * 
 * @version 1.0.1
 * @access public
 */
class nggNavigation {

	/**
	 * Return the navigation output
	 *
	 * @access public
	 * @var string
	 */
	var $output = false;

	/**
	 * Link to previous page
	 *
	 * @access public
	 * @var string
	 */
	var $prev = false;

	/**
	 * Link to next page
	 *
	 * @access public
	 * @var string
	 */
	var $next = false;

	/**
	 * Main constructor - Does nothing.
	 * Call create_navigation() method when you need a navigation.
	 * 
	 */	
	function __construct() {
		return;		
	}

	/**
	 * nggNavigation::create_navigation()
	 * 
	 * @param mixed $page
	 * @param integer $totalElement 
	 * @param integer $maxElement
	 * @return string pagination content
	 */
	function create_navigation($page, $totalElement, $maxElement = 0) {
		global $nggRewrite;
        
        $prev_symbol = apply_filters('ngg_prev_symbol', '&#9668;');
		$next_symbol = apply_filters('ngg_prev_symbol', '&#9658;');
        
		if ($maxElement > 0) {
			$total = $totalElement;
			
			// create navigation	
			if ( $total > $maxElement ) {
				$total_pages = ceil( $total / $maxElement );
				$r = '';
				if ( 1 < $page ) {
					$args['nggpage'] = ( 1 == $page - 1 ) ? FALSE : $page - 1;
					$previous = $args['nggpage'];
					if (FALSE == $args['nggpage']) {
						$previous = 1; 
					}
					$this->prev = $nggRewrite->get_permalink ( $args );
					$r .=  '<a class="prev" id="ngg-prev-' . $previous . '" href="' . $this->prev . '">' . $prev_symbol . '</a>';
				}
				
				$total_pages = ceil( $total / $maxElement );
				
				if ( $total_pages > 1 ) {
					for ( $page_num = 1; $page_num <= $total_pages; $page_num++ ) {
						if ( $page == $page_num ) {
							$r .=  '<span class="current">' . $page_num . '</span>';
						} else {
							$p = false;
							if ( $page_num < 3 || ( $page_num >= $page - 3 && $page_num <= $page + 3 ) || $page_num > $total_pages - 3 ) {
								$args['nggpage'] = ( 1 == $page_num ) ? FALSE : $page_num;
								$r .= '<a class="page-numbers" href="' . $nggRewrite->get_permalink( $args ) . '">' . ( $page_num ) . '</a>';
								$in = true;
							} elseif ( $in == true ) {
								$r .= '<span class="more">...</span>';
								$in = false;
							}
						}
					}
				}
				
				if ( ( $page ) * $maxElement < $total || -1 == $total ) {
					$args['nggpage'] = $page + 1;
					$this->next = $nggRewrite->get_permalink ( $args );
					$r .=  '<a class="next" id="ngg-next-' . $args['nggpage'] . '" href="' . $this->next . '">' . $next_symbol . '</a>';
				}
				
				$this->output = "<div class='ngg-navigation'>$r</div>";
			} else {
				$this->output = "<div class='ngg-clear'></div>"."\n";
			}
		}
		
		return $this->output;
	}
}
?>
