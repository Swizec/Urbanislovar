<div id="wrap">
	<div class="content">
		<!-- IF class ('{ERROR:CLASS}'!='') -->
		<br /><span class="genbig">{ERROR:L_CLASS}: {ERROR:CLASS}</span>
		<!-- END class -->
		<!-- IF funct ('{ERROR:FUNCT}'!='') -->
		<br /><span class="gen">{ERROR:L_FUNCT}: {ERROR:FUNCT}</span>
		<!-- END funct -->
		<!-- IF class ('{ERROR:LINE}'!='') -->
		<br /><span class="gen">{ERROR:L_LINE}: <b>{ERROR:LINE}</b></span>
		<!-- END class -->
		<!-- IF all ('{ERROR:CLASS}'!=''||'{ERROR:FUNCT}'!=''||'{ERROR:LINE}'!='') -->
		<br />
		<!-- END all -->
		<br />
		<span class="genbig">{ERROR:TEXT}</span>
		<span class="genmed">{ERROR:SQLTEXT}</span>
		<br /><br />
		<span class="genmed"><a href="{U_BACK}">{L_BACK}</a></span>
	</div>
</div>