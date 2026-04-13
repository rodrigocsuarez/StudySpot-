<?php
session_start();
session_destroy(); 
header("Location: ../index.php"); // Manda de volta para o mapa
exit();