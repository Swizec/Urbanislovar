<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:             lang_quiz.php[English]                   //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        19th March 2007                        //
//     version:               0.1.0                              //
//                                                               //
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
//                                                               //
// This program is free software; you can redistribute it        //
// and/or modify it under the terms of the GNU General Public    //
// License as published by the Free Software Foundation;         //
// either version 2 of the License, or (at your option)          //
// any later version.                                            //
//                                                               //
///////////////////////////////////////////////////////////////////

//
// the language stuff for the ACP
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'True' ] = 'Res je';
$lang[ 'False' ] = 'Ni res';
$lang[ 'NoScript' ] = 'Za resevanje kviza potrebujes JavaScript';
$lang[ 'Correct' ] = 'Odgovor je bil pravilen';
$lang[ 'Incorrect' ] = 'Odgovor je bil nepravilen';
$lang[ 'First' ] = 'Zacni';
$lang[ 'Explain' ] = 'Ugotovi ali so naslednje trditve pravilne ali napacne';
$lang[ 'Congratz' ] = '</h3>Ali
te je kak&scaron;en odgovor presenetil, ko si primerjal s pravilnim
odgovorom? Veliko ljudi misli, da vedo dovolj o u&#269;inkih alkohola
na telo. Ne pusti, da bi alkohol ogrozil tvoje zdravje in varnost
zaradi nevednosti!<br /><h3>Na vsa vprasanja si odgovoril pravilno. Vpisi svoje podatke.';
$lang[ 'Name' ] = 'Ime';
$lang[ 'Mail' ] = 'E-Mail';
$lang[ 'Again' ] = '</h3>Ali
te je kak&scaron;en odgovor presenetil, ko si primerjal s pravilnim
odgovorom? Veliko ljudi misli, da vedo dovolj o u&#269;inkih alkohola
na telo. Ne pusti, da bi alkohol ogrozil tvoje zdravje in varnost
zaradi nevednosti!<br /><h3>Zal si na nekatera vprasanja odgovoril napacno. Osvezi stran in poskusi ponovno';
$lang[ 'Submitted' ] = 'Tvoji podatki so bili uspesno vneseni.';
$lang[ 'Existant' ] = 'Uporabnik s takim e-mail naslovom je ze vpisan med zmagovalce.';
$lang[ 'Wrong_form' ] = 'Napacno poslana forma';

?>