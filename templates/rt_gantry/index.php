<?php
/**
 * @package Gantry Template Framework - RocketTheme
 * @version 3.2.16 February 8, 2012
 * @author RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

// load and inititialize gantry class
require_once('lib/gantry/gantry.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $gantry->language; ?>" lang="<?php echo $gantry->language;?>" >
    <head>
        <?php
            $gantry->displayHead();
            $gantry->addStyles(array('template.css','joomla.css','style.css','customs.css'));
        ?>		
    </head>
    <body <?php echo $gantry->displayBodyTag(); ?>>
		<div id="page-wrapper">
			<?php /** Begin Drawer **/ if ($gantry->countModules('drawer')) : ?>
			<div id="rt-drawer">
				<div class="rt-container">
					<?php echo $gantry->displayModules('drawer','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Drawer **/ endif; ?>
			<?php /** Begin Top **/ if ($gantry->countModules('top')) : ?>
			<div id="rt-top" <?php echo $gantry->displayClassesByTag('rt-top'); ?>>
				<div class="rt-container">
					<?php echo $gantry->displayModules('top','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Top **/ endif; ?>
			<?php /** Begin Header **/ if ($gantry->countModules('header')) : ?>
			<div id="rt-header">
				<div class="rt-container">
					<div class="menu-top">
						<ul>
							<li><a href="#">Basket</a></li>
							<li><a href="#">Login</a></li>
							<li><a href="#">Sign Up</a></li>
						</ul>
					</div><!-- End .menu-top -->
					<?php echo $gantry->displayModules('header','standard','standard'); ?>
					
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Header **/ endif; ?>
			<?php /** Begin Menu **/ if ($gantry->countModules('navigation')) : ?>
			<div id="rt-menu" style="display:none;">
				<div class="rt-container">
					<div class="search-box" style="display:none;">
						<form method="post" action="">
							<input type="text" name="Input search" placeholder="Search" />
							<input type="submit" name="Button search" value=""/>
						</form>
					</div>
						
					<?php #echo $gantry->displayModules('navigation','basic','basic'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Menu **/ endif; ?>
			<?php /** Begin Showcase **/ if ($gantry->countModules('showcase')) : ?>
			<div id="rt-showcase" style="display:none;">
				<div class="rt-container">
					<?php echo $gantry->displayModules('showcase','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Showcase **/ endif; ?>
			<?php /** Begin Feature **/ if ($gantry->countModules('feature')) : ?>
			<div id="rt-feature">
				<div class="rt-container">		
			<?php /** Check homepage**/
				$app = JFactory::getApplication();
				$menu = $app->getMenu();
				if ($menu->getActive() == $menu->getDefault()) { ?>
					<style>
						#rt-mainbody .component-content{
							display:none;
						}
					</style>
					<div class="banner">
						<img src="templates/rt_gantry/images/banner.png" alt="" />
					</div>
			<?php }	?>
								
					<?php echo $gantry->displayModules('feature','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Feature **/ endif; ?>
			<?php /** Begin Utility **/ if ($gantry->countModules('utility')) : ?>
			<div id="rt-utility">
				<div class="rt-container">
					<?php echo $gantry->displayModules('utility','standard','basic'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Utility **/ endif; ?>
			<?php /** Begin Breadcrumbs **/ if ($gantry->countModules('breadcrumb')) : ?>
			<div id="rt-breadcrumbs">
				<div class="rt-container">
					<?php echo $gantry->displayModules('breadcrumb','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Breadcrumbs **/ endif; ?>
			<?php /** Begin Main Top **/ if ($gantry->countModules('maintop')) : ?>
			<div id="rt-maintop">
				<div class="rt-container">
					<?php echo $gantry->displayModules('maintop','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Main Top **/ endif; ?>
			<?php /** Begin Main Body **/ ?>
			<?php echo $gantry->displayMainbody('mainbody','sidebar','standard','standard','standard','standard','standard'); ?>
			<?php /** End Main Body **/ ?>
			<?php /** Begin Main Bottom **/ if ($gantry->countModules('mainbottom')) : ?>
			<div id="rt-mainbottom">
				<div class="rt-container">
					<?php echo $gantry->displayModules('mainbottom','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Main Bottom **/ endif; ?>
			<?php /** Begin Bottom **/ if ($gantry->countModules('bottom')) : ?>
			<div id="rt-bottom">
				<div class="rt-container">
					<?php echo $gantry->displayModules('bottom','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Bottom **/ endif; ?>
			<?php /** Begin Footer **/ if ($gantry->countModules('footer')) : ?>
			<div id="rt-footer">
				<div class="rt-container">
					<?php echo $gantry->displayModules('footer','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Footer **/ endif; ?>
			<?php /** Begin Copyright **/ if ($gantry->countModules('copyright')) : ?>
			<div id="rt-copyright">
				<div class="rt-container">
					<div class="border">
						<div class="border-top">
							<div class="border-bot">
								<div class="txt-copyright">
									&copy; Copyright Artson UGG Prt Ltd 2012
								</div>							
								<?php if ($this->countModules('menu-footer')) : ?>
									<div class="menu-bottom">
									<jdoc:include type="modules" name="menu-footer" />
									</div>
								<?php endif; ?>													
							</div>
						</div>
					</div>
					<?php echo $gantry->displayModules('copyright','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Copyright **/ endif; ?>
			<?php /** Begin Debug **/ if ($gantry->countModules('debug')) : ?>
			<div id="rt-debug">
				<div class="rt-container">
					<?php echo $gantry->displayModules('debug','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Debug **/ endif; ?>
			<?php /** Begin Analytics **/ if ($gantry->countModules('analytics')) : ?>
			<?php echo $gantry->displayModules('analytics','basic','basic'); ?>
			<?php /** End Analytics **/ endif; ?>
		
		<script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/custom.js" type="text/javascript"></script>
		</div>
	</body>
</html>
<?php
$gantry->finalize();
?>