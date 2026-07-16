<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="maincontentcontainer">
 *
 * @package Quark
 * @since Quark 1.0
 */
?><!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->


<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!-- moved to functions <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">-->
	<meta http-equiv="cleartype" content="on">

   <!--favicon multiple platforms (realfavicongenerator.net) -->
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#ea2e2e">
<meta name="msapplication-TileColor" content="#b91d47">
<meta name="theme-color" content="#ffffff">

	<!-- Responsive and mobile friendly stuff -->
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- SET GOOGLE FONT -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..700&display=swap" rel="stylesheet">
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="paddingtop"></div>
<div id="wrapper" class="hfeed site">

 <a href="#primary" class="header-down-arrow" >

<svg width="34" height="34" version="1.1" id="chevronarrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 455 455" style="enable-background:new 0 0 455 455;" xml:space="preserve">
<path d="M227.5,0C101.855,0,0,101.855,0,227.5S101.855,455,227.5,455S455,353.145,455,227.5S353.145,0,227.5,0z M227.5,327.148
	L99.411,199.476l21.178-21.248L227.5,284.791l106.911-106.563l21.178,21.248L227.5,327.148z"/>
</svg>

</a>	

	<div id="headercontainer">
	
		<header id="masthead" class="site-size site-header row" role="banner">
	   
		
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" rel="home">		</a>

			<div class="flexcontainer col grid_12_of_12 site-title ">

                  <?php if  ( !is_front_page()  ) {?>
	            <h1 id="mainlogo">
                <?php }else{ ?>
                <h1 id="mainlogo" class="hide">
               <?php  }?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" rel="home">
						<?php 
						$headerImg = get_header_image();
                        //$headerImg = get_bloginfo( 'stylesheet_directory') . '/images/FWlogo-black.svg';
                       // echo $headerimg; ?>
						
                        <img class="normal topimg" src="<?php echo $headerImg; ?>" height="60" width="150" alt="site logo and link to home" />
						
						
					</a>
				</h1>
                

             


				
				<!-- hamburger menu -->
				<div class="menucont">
				
				<?php if ( is_front_page() ) { ?>
				<!-- <input id="burger" type="checkbox" checked /> -->
                <input id="burger" type="checkbox" />
				<?php } else { ?>
				<input id="burger" type="checkbox"  />
				<?php }  ?>
				<label for="burger">
					<span></span>
					<span></span>
					<span></span>
				</label>
				<div id="burgerbox"></div>	
				<nav id="site-navigation" class="main-navigation" role="navigation">
					<h3 class="menu-toggle assistive-text"><?php esc_html_e( 'Menu', 'quark' ); ?></h3>
					<div class="assistive-text skip-link"><a href="#primary" title="<?php esc_attr_e( 'Skip to content', 'quark' ); ?>"><?php esc_html_e( 'Skip to content', 'quark' ); ?></a></div>
					

					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
                   
				</nav> <!-- /.site-navigation.main-navigation -->
				
				</div><!-- end hamburger menu -->
			</div> <!-- /.col.grid_7_of_12 -->
		</header> <!-- /#masthead.site-header.row -->

	</div> <!-- /#headercontainer -->

	<?php if ( !is_404() ) { ?>  



<?php if (has_post_thumbnail( $post->ID ))  : //get featured image ?>
<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), '2048x2048' );
$image = $image[0]; ?>
<?php else :
$image = get_bloginfo( 'stylesheet_directory') . '/images/coverdefault.jpg'; ?>
<?php endif; ?>

<?php if (is_home( ))  { //adding isfrontpage to only look for the featured image on the front page
}
?>

<?php //} 
$gradient="linear-gradient(270deg, rgba(0, 0, 0, 0) 35.63%, #000000 94.13%)";
$gradient="linear-gradient(270deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0) 65%, rgba(255,255,255,1) 65%, rgba(255,255,255,1) 100%)"

?>

	<?php if  ( is_front_page()  ) {?>
			<div id="bannercontainer" class="homepg" style="background-image:<?php echo $gradient; ?>, url(<?php echo $image; ?>)" >





    

		<div class="flexcontainer banner row">
         
			<?php 
				// Count how many banner sidebars are active so we can work out how many containers we need
				$bannerSidebars = 0;
				for ( $x=1; $x<=2; $x++ ) {
					if ( is_active_sidebar( 'frontpage-banner' . $x ) ) {
						$bannerSidebars++;
					}
				}

				// If there's one or more one active sidebars, create a row and add them
				if ( $bannerSidebars > 0 ) { ?>
					<?php
					// Work out the container class name based on the number of active banner sidebars
					$containerClass = "grid_6_of_12";

					// Display the active banner sidebars
					for ( $x=1; $x<=2; $x++ ) {
						if ( is_active_sidebar( 'frontpage-banner'. $x ) ) { ?>
							<div class="slbanner <?php echo 'banner'. $x ?> <?php echo $containerClass?>">
								<div class="widget-area" role="complementary">
								
						

                                    <?php 
                                     if ( current_user_can( 'manage_options' ) ) {
                    	            
//echo '<a class="dsedit" href="'.admin_url( 'widgets.php', 'https' ).'">Edit FBBanner Widget</a>';
 }    
                                    dynamic_sidebar( 'frontpage-banner'. $x ); 
                                    
                                ?>
								</div> <!-- /.widget-area -->
							</div> <!-- /.col.<?php echo $containerClass?> -->
						<?php }
					} ?>

				<?php }?>
				
				
		

			
			
		</div> <!-- /.banner.row -->
	
          
	</div> <!-- /#bannercontainer -->

			<?php }	elseif ( is_page_template( 'page-templates/nofeatured.php' ) || is_page_template( 'page-templates/recent.php' )) { // nofeatured.php is used to indicate no featured image?>
    
   <?php  } elseif  ( is_page() ) {?>
			<div id="bannercontainer" class="subpage" style="background-image:<?php echo $gradient; ?>, url(<?php echo $image; ?>)" >

		<div id="subbanner" class="flexcontainer banner row">
			
				
			
						
							<div class="slbanner">
                             <header class="page-header">
                           
		                    	<h1 class="entry-title"><?php the_title(); ?></h1>
                                   <?php  if( $post->post_excerpt ) {
                                        $subtitle = get_the_excerpt();
                                         echo "<p class='subtitle'>".$subtitle."</p>";
                                   }  ?>
		                    </header>
                            </div> <!-- /.col.<?php echo $containerClass?> -->
						
					

			
				
				
		

			
			
		</div> <!-- /.banner.row -->
	

	</div> <!-- /#bannercontainer -->
       <?php  } elseif  (  is_home() ) {
            $bid = get_option( 'page_for_posts' );
            $image = wp_get_attachment_image_src( get_post_thumbnail_id($bid), '2048x2048' );
            $image = $image[0];
            $title = get_the_title( $bid );
            $posttitle = "";

          if ( has_excerpt($bid) ) {
        	 $subtitle = get_the_excerpt($bid);
            } else { 
            $posttitle = get_the_title();
	        $subtitle = get_the_excerpt();
            }
            


            ?>
			<div id="bannercontainer" class="subpage" style="background-image:<?php echo $gradient; ?>, url(<?php echo $image; ?>)" >

		<div id="subbanner" class="flexcontainer banner row">
			
				
			
						
							<div class="slbanner">
                             <header class="page-header">
                             	<?php   echo "<h1 class='page-title'>".$title."</h1>";
                                    if(empty($posttitle)) {
                                          echo "Empty string";
                                    }
                                    else {
                                        echo "<h3 class='entry-title'>".$posttitle."</h3>";
                                    }

                                    echo "<p class='subtitle'>".$subtitle."</p>";
                                    ?>
		                  
		                    </header>
                            </div> <!-- /.col.<?php echo $containerClass?> -->
						
					

			
				
				
		

			
			
		</div> <!-- /.banner.row -->
	

	</div> <!-- /#bannercontainer -->

	<?php  } else { // end banner?>
	
	<div class="nobanner" ></div>
<?php }	// end if is not page?>
	
<?php }	// end if 404 ?>

	<div id="maincontentcontainer" class=" maincontent" >
  
		<?php	do_action( 'quark_before_woocommerce' ); ?>