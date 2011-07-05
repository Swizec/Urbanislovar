				
				<!-- BEGIN dirrow.filerow -->
					<div id="file_{dirrow.filerow.ID}" name="file_{dirrow.filerow.ID}" class="filerow" {dirrow.filerow.INFO} >
						<div class="dirrow.filerow2">
							<img src="{ROOT_PATH}template/TagCloud/images/{dirrow.filerow.ICON}" name="icon_{dirrow.filerow.ID}" class="icon" />
						</div>
						<div class="Shadow" id="shadow_{dirrow.filerow.ID}" name="shadow_{dirrow.filerow.ID}"><img src="{ROOT_PATH}template/TagCloud/images/{dirrow.filerow.SHADOW_ICON}" /></div>
						<div id="file_{dirrow.filerow.ID}Tip" class="fileTip">
<!-- 							<span class="info"> -->
								{$ UNEMPTY name {dirrow.filerow.TIP:NAME} $}
									<b id="documentNameTip_{dirrow.filerow.ID}">{dirrow.filerow.TIP:NAME}</b><br />
								{$ END name $}
								{$ UNEMPTY lang {dirrow.filerow.TIP:LANGUAGE} $}
									<b>{LANG:TAGCLOUD:INFO:LANGUAGE}: </b>{dirrow.filerow.TIP:LANGUAGE}<br />
								{$ END lang $}
								{$ UNEMPTY words {dirrow.filerow.TIP:WORDS} $}
									<b>{LANG:TAGCLOUD:INFO:WORDS}: </b>{dirrow.filerow.TIP:WORDS}<br />
								{$ END words $}
								{$ UNEMPTY sentences {dirrow.filerow.TIP:SENTENCES} $}
									<b>{LANG:TAGCLOUD:INFO:SENTENCES}: </b>{dirrow.filerow.TIP:SENTENCES}<br />
								{$ END sentences $}
								{$ UNEMPTY paragraphs {dirrow.filerow.TIP:PARAGRAPHS} $}
									<b>{LANG:TAGCLOUD:INFO:PARAGRAPHS}: </b>{dirrow.filerow.TIP:PARAGRAPHS}<br />
								{$ END paragraphs $}
								{$ UNEMPTY uri {dirrow.filerow.TIP:URI} $}
									<b>{LANG:TAGCLOUD:INFO:URI}: </b>{dirrow.filerow.TIP:URI}<br />
								{$ END uri $}
<!-- 							</span> -->
						</div>
					</div>
					<img src="{ROOT_PATH}template/TagCloud/images/{dirrow.filerow.ICON}" id="replacement_{dirrow.filerow.ID}" class="dirrow.filerow icon replacement" />
					<script language="JavaScript" type="text/javascript">
						directories[ {dirrow.ID} ].fileIds[ {this.INDEX} ] = '{dirrow.filerow.ID}';
						directories[ {dirrow.ID} ].fileHash[ '{dirrow.filerow.ID}' ] = {this.INDEX};
						directories[ {dirrow.ID} ].fileType.push( '{dirrow.filerow.TYPE}' );
					</script>
				<!-- END dirrow.filerow -->