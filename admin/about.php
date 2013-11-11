<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	function nggallery_admin_about()  {

	?>

	<div class="wrap">
	<?php include('templates/social_media_buttons.php'); ?>
    <?php screen_icon( 'nextgen-gallery' ); ?>
	<h2><?php _e('Copyright notes / Credits', 'nggallery') ;?></h2>
	<div id="poststuff">

		<div class="postbox">
			<h3 class="hndle"><span><?php _e('Contributors / Tribute to', 'nggallery'); ?></span></h3>
		    <div class="inside">
				<p><?php _e('If you study the code of this plugin, you\'ll find we\'ve included a lot of good, existing code and ideas. We\'d like to thank the following people for their work:', 'nggallery') ;?></p>
				<ul class="ngg-list">
				<li><a href="http://wordpress.org" target="_blank">The WordPress Team</a> <?php _e('for their great documented code', 'nggallery') ;?></li>
				<li><a href="http://jquery.com" target="_blank">The jQuery Team</a> <?php _e('for jQuery, which is the best Web2.0 framework', 'nggallery') ;?></li>
				<li><a href="http://www.gen-x-design.com" target="_blank">Ian Selby</a> <?php _e('for the fantastic PHP Thumbnail Class', 'nggallery') ;?></li>
				<li><a href="http://www.lesterchan.net/" target="_blank">GaMerZ</a> <?php _e('for a lot of very useful plugins and ideas', 'nggallery') ;?></li>
				<li><a href="http://www.laptoptips.ca/" target="_blank">Andrew Ozz</a> <?php _e('for Shutter Reloaded, a real lightweight image effect', 'nggallery') ;?></li>
				<li><a href="http://www.jeroenwijering.com/" target="_blank">Jeroen Wijering</a> <?php _e('for the best Media Flash Scripts on earth', 'nggallery') ;?></li>
				<li><a href="http://field2.com" target="_blank">Ben Dunkle</a> <?php _e('for the Gallery Icon', 'nggallery') ;?></li>
				<li><a href="http://watermark.malcherek.com/" target="_blank">Marek Malcherek</a> <?php _e('for the Watermark plugin', 'nggallery') ;?></li>
				</ul>
				<p><?php _e('If you don\'t see your name on this list and we\'ve integrated some of your code into the plugin, don\'t hesitate to email me.', 'nggallery') ;?></p>
			</div>
		</div>
        <div class="postbox">
            <h3 class="hndle"><span><?php _e('NextCellent', 'nggallery'); ?></span></h3>
            <div class="inside">
                <p><?php _e('NextCellent Gallery is based on code originally thanks to Alex Rabe who maintained it through 2011. Special thanks to Photocrati Media which followed the work.', 'nggallery') ;?></p>
                <p><?php ngg_list_contributors(); ?></p>
            </div>
        </div>
		<div class="postbox">
			<h3 class="hndle"><span><?php _e('How to support ?', 'nggallery'); ?></span></h3>
			<div class="inside">
				<p><?php _e('There are several ways to contribute:', 'nggallery') ;?></p>
				<ul class="ngg-list">
					<li><strong><?php _e('Send us bugfixes / code changes', 'nggallery') ;?></strong><br /><?php _e('The most motivated support for this plugin are your ideas and brain work.', 'nggallery') ;?></li>
					<li><strong><?php _e('Translate the plugin', 'nggallery') ;?></strong><br /><?php _e('To help people to work with this plugin, we would like to have it in all available languages.', 'nggallery') ;?></li>
					<li><strong><?php _e('Place a link to the plugin in your blog/webpage', 'nggallery') ;?></strong><br /><?php _e('Yes, sharing and linking are also supportive and helpful.', 'nggallery') ;?></li>
				</ul>
			</div>
		</div>

	</div>
	</div>

	<?php
}

/*
20131004: Disabled by Photocrati. There is no more ngg_list_support
<div class="postbox" id="donators">
<h3 class="hndle"><span><?php _e('Thanks!', 'nggallery'); ?></span></h3>
<div class="inside">
<p><?php _e('We would like to thank the following people who have supported the NextGEN Gallery plugin:', 'nggallery'); ?></p>
<p><a href="http://www.boelinger.com/heike/" target="_blank">HEIKE</a>, < ? php ngg_list_support(); ? ></p>
</div>
</div>

 */

function ngg_list_contributors()	{
/* The list of my contributors. Thanks to all of them !*/

	$contributors = array(
	'Anty (Code contributor)' => 'http://www.anty.at/',
	'Bjoern von Prollius (Code contributor)' => 'http://www.prollius.de/',
	'Simone Fumagalli (Code contributor)' => 'http://www.iliveinperego.com/',
	'Vincent Prat (Code contributor)' => 'http://www.vincentprat.info',
	'Frederic De Ranter (AJAX code contributor)' => 'http://li.deranter.com/',
	'Christian Arnold (Code contributor)' => 'http://blog.arctic-media.de/',
	'Thomas Matzke (Album code contributor)' => 'http://mufuschnu.mu.funpic.de/',
	'KeViN (Sidebar Widget developer)' => 'http://www.kev.hu/',
	'Lazy (German Translation)' => 'http://www.lazychris.de/',
	'Lise (French Translation)' => 'http://liseweb.fr/',
	'Anja (Dutch Translation)' => 'http://www.werkgroepen.net/wordpress',
	'Adrian (Indonesian Translation)' => 'http://adrian.web.id/',
	'Gaspard Tseng / SillyCCSmile (Chinese Translation)' => '',
	'Mika Pennanen (Finnish Translation)' => 'http://kapsi.fi/~penni',
	'Wojciech Owczarek (Polish Translation)' => 'http://www.owczi.net',
	'Dilip Ramirez (Spanish Translation)' => 'http://jmtd.110mb.com/blog',
	'Oleinikov Vedmak Evgeny (Russian Translation)' => 'http://ka-2-03.mirea.org/',
	'Sebastien MALHERBE	(Logo design)' => 'http://www.7vision.com/',
	'Claudia (German documentation)' => 'http://www.blog-werkstatt.de/',
	'Robert (German documentation)' => 'http://www.curlyrob.de/',
	'Pierpaolo Mannone (Italian Translation)' => 'http://www.interscambiocasa.com/',
	'Mattias Tengblad (Swedish Translation)' => 'http://wp-support.se/',
	'M&uuml;fit Kiper (Swedish Translation)' => 'http://www.kiper.se/',
	'Gil Yaker (Documentation)' => 'http://bamboosoup.com/',
	'Morten Johansen (Danish Translation)' => 'http://www.fr3ak.dk/',
	'Vidar Seland (Norwegian Translation)' => 'http://www.viidar.net/',
	'Emre G&uuml;ler (Turkish Translation)' => 'http://www.emreguler.com/',
	'Emilio Lauretti (Italian Translation)' => '',
	'Jan Angelovic (Czech Translation)' => 'http://www.angelovic.cz/',
	'Laki (Slovak Translation)' => 'http://www.laki.sk/',
	'Rowan Crane (WPMU support)' => 'http://blog.rowancrane.com/',
	'Kuba Zwolinski (Polish Translation)' => 'http://kubazwolinski.com/',
	'Rina Jiang (Chinese Translation)' => 'http://http://mysticecho.net/',
	'Anthony (Chinese Translation)' => 'http://www.angryouth.com/',
	'Milan Vasicek (Czech Translation)' => 'http://www.NoWorkTeam.cz/',
	'Joo Gi-young (Korean Translation)' => 'http://lombric.linuxstudy.pe.kr/wp/',
	'Oleg A. Safonov (Russian Translation)' => 'http://blog.olart.ru',
	'AleXander Kirichev (Bulgarian Translation)' => 'http://xsakex.art-bg.org/',
	'Richer Yang (Chinese Translation)' => 'http://fantasyworld.idv.tw/',
	'Bill Jones (Forums contributor)' => 'http://jonesphoto.bluehorizoninternet.com/',
	'TheDonSansone (Forums contributor)' => 'http://abseiling.200blogs.co.uk/',
	'Komyshov (Russian Translation)' => 'http://kf-web.ru/',
	'aleX Zhang (Chinese Translation)' => 'http://zhangfei.info/',
	'TheSoloist (Chinese Translation)' => 'http://www.soloist-ic.cn/',
	'Nica Luigi Cristian (Romanian Translation)' => 'http://www.cristiannica.com/',
	'Zdenek Hatas (Czech Translation)' => '',
	'David Potter (Documentation and Help)' => 'http://dpotter.net/',
	'Carlale Chen (Chinese Translation)' => 'http://0-o-0.cc/',
	'Nica Luigi Cristian (Romanian Translation)' => 'http://www.cristiannica.com/',
	'Igor Shevkoplyas (Russian Translation)' => 'http://www.russian-translation-matters.com',
	'Alexandr Kindras (Code contributor)' => 'http://www.fixdev.com',
	'Manabu Togawa (Japanese Translation)' => 'http://www.churadesign.com/',
	'Serhiy Tretyak (Ukrainian Translation)' => 'http://designpoint.com.ua/',
	'Janis Grinvalds (Latvian Translation)' => 'http://riga.bmxrace.lv/',
	'Kristoffer Th&oslash;ring (Norwegian Translation)' => '',
	'Flactarus (Italian Translation)' => 'http://www.giroevago.it',
	'Felip Alfred Galit&oacute; i Trilla (Catalan Translation)' => 'http://www.bratac.cat',
	'Luka Komac (Slovenian Translation)' => 'http://www.komac.biz',
    'Dimitris Ikonomou / Nikos Mouratidis (Greek Translation)' => 'http://www.kepik.gr'
	);

	ksort($contributors);
	$i = count($contributors);
	foreach ($contributors as $name => $url)
	{
		if ($url)
			echo "<a href=\"$url\" target=\"_blank\">$name</a>";
		else
			echo $name;
		$i--;
		if ($i == 1)
			echo " & ";
		elseif ($i)
			echo ", ";
	}
}

/**
 * 20131004: Deprecated since this list dissapear.
 */
function ngg_list_support()	{
/* The list of my supporters. Thanks to all of them !*/

	global $ngg;

	$supporter = nggAdminPanel::get_remote_array($ngg->donators);

	// Ensure that this is a array
	if ( !is_array($supporter) )
		return _e('and all donators...', 'nggallery');

	ksort($supporter);
	$i = count($supporter);
	foreach ($supporter as $name => $url)
	{
		if ($url)
			echo "<a href=\"$url\" target=\"_blank\">$name</a>";
		else
			echo $name;
		$i--;
		if ($i == 1)
			echo " & ";
		elseif ($i)
			echo ", ";
	}
}
?>
