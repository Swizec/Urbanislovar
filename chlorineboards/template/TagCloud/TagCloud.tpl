<script language="JavaScript" type="text/javascript">
	if ( BrowserDetect.browser != 'Opera' )
	{
		browserWarn( '{L_BROWSER_WARN}' );
	}
</script>

<!-- BEGIN login -->
	<div id="wrap">
		<div class="content">
			<form action="{login.S_ACTION}" method="POST">
				{login.L_USERNAME}: <input type="text" name="meow" /><br />
				{login.L_PASSWORD}: <input type="password" name="purr" /><br />
				<p class="submit"><input type="submit" name="kitten!" value="{L_SUBMIT}" /></p>
			</form>
		</div>
	</div>
<!-- END login -->

<!-- BEGIN interface -->
	<div id="wrap" >
		<div class="content content_full">
			<p style="text-align: center; font-weight: bold">This is pre alpha software, please do not expect too much polish and report any odd behaviour.</p>
			<div class="fileStuff">
				<iframe name="fileframe"></iframe>
				<div id="filePad">
					<div id="fileinfo" onclick="this.style.display = 'none'"></div>
					<span id="beforeThis"></span>
				</div>
				<form id="fileinput" method="POST" enctype="multipart/form-data" action="{interface.S_FILE_ACTION}" target="fileframe">
					<input type="file" name="file" onchange="fileUpload( '{ROOT_PATH}', '{interface.S_TIME}' );" />
					<input type="hidden" name="time" value="{interface.S_TIME}" />
				</form>
				<form id="uriinput" action="#" method="POST" onsubmit="return uriAdd( '{ROOT_PATH}', '{interface.S_TIME}' )">
					<input type="text" name="uri" id="uri" />
					<div class="submit" id="submit"><input type="submit" value="{LANG:TAGCLOUD:INTERFACE:URIADD}" /></div>
				</form>				
			</div>
			<form action="{interface.S_ACTION}" method="POST" id="mainform" onsubmit="return isUploaded();">
				<input type="hidden" name="time" value="{interface.S_TIME}" />
				<span>
					<br />
					<div onclick="getStyleObject( 'text' ).display = 'block'" style="cursor: pointer"><a>{interface.L_TEXT}</a></div><br /> <textarea name="text" id="text"></textarea><br />
				</span>
				<p class="submit" id="submit"><input type="submit" /></p>
			</form>
			<div class="center">
				{$ UNEMPTY direct {interface.SD_TIME} $}
					<a href="{interface.U_DIRECT}">{interface.L_DIRECT}</a>
				{$ END direct $}
			</div>
		</div>
	</div>
	<div id="blackDim" name="blackDim"><br /></div>
	<div id="uriShow" name="uriShow"><iframe id="uriWindow"></iframe></div>
<!-- END interface -->

<!-- BEGIN loading -->
	<script language="JavaScript" type="text/javascript">
		setTimeout( 'Tagcheck_done( '+{loading.TIME}+' )', 5000 );
		setTimeout( 'x_funny_image( Tagfunny )', 1000 );
	</script>
	<div id="wrap">
		<div class="content">
			<img src="{ROOT_PATH}template/TagCloud/images/loading.gif" /><br /><br />
			{loading.L_LOADING}
			<div id="funny_image">
			</div>
		</div>
	</div>
<!-- END loading -->

<!-- BEGIN display -->
	<script language="JavaScript" type="text/javascript">
		var rootPath = '{ROOT_PATH}';
		var execTime = {display.TIME};
		var fileIds = new Array();
		var fileHash = new Array();
		var LsessionRenamed = '{display.L_SESSION_RENAMED}';
		var LdocumentRenamed = '{LANG:TAGCLOUD:SESSION:RENAMEDDOC}';
		var LtextpadRenamed = '{LANG:TAGCLOUD:TEXTPAD:RENAMED}';
		var Loading = '{display.L_LOADING}';
		var directories = new Array();
	</script>
	<div class="obstructor" name="obstructor"><br /></div>
	<div id="container">
		<div id="leftColumn">
			<img src="{ROOT_PATH}template/TagCloud/images/uparrow.png" id="upArr" />
			<img src="{ROOT_PATH}template/TagCloud/images/downarrow.png" id="downArr" />
		</div>
		<div id="leftColumn2" onmouseover="scrollIcons();" onmouseout="scrollStop()">
			<!-- BEGIN dirrow -->
				<div id="dir_{dirrow.ID}" name="dir_{dirrow.ID}" class="fileRow dir" {dirrow.INFO} onclick="swapDir( {dirrow.ID} )">
					<img src="{ROOT_PATH}template/TagCloud/images/{dirrow.ICON}" />
					<div id="dir_{dirrow.ID}Tip" class="dirTip">
<!-- 						<span class="info"> -->
							<b>{dirrow.TIP:TIME}</b><br />
							<b>{dirrow.TIP:L_NAME}: </b><span id="sessionNameTip_{dirrow.ID}">{dirrow.TIP:NAME}</span><br />
							<b>{dirrow.TIP:L_FILES}: </b><span id="sessionFilesTip_{dirrow.ID}">{dirrow.TIP:FILES}</span><br />
<!-- 						</span> -->
					</div>
				</div>
				<script language="JavaScript" type="text/javascript">
					directories[ {this.INDEX} ] = new dirObj( {dirrow.TIME} );
				</script>
				{$ INCLUDE TagCloud_filerow $}
				<div id="endOfDir_{dirrow.ID}" class="endOfDir"></div>
			<!-- END dirrow -->
		</div>
		<div id="workArea">
				<!-- IF show ({display.U_IMAGE}!='') -->
				<img src="{ROOT_PATH}/{display.U_IMAGE}" id="image" name="image" />
				<!-- END show -->
		</div>
		<div id="workArea2" name="workArea2" onclick="showAdvanced()">
		{display.CLICKIES}
		</div>
		<div id="advanced" name="advanced">
			<a href="" onclick="exportImage( '{display.EXPORT:URL1}', {display.EXPORT:TIME1}, 1 ); return false"><img src="{ROOT_PATH}template/TagCloud/images/image_22.png" {display.EXPORT:TOOL1} /></a>
			<a href="" onclick="exportImage( '{display.EXPORT:URL2}', {display.EXPORT:TIME2}, 2 ); return false"><img src="{ROOT_PATH}template/TagCloud/images/image_32.png" {display.EXPORT:TOOL2} /></a>
			<a href="" onclick="exportImage( '{display.EXPORT:URL3}', {display.EXPORT:TIME3}, 3 ); return false"><img src="{ROOT_PATH}template/TagCloud/images/image_56.png" {display.EXPORT:TOOL3} /></a>
			<a href="" onclick="exportImage( '{display.EXPORT:URL4}', {display.EXPORT:TIME4}, 4 ); return false"><img src="{ROOT_PATH}template/TagCloud/images/image_72.png" {display.EXPORT:TOOL4} /></a>
			<a href="" onclick="exportImage( '{display.EXPORT:URL5}', {display.EXPORT:TIME5}, 5 ); return false"><img src="{ROOT_PATH}template/TagCloud/images/image_96.png" {display.EXPORT:TOOL5} /></a>
			<hr />
			<div id="advancedword">
				<a href="" onclick="analyzeWord( false ); return false">{display.L_ANALYZE} "<b id="advancedWordB"></b>"</a>
			</div>
			<div id="cluster">
				<strong>Cluster:</strong><input type="text" id="clusterIn" onkeydown="enterCluster()" /><img src="{ROOT_PATH}template/TagCloud/images/ok.png" onclick="analyzeWord( true )" />
			</div>
			<div id="moreCommands">
				<img src="{ROOT_PATH}template/TagCloud/images/remove.png" {display.ADVTOOLS:REMOVE} onclick="removeDocument()" />
				<img src="{ROOT_PATH}template/TagCloud/images/renameDocument.png" {display.ADVTOOLS:RENAME2} onclick="renameDocument()" />
				<img src="{ROOT_PATH}template/TagCloud/images/spacer.png" />
				<img src="{ROOT_PATH}template/TagCloud/images/save2.png" {display.ADVTOOLS:SAVE2} onclick="saveSession()" />
				<img src="{ROOT_PATH}template/TagCloud/images/remove2.png" {display.ADVTOOLS:REMOVE2} onclick="removeSession()" />
				<img src="{ROOT_PATH}template/TagCloud/images/rename.png" {display.ADVTOOLS:RENAME} onclick="renameSession()" />
				<img src="{ROOT_PATH}template/TagCloud/images/spacer.png" />
				<img src="{ROOT_PATH}template/TagCloud/images/textpad.png" {display.ADVTOOLS:TEXTPAD} onclick="newTextPad()" />
				<img src="{ROOT_PATH}template/TagCloud/images/newscrap.png" {display.ADVTOOLS:SCRAPBOOK} onclick="newScrapBook()" />
			</div>
			<div id="changeName">
				<input type="text" id="sessionName" onkeydown="renameSessionS()" /><img src="{ROOT_PATH}template/TagCloud/images/ok.png" onclick="renameSession3()" /><img src="{ROOT_PATH}template/TagCloud/images/cancel.png" onclick="renameSessionC()" />
			</div>
			<div id="changeName2">
				<input type="text" id="documentName" onkeydown="renameDocumentS()" /><img src="{ROOT_PATH}template/TagCloud/images/ok.png" onclick="renameSession3()" /><img src="{ROOT_PATH}template/TagCloud/images/cancel.png" onclick="renameDocumentC()" />
			</div>
		</div>
		<iframe name="iframe" id="iframe"></iframe>
		<div id="analysis" name="analysis">
		</div>
		<div id="Tip" name="Tip">
			<img src="{ROOT_PATH}template/TagCloud/images/close.png" style="float: right; cursor: pointer; margin: 5px; margin-bottom: -100%" onclick="closeWhatever()" />
			<h1>{display.L_QUICK}</h1>
			<p><numbering>1: </numbering>{display.L_INSTRUCTIONS1}</p>
			<p><numbering>2: </numbering>{display.L_INSTRUCTIONS2}</p>
			<p><numbering>3: </numbering>{display.L_INSTRUCTIONS3}</p>
		</div>
		<div id="messaging" name="messaging">
			
		</div>
	</div>
	<div class="obstructor" id="bottomObstruct"><br /></div>
	<div id="blackDim" name="blackDim" onclick="closeWhatever()"><br /></div>
	<div id="Loading" name="Loading"><img src="{ROOT_PATH}template/TagCloud/images/loading.gif" /></div>
<!-- END display -->
