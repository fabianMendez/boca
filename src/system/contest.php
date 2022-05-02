<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
// Last modified 05/aug/2012 by cassio@ime.usp.br
//Change list: 
// 02/jul/2006 by cassio@ime.usp.br
// 25/aug/2007 by cassio@ime.usp.br: php initial tag changed to complete form

require 'header.php';

if (isset($_GET["new"]) && $_GET["new"]=="1") {
	$n = DBNewContest();
	ForceLoad("contest.php?contest=$n");
}

if (isset($_GET["contest"]) && is_numeric($_GET["contest"]))
  $contest=$_GET["contest"];
else
  $contest=$_SESSION["usertable"]["contestnumber"];

if(($ct = DBContestInfo($contest)) == null)
	ForceLoad("../index.php");
if ($ct["contestlocalsite"]==$ct["contestmainsite"]) $main=true; else $main=false;

if (isset($_POST["Submit3"]) && isset($_POST["penalty"]) && is_numeric($_POST["penalty"]) && 
    isset($_POST["maxfilesize"]) && isset($_POST["mainsite"]) && isset($_POST['localsite']) &&
    isset($_POST["name"]) && $_POST["name"] != "" && isset($_POST["lastmileanswer"]) && 
    is_numeric($_POST["lastmileanswer"]) && is_numeric($_POST["mainsite"]) && is_numeric($_POST['localsite']) &&
    isset($_POST["lastmilescore"]) && is_numeric($_POST["lastmilescore"]) && isset($_POST["duration"]) && 
    is_numeric($_POST["duration"]) &&
    isset($_POST["startdateh"]) && $_POST["startdateh"] >= 0 && $_POST["startdateh"] <= 23 && 
    isset($_POST["contest"]) && is_numeric($_POST["contest"]) &&
    isset($_POST["startdatemin"]) && $_POST["startdatemin"] >= 0 && $_POST["startdatemin"] <= 59 &&
    isset($_POST["startdated"]) && isset($_POST["startdatem"]) && isset($_POST["startdatey"]) && 
    checkdate($_POST["startdatem"], $_POST["startdated"], $_POST["startdatey"])) {
	if ($_POST["confirmation"] == "confirm") {
		$t = mktime ($_POST["startdateh"], $_POST["startdatemin"], 0, $_POST["startdatem"], 
                             $_POST["startdated"], $_POST["startdatey"]);
		if ($_POST["Submit3"] == "Activate") $ac=1;
		else $ac=0;
		$param['number']=$_POST["contest"];
		$param['name']=$_POST["name"];
		$param['startdate']=$t;
		$param['duration']=$_POST["duration"]*60;
		$param['lastmileanswer']=$_POST["lastmileanswer"]*60;
		$param['lastmilescore']= $_POST["lastmilescore"]*60;
		$param['penalty']=$_POST["penalty"]*60;
		$param['maxfilesize']=$_POST["maxfilesize"]*1000;
		$param['active']=$ac;
		$param['mainsite']=$_POST["mainsite"];
		$param['localsite']=$_POST["localsite"];
		$param['mainsiteurl']=$_POST["mainsiteurl"];

		DBUpdateContest ($param);
		if ($ac == 1 && $_POST["contest"] != $_SESSION["usertable"]["contestnumber"]) {
			$cf = globalconf();
			if($cf["basepass"] == "")
				MSGError("You must log in the new contest. The standard admin password is empty (if not changed yet).");
			else
				MSGError("You must log in the new contest. The standard admin password is " . $cf["basepass"] . " (if not changed yet).");

			ForceLoad("../index.php");
		}
	}
	ForceLoad("contest.php?contest=".$_POST["contest"]);
}
?>
<br>

<form name="form1" class="form" enctype="multipart/form-data" method="post" action="contest.php">
  <input type=hidden name="confirmation" value="noconfirm" />
  <script language="javascript">
    function conf() {
      if (confirm("Confirm?")) {
        document.form1.confirmation.value='confirm';
      }
    }
    function newcontest() {
      document.location='contest.php?new=1';
    }
    function contestch(n) {
      if(n==null) {
        k=document.form1.contest[document.form1.contest.selectedIndex].value;
        if(k=='new') newcontest();
        else document.location='contest.php?contest='+k;
      } else {
        document.location='contest.php?contest='+n;
      }
    }
  </script>
  <br><br>
  <center>
    <?php 
    $cs = DBAllContestInfo();
    $isfake=false;
    for ($i=0; $i<count($cs); $i++) {
      if ($contest == $cs[$i]["contestnumber"]) {
        if($cs[$i]["contestnumber"] == 0) $isfake=true;
      }
    }
    ?>

    <?php if ($isfake) { ?>
      <h2>Select a contest or create a new one.</h2>
    <?php } ?>

    <div class="formgroup" style="max-width: 40vw">
      <label for="contestnumber">Contest number:</label>
      <select id="contestnumber" onChange="contestch()" name="contest">
      <?php 
      for ($i=0; $i<count($cs); $i++) {
        echo "<option value=\"" . $cs[$i]["contestnumber"] . "\" ";
        if ($contest == $cs[$i]["contestnumber"]) {
          echo "selected";
        }
        echo ">" . $cs[$i]["contestnumber"] . ($cs[$i]["contestactive"]=="t"?"*":"") ."</option>\n";
      }
      ?>
        <option value="new">new</option>
      </select>
    </div>
	<?php if(!$isfake) { ?>
      <div class="formgroup">
        <label for="name">Name:</label>
        <input id="name" type="text" <?php if(!$main) echo "readonly"; ?> name="name" value="<?php echo $ct["contestname"]; ?>" size="50" maxlength="50" />
      </div>
      <div class="formgroup">
        <label for="startdatetime">Start time:</label>
        <input type="datetime-local" name="startdate" id="startdate">
      </div>
      <div class="formgroup">
        <td width="35%" align=right>Duration (in minutes):</td>
        <td width="65%">
          <input type="text" name="duration" <?php if(!$main) echo "readonly"; ?> value="<?php echo $ct["contestduration"]/60; ?>" size="20" maxlength="20" />
        </td>
      </div>
      <div class="formgroup">
        <td width="35%" align=right>Stop answering (in minutes):</td>
        <td width="65%">
          <input type="text" name="lastmileanswer" <?php if(!$main) echo "readonly"; ?> value="<?php echo $ct["contestlastmileanswer"]/60; ?>" size="20" maxlength="20" />
        </td>
      </div>
      <div class="formgroup">
        <td width="35%" align=right>Stop scoreboard (in minutes):</td>
        <td width="65%">
          <input type="text" name="lastmilescore" <?php if(!$main) echo "readonly"; ?> value="<?php echo $ct["contestlastmilescore"]/60; ?>" size="20" maxlength="20" />
        </td>
      </div>
      <div class="formgroup">
        <td width="35%" align=right>Penalty (in minutes):</td>
        <td width="65%">
          <input type="text" name="penalty" <?php if(!$main) echo "readonly"; ?> value="<?php echo $ct["contestpenalty"]/60; ?>" size="20" maxlength="20" />
        </td>
      </div>
      <div class="formgroup">
        <td width="35%" align=right>Max file size allowed for teams (in KB):</td>
        <td width="65%">
          <input type="text" name="maxfilesize" <?php if(!$main) echo "readonly"; ?> 
               value="<?php echo $ct["contestmaxfilesize"]/1000; ?>" size="20" maxlength="20" />
        </td>
      </div>
      <p>Your PHP config. allows at most:
        <?php echo ini_get('post_max_size').'B(max. post) and '.ini_get('upload_max_filesize').'B(max. filesize)'; ?>
      </p>
      <div class="formgroup">
        <label for="mainsiteurl">Contest main site URL (IP/bocafolder):</label>
        <input type="text" id="mainsiteurl" name="mainsiteurl" value="<?php echo $ct["contestmainsiteurl"]; ?>" size="40" maxlength="200" />
      </div>
      <div class="formgroup">
        <label for="mainsite">Contest main site number:</label>
        <input type="text" name="mainsite" value="<?php echo $ct["contestmainsite"]; ?>" size="4" maxlength="4" />
      </div>
      <div class="formgroup">
        <label for="localsite">Contest local site number:</label>
        <input type="text" id="localsite" name="localsite" value="<?php echo $ct["contestlocalsite"]; ?>" size="4" maxlength="4" />
      </div>
    </table>
  </center>
  <div class="formgroup">
      <input type="submit" name="Submit3" value="Send" onClick="conf()">
      <input type="submit" name="Submit3" value="Activate" onClick="conf()">
      <input type="reset" name="Submit4" value="Clear">
  </div>
  <?php }?>
</form>

</body>
</html>
