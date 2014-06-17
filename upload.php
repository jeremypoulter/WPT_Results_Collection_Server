<?php
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: web-platform.test");

$data = "no data";

if (isset($_POST["file"]) && isset($_POST["log"])) {
  $file = $_POST["file"];
  $data = $_POST["log"];

  if (preg_match('/^[0-9a-km-z]{6}$/', $file)) {

    $fp = fopen("./logs/$file.log", "a");
    if (!$fp) {
      echo "Not able to open file: $file";
      return;
    }

    fwrite($fp, $data, strlen($data));
    fclose($fp);
  }
} else {
  echo "Missing: file or log parameter";
}
?>
<?php
echo "Uploaded to: $file.log";
?>
