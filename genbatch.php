<?php
if(!isset($argv[1])){
	echo "uso: php genbatch.php prefijo start end\n";
	exit;
}

$prefijo=$argv[1];
$start=$argv[2];
$end=$argv[3];

for ($i=$start; $i<=$end ; $i++){
	if($i%2){ // impar
		printf("php pag1.php $prefijo"."/%03d.pgm\n",$i);
	} else {
		printf("php pag2.php $prefijo"."/%03d.pgm\n",$i);
	}
}

?>
