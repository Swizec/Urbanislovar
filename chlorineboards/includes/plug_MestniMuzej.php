<?php

/**
*     defines the plug_MestniMuzej class
*     @file                plug_MestniMuzej.php
*     @see plug_MestniMuzej
*/
/**
* this is a MestniMuzej plugin needed for some stuff for the skin
*     @class		   plug_MestniMuzej
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.1
*     @since        2nd July 2007
*     @package		     plug_MestniMuzej
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag

// class creation
$vars = array( 'debug' );
$visible = array( 'private' );
eval( Varloader::createclass( 'plug_MestniMuzej', $vars, $visible ) );
// end class creation

class Plug_MestniMuzej extends plug_MestniMuzej_def
{
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function Plug_MestniMuzej( $debug = FALSE )
	{
		global $template, $lang_loader, $security, $userdata;
		
		if ( $userdata[ 'user_skin' ] != 'MestniMuzej' )
		{ // don't do stuff if a different skin is being loaded
			return;
		}
		
		$this->lang = $lang_loader->get_lang( 'ShowEvent' );
		
		if ( $lang_loader->board_lang == 'en' )
		{
			$lang = 'sl';
		}else
		{
			$lang = 'en';
		}
		$uri = '?';
		foreach ( $_GET as $var => $val )
		{
			if ( $var == 'lang' )
			{
				continue;
			}
			$uri .= "$var=$val&";
		}
		$uri .= "lang=$lang";
		$uri = $security->append_sid( $uri );
		$L_uri = ( $lang == 'sl' ) ? 'SLO' : 'ENG';
		
		$template->assign_vars( array(
			'L_MESTNI_CALENDAR' => $this->lang[ 'Calendar' ],
			'L_MESTNI_LANG' => $L_uri,
			'L_MESTNI_PAGINATION' => $this->lang[ 'Pagination' ],
			'L_MESTNI_MORE' => $this->lang[ 'More' ],
			'L_MESTNI_OPENING' => $this->lang[ 'Opening' ],
			
			'U_MESTNI_HOME' => $security->append_sid( '' ),
			'U_MESTNI_CALENDAR' => $security->append_sid( '?' . MODE_URL . '=calendar' ),
			'U_MESTNI_LANG' => $uri,
			'U_MESTNI_SEARCH' => $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=search' ),
			
			'IMG_MESTNI_LEFT' => $this->leftImg(),
			
			'MESTNI_LANG' => $lang_loader->board_lang
		) );
		$this->menu();
	}
	/*
	* calculates the url for the left-hand image on the page
	*/
	function leftImg()
	{
		global $Cl_root_path4template;
		
		$imgs = array( 'denar', 'zajec', 'arkade', 'vaze', 'pisave' );
		
		return $Cl_root_path4template . 'template/MestniMuzej/images/left_' . $imgs[ array_rand( $imgs ) ] . '.jpg';
	}
	/**
	* defines everything needed for the menu
	*/
	function menu()
	{
		global $template, $security, $lang_loader;
		
		$lang = $lang_loader->board_lang;
		
		// define the array of menus
		$boxes = Array(
			Array(
				'rows' => Array( 
							Array(
								'TITLE' => $this->lang[ 'Menu_poslanstvo' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Poslanstvo' ) :
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Mission%20statement' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_zaposleni' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Zaposleni' ) :
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Employees' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_osebna_izkaznica' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Osebna%20izkaznica' ) : 
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Identification%20card' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_letna_porocila' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Letna%20poročila' ) : 
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Annual%20reports' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_turjaska_palaca' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=1' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=54' ) )
							),
					'bottom' => Array(
						'TITLE' => $this->lang[ 'Menu_o_nas' ],
						'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=O%20nas' ) :
													$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=About%20us' ) 
					)
				),
			Array(
				'rows' => Array( 
							Array(
								'TITLE' => $this->lang[ 'Menu_obrazi_ljubljane' ],
								'URL' => ( $lang == 'sl' ) ? 'http://www.obraziljubljane.si' : 'http://www.obraziljubljane.si/en/main.php',
								'BLANK' => 1 ),
							Array(
								'TITLE' => $this->lang[ 'Menu_aktualne' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=9' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=26' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_gostujoce' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=11' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=28' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_prihajajoce' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=10' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=27' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_arhiv_razstav' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=0' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=0' ) )
							),
					'bottom' => Array(
						'TITLE' => $this->lang[ 'Menu_razstave' ],
						'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=overview&event=0' ) :
													$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=overview&event=0' ) 
					)
				),
			Array(
				'rows' => Array( 
							Array(
								'TITLE' => $this->lang[ 'Menu_za_otroke_in_druzine' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=7' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=24' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_za_odrasle' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=8' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=25' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_za_sole' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=1' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=18' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_posebni_dogodki' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=12' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=29' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_arhiv_dogodkov' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=1' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=1' ) )
							),
					'bottom' => Array(
						'TITLE' => $this->lang[ 'Menu_programi' ],
						'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=overview&event=1' ) :
													$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=overview&event=1' ) 
					)
				),
			Array(
				'rows' => Array( 
							Array(
								'TITLE' => $this->lang[ 'Menu_muzej' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Muzej' ) : 
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Museum' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_muzejska_trgovina' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=store' ) : 
															$security->append_sid( '?' . MODE_URL . '=store' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_kavarna' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=23' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=55' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_oddaja_prostorov' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Oddaja%20prostorov' ) : 
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Renting%20the%20premises' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_galerija_vzigalica' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=7' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=61' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_druge_lokacije' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=13&show=all' ) : 
															$security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=30&show=all' ) )
							),
					'bottom' => Array(
						'TITLE' => $this->lang[ 'Menu_obiscite_nas' ],
						'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Muzej' ) :
													$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Museum' ) 
					)
				),
			Array(
				'rows' => Array( 
							Array(
								'TITLE' => $this->lang[ 'Menu_sporocila_za_medije' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=mediji' ) : 
															$security->append_sid( '?' . MODE_URL . '=mediji' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_fotografije' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Fotografije' ) : 
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Press%20photos' ) ),
							Array(
								'TITLE' => $this->lang[ 'Menu_publikacije_in_katalogi' ],
								'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Publikacije' ) : 
															$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Publications' ) )
							),
					'bottom' => Array(
						'TITLE' => $this->lang[ 'Menu_mediji' ],
						'URL' => ( $lang == 'sl' ) ? $security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Mediji' ) :
													$security->append_sid( '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=Mediji' ) 
					)
				)
			);
		
		// feed them to the template		
		foreach ( $boxes as $box )
		{
			$template->assign_block_vars( 'menurow', '', array(
				'BOTTOM' => ( !empty( $box[ 'bottom' ][ 'URL' ] ) ) ? '<a href="' . $box[ 'bottom' ][ 'URL' ] . '">' . $box[ 'bottom' ][ 'TITLE' ] . '</a>' : $box[ 'bottom' ][ 'TITLE' ],
			) );
			$rows = $box[ 'rows' ];
			foreach ( $rows as $row )
			{
				$template->assign_block_vars( 'menurow.submenurow', '', array(
					'URL' => $row[ 'URL' ],
					'TITLE' => $row[ 'TITLE' ],
					'BLANK' => $row[ 'BLANK' ],
				) );
				$template->assign_switch( 'menurow.submenurow', TRUE );
			}
			$template->assign_switch( 'menurow', TRUE );
		}
	}
	
	
	//
	// End of Plug_MestniMuzej class
	//
}


?>