<?php

//use SQLite3;

$sqlite3 = new SQLite3(DB_SQLITE3);
$sqlite3->busyTimeout(60000);

$sql = isset($_REQUEST['sql'])? $_REQUEST['sql'] : '';

?>
{* include theme/{*~THEME}/page-header.php }
<article>
	<form id="sql-form" action="{*~url-base}db" method="post">
		<textarea id="sql-text" name="sql"><?= htmlspecialchars($sql) ?></textarea>

		<nav><!--
			--><a id="sql-exec-query" class="right">{*~exec-query}</a><!--
		--></nav>
	</form>

<?php

function print_table($name,$select=true,$show=true,$drop=true) {
?>
		<tr>
		<td><strong><?= $name ?>: </strong></td>
		<td><!--
<?php
	if($select) {
?>			--><a href="{*~url-base}db?sql=SELECT * FROM <?= $name ?>" class="button">{*~select-table}</a><!--
<?php
	}
	if($show) {
?>			--><a href="{*~url-base}db?sql=SELECT sql FROM sqlite_master WHERE name='<?= $name ?>'" class="button">{*~show-table}</a><!--
<?php
	}
	if($drop) {
?>			--><a href="{*~url-base}db?sql=DROP TABLE <?= $name ?>" class="button">{*~drop-table}</a><!--
<?php
	}
?>		--></td>
		</tr>
<?php
}

$result = $sqlite3->query("SELECT name FROM sqlite_master WHERE type='table'");
if($result) {
?>
	<hr />
	<table id="sql-tables" class="full striped">
		<tr>
		<th colspan="3" align="left">{*~list-tables}</th>
		<th>&nbsp;</th>
		</tr>
<?php
	print_table('sqlite_master',true,false,false);
	for($i=0; $row=$result->fetchArray(SQLITE3_NUM); ++$i)
		print_table($row[0]);
?>
	</table>
<?php
}

if($sql) {

	function write_result($sqlite3,$result,$ind='') {
		if($sqlite3->lastErrorCode()!==0) {
			echo $ind.'{*~error-msg} '.$sqlite3->lastErrorMsg()."<br/>\n";
		} elseif($result) {
			echo "{$ind}<table class=\"sql-table\">\n{$ind}\t<tr>\n";
			for($i=0,$n=$result->numColumns(); $i<$n; ++$i) echo "{$ind}\t<th>".$result->columnName($i)."</th>\n";
			echo "{$ind}\t</tr>\n";
			for($i=0; $row=$result->fetchArray(SQLITE3_NUM); ++$i)
				echo "{$ind}\t<tr><td>".implode('</td><td>',$row)."</td></tr>\n";
			if($i==0) echo "{$ind}\t<tr><td style=\"text-align:center;padding:10px;\" colspan=\"".$n."\">{*~table-empty}</td></tr>\n";
			echo "{$ind}</table>\n";
		}
	}

	$list = explode(';',$sql);
	foreach($list as $statement) {
		$statement = trim($statement);
		if($statement) {
			$result = $sqlite3->query($statement);
			echo "\t<hr />\n\t<p><b>{*~sql-statement} \"".$statement."\"</b></p>\n";
			write_result($sqlite3,$result,"\t");
		}
	}
}

$sqlite3->close();
unset($sqlite3);
$sqlite3 = NULL;

?>
</article>

{* include theme/{*~THEME}/page-footer.php }
