<!-- IF level ({LEVEL}=0) -->
<form method="POST" action="{S_ACTION}">
 <table cellspacing="2" cellpadding="3" border="0" align="center" width="80%">
  <tr>
   <td colspan="2"><span class="gen" align="center" width="80%">{L_GREET}</span></td>
  </tr>
  <tr>
   <th class="thHead" colspan="2" align="center">{L_SQL}</th>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_DBHOST}</span></td>
   <td width="50%"><input type="text" name="dbhost" length="30" value="localhost"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_DBUSER}</span></td>
   <td width="50%"><input type="text" name="dbuser" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_DBPASS}</span></td>
   <td width="50%"><input type="password" name="dbpass" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_DBNAME}</span></td>
   <td width="50%"><input type="text" name="dbname" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_DBPREF}</span></td>
   <td width="50%"><input type="text" name="dbpref" length="30" value="ClB_"></td>
  </tr>
  
  <tr>
   <th class="thHead" colspan="2" align="center">{L_CHC}</th>
  </tr>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_CHCIP}</span></td>
   <td width="50%"><input type="text" name="chcip" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_CHCPORT}</span></td>
   <td width="50%"><input type="text" name="chcport" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_CHCTYPE}</span></td>
   <td width="50%">
    <select name="chctype">
     <option value="disk">{L_CHCDSK}</option>
     <option value="mem" selected>{L_CHCMEM}</option>
    </select>
   </td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_CHCENABLE}</span></td>
   <td width="50%"><input type="checkbox" value="yes" name="chcenable" checked></td>
  </tr>
  
  <tr>
   <th class="thHead" colspan="2" align="center">{L_SRV}</th>
  </tr>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_SRVDOMAIN}</span></td>
   <td width="50%"><input type="text" name="srvdomain" length="30" value="{DOMAIN}"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_SRVPATH}</span></td>
   <td width="50%"><input type="text" name="srvpath" length="30" value="/"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_SRVEXP}</span></td>
   <td width="50%"><input type="text" name="srvexp" length="30" value="{EXPIRE}"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_SRVSEC}</span></td>
   <td width="50%"><input type="checkbox" name="srvsec" value="yes"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_SRVNAME}</span></td>
   <td width="50%"><input type="text" name="srvname" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_SCRPATH}</span></td>
   <td width="50%"><input type="text" name="scrpath" length="30" value="{PATH}"></td>
  </tr>
  
  <tr>
   <th class="thHead" colspan="2" align="center">{L_ADM}</th>
  </tr>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_ADMMAIL}</span></td>
   <td width="50%"><input type="text" name="admmail" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_ADMNAME}</span></td>
   <td width="50%"><input type="text" name="admname" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_ADMPASS1}</span></td>
   <td width="50%"><input type="password" name="admpass1" length="30"></td>
  </tr>
  <tr class="row">
   <td width="50%" align="center"><span class="gen">{L_ADMPASS2}</span></td>
   <td width="50%"><input type="password" name="admpass2" length="30"></td>
  </tr>
  
  <tr class="row">
   <td width="100%" colspan="2" align="center"><input type="submit" value="Submit" name="submit">&nbsp;<input type="reset" value="Reset" name="reset"></td>
  </tr>
 </table>
</form>
<!-- END level -->