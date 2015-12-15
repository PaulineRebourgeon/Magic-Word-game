<?php

$weights = array(14, 3, 6, 3, 11, 2, 3, 2, 12, 0, 0, 5, 5, 5, 15, 3, 1, 6, 6, 6, 5, 3, 0, 0, 0, 2);

// meilleur score
$weights = array(28, 0, 12, 0, 22, 0, 0, 0, 24, 0, 0, 8, 8, 8, 30, 0, 0, 12, 12, 12, 8, 0, 0, 0, 0, 0);

// on inclut les consonnes faibles
$weights = array(76, 3, 44, 3, 64, 3, 2, 2, 68, 0, 0, 36, 36, 36, 80, 3, 1, 44, 44, 44, 36, 3, 0, 0, 0, 2);

$alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
$weights = array(28, 1, 12, 1, 22, 1, 1, 1, 24, 0, 0, 8, 8, 8, 30, 1, 1, 12, 12, 12, 8, 1, 0, 0, 0, 1);
$points = array(1, 5, 2, 5, 1, 8, 5, 8, 1, 0, 0, 3, 3, 3, 1, 5, 10, 2, 2, 2, 3, 5, 0, 0, 0, 8);
?>