<?php

require("cleanEncoding.php");

$str1 = "Minha string bem comum";

$str2 = "Mamãe marcou um golaço";

$unmessed1 = unmessEncoding($str1);
$unmessed2 = unmessEncoding($str2);

echo "\nSTR1:\n";
echo "Normal: $str1\nUnmessed: $unmessed1\n\n";
echo "STR2:\n";
echo "Normal: $str2\nUnmessed: $unmessed2\n\n";
