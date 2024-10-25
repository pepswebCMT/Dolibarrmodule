<?php

$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

$action = GETPOST('action', 'aZ09');
$form = new Form($db);

llxHeader("", "Calculatrice", '', '', 0, 0, '', '', '', 'mod-test page-index');

echo '<form method="POST">';
echo 'Nombre 1: <input type="number" name="number1"><br>';
echo 'Nombre 2: <input type="number" name="number2"><br>';
echo '<select name="operation">
    <option value="add">Addition</option>
    <option value="subtract">Soustraction</option>
    <option value="multiply">Multiplication</option>
    <option value="divide">Division</option>
</select><br>';
echo '<input type="submit" name="calculate" value="Calculer">';
echo '</form>';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["calculate"])) {
	$number1 = $_POST["number1"];
	$number2 = $_POST["number2"];
	$operation = $_POST["operation"];
	$result = 0;

	switch ($operation) {
		case "add":
			$result = $number1 + $number2;
			break;
		case "subtract":
			$result = $number1 - $number2;
			break;
		case "multiply":
			$result = $number1 * $number2;
			break;
		case "divide":
			$result = $number2 != 0 ? $number1 / $number2 : "Erreur : division par zéro";
			break;
	}
	echo "<h3>Résultat : $result</h3>";
}

llxFooter();
$db->close();
