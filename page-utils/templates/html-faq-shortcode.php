<?php 
$qnas = ( isset( $atts['data'] ) && !empty( $atts['data'] ) ) ? $atts['data'] : array();
$faq_header = ( isset( $atts['header'] ) && !empty( $atts['header'] ) ) ? $atts['header'] : '';

$shortcode_page_url = '';
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$shortcode_page_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

?>

<!-- FAQ -->
<div class="faq-container parent-container" itemscope itemtype="http://schema.org/FAQPage">
	<meta itemprop="mainEntityOfPage" content="<?php echo $shortcode_page_url; ?>">
	<h2 id="faq" class="faq-header" itemprop="name"><?php echo $faq_header; ?></h2>
  	<ul class="faq faq-wrapper">
  		<?php 
  		if( $qnas ) {
  			foreach ( $qnas as $qna ) {
  				?>
		<li class="faq-item" itemprop="mainEntity" itemscope itemtype="http://schema.org/Question">
	      	<h3 class="question" itemprop="name"><?php echo htmlspecialchars_decode( $qna->faq_question, ENT_QUOTES ); ?>
	        	<div class="plus-minus-toggle collapsed"></div>
	      	</h3>
	      	<div class="answer" itemprop="acceptedAnswer" itemscope itemtype="http://schema.org/Answer">
	      		<div itemprop="text"><?php echo htmlspecialchars_decode( $qna->faq_answer, ENT_QUOTES ); ?></div>
	      	</div>
	    </li>
  				<?php
  			}
  		}
  		?>
  	</ul>
</div>