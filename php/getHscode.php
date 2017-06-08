<?php
require_once("services/hscodeService.php");
$hscodeService = new hscodeService();
$res = $hscodeService->getHSCode();
?>
