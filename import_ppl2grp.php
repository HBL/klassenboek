<?php
die();
include("import_database.php");

// First get all people
$result = mysql_query("SELECT * FROM ppl") or die(mysql_error());

$ppl = array();
while ($row = mysql_fetch_object($result)) {
        $ppl[$row->login] = $row->ppl_id;
}

// Get all groups
$result = mysql_query("SELECT * FROM grp") or die(mysql_error());

$grp = array();
while ($row = mysql_fetch_object($result)) {
        $grp[$row->naam] = $row->grp_id;
}


 $list = file('ppl2grp.csv');
        foreach ($list as $value) {
                list($group, $leerling) = explode(';', $value);
                $group = trim($group);
                $leerling = trim($leerling);
echo $value."<br>";
                $result = mysql_query("SELECT * FROM ppl2grp WHERE ppl_id=".$ppl[$leerling]." AND grp_id=".$grp[$group]);
                if (mysql_num_rows($result) == 0) {
                        $query = "INSERT INTO ppl2grp (ppl_id, grp_id) VALUES (".$ppl[$leerling].", ".$grp[$group].")";
                        mysql_query($query);
                        echo $query.'<br>';
                } 
                echo mysql_error();
        }


