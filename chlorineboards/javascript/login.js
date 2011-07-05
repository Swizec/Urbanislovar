function getcaptcha()
{
	document.getElementById( "captcha" ).innerHTML = '<b>Loading...</b>';
	x_captcha( returncaptcha );
}

function returncaptcha( get )
{
	document.getElementById( "captcha" ).innerHTML = get;
	document.getElementById( "captchainput" ).value = '';
}