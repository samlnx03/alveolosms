<?php

// adaptarse a las rotaciones
// buscar marca de tiempo start
// determinar el centro y descender por la vertical ajustando en cada subsiguiente marca de tiempo
//
// usa kernel sobel para determinar el gradiente   FRACASO
//
// implementar busqueda horizontal a la izquierda desde los centros de la marca de tiempo
// identifica numero de pregunta, numero de grupo, y centros de alveolos
// hay un detalle con la pregunta 151 y 152 que no existen
//
$NOINFO=0; $RESULTADOS=1; $CONFIRMACIONES=1; $ERRORES=2; $ADVERTENCIAS=3; 
$DEPURACION_BAJA=4; $DEPURACION_MEDIA=5; $DEPURACION_ALTA=6;

//$DEPURANDO=$DEPURACION_MEDIA;
//$DEPURANDO=$ADVERTENCIAS;
$DEPURANDO=$RESULTADOS;

$RELLENO=22*22*127;     // umbral para determinar alveolo relleno

//$filename='Imagen2.bmp';
if(!isset($argv[1])){
	print "Sintaxis: php ".$argv[0]." [debug level (0-6)]\n";
	exit(1);
}
$filename=$argv[1];

if(isset($argv[2])){
	$DEPURANDO=$argv[2]+0;
}

$image = new Imagick();
$image->readImage($filename);
$height=$image->getImageHeight();
$width = $image->getImageWidth();

$pixeles = $image->exportImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR);
$pixelesdebug=$image->exportImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR);

/*
es_ruido(704,1088);
exit;
 */

if($DEPURANDO>=$DEPURACION_BAJA) echo "Buscando centro de marcas de tiempo (rectangulos) del lado derecho PRIMERA\n";
$x=1580;   // 14 de la mitad derecha del alveolo izquierdo, 38 del espacio entre alveolos
$y=160;
$mt0=marcatiempo($x,$y);

$x=$mt0['x'];
$y=$mt0['y'];
for($n=1; $n<=24; $n++){
	// ubicar en el centro y descender
	if($DEPURANDO>=$DEPURACION_BAJA) echo "Buscando centro de marcas de tiempo (rectangulos) del lado derecho PREG $n\n";
	for(;;$y++){
		// buscar un blanco (parte inferior de recytangulo actual
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		//if($DEPURANDO>=$DEPURACION_ALTA) print("\ty=$y ");
		if($pixeles[$offsetprgb]>200)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
			break;
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	if($DEPURANDO>=$DEPURACION_ALTA) {
		print "terminan negros en x:$x, y:$y\n";
		print("buscando blancos\n");
	}
	for(;;$y++){
		// buscar un blanco (parte inferior de rectangulo actual, rumbo a siguiente marca de tiempo)
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
	        $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		if($pixeles[$offsetprgb]<50)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
			break;
		$pixelesdebug[$offsetprgb]=0;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=255;
	}
	$mt[$n]['ysup']=$y;
	$y+=6;  // posible centro de la siguiente marca de tiempo
	if($DEPURANDO>=$DEPURACION_MEDIA) print "centro posible en $x,$y   ";
	$sinccol=array('x'=>$x, 'y'=>$y);
	$sinccol=ajusta_centro_rectangulo_desde_centro($sinccol);
	if($DEPURANDO>=$DEPURACION_BAJA) print "Preg $n: centro ajustado a ".$sinccol['x'].",".$sinccol['y']."\n";
	$x=$sinccol['x']; $y=$sinccol['y'];
	$mt[$n]['x']=$x;
	$mt[$n]['y']=$y;
}


//-------------------------------------------------------
// BUSQUEDA A LA IZQUIERDA DEL PRIMER ALVEOLO
//
for($preg=129,$nr=1; $nr<=24; $nr++,$preg+=97){  // numero de renglon, no exiten la preg 151 y 152 (renglon 23 y 24, grupo de mas a la derecha)
   $x=$mt[$nr]['x'];	// centros de marca de tiempo
   $y=$mt[$nr]['y'];
   $pxy=salir_de_la_marca_de_tiempo($x,$y);
   for($gpo=1; $gpo<=4; $gpo++,$preg-=24){
	if($DEPURANDO>=$DEPURACION_MEDIA) echo "GRUPO $gpo, Buscando 1er alveolo a la izq de la marca de tiempo\n";
	$pxy=encontrar_alveolo_a_la_izq($pxy[0]-10,$pxy[1]);
	//if($preg>150 AND $pxy[0]<1200){ // no exiten alveolos en el grupo de mas a la derecha, prego 150 y 151
	if($preg>150 AND $pxy[0]<1480){ // no exiten alveolos en el grupo de mas a la derecha, prego 150 y 151
		$preg=$preg-24;
		$gpo++;
	}
	if($DEPURANDO>=$DEPURACION_MEDIA) print "extremo derecho 1er alveolo en ".$pxy[0].",".$pxy[1]."\n";
	$centroxy['x']=$pxy[0]-15;
	$centroxy['y']=$pxy[1];
	//verificar_tipo_alveolo($centroxy);
	if($DEPURANDO>=$DEPURACION_MEDIA) print "centro posible en ".$centroxy['x'].",".$centroxy['y']."\n";
	$centroxy=ajusta_centro($centroxy);
	if($DEPURANDO>=$DEPURACION_MEDIA) print "centro corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
		//-----------------------------------
		$alveolos[$preg][(5-1)]=$centroxy;
		//-----------------------------------
	//plot_alveolo($centroxy,28,28);
	//if($DEPURANDO>=$DEPURACION_BAJA) print "renglon $nr, grupo $gpo, alveolo 1,  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
		if($DEPURANDO>=$DEPURACION_MEDIA) print "pregunta $preg\n";
	if($DEPURANDO>=$DEPURACION_MEDIA) print "*renglon ".($nr-1).", grupo ".(5-$gpo).", alveolo ".(5-1).",  centro en ".$centroxy['x'].",".$centroxy['y']."\n";

	//for($n=2; $n<=4; $n++){
	for($n=2; $n<=5; $n++){ // 5 alveolos en media superior
		// siguiente alveolo  n
		$centroxy['x']=$centroxy['x']-15-37-15;  // del centro 15 para salir del alveolo, 37 entre alveolos y 15 al nuevo centro
		//verificar_tipo_alveolo($centroxy);
		if($DEPURANDO>=$DEPURACION_MEDIA) print "alveolo $n centro posible en ".$centroxy['x'].",".$centroxy['y']."\n";
		$centroxy=ajusta_centro($centroxy);
		if($DEPURANDO>=$DEPURACION_MEDIA) print "alveolo $n centro corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
		//-----------------------------------
		$alveolos[$preg][(5-$n)]=$centroxy;
		//-----------------------------------
		//plot_alveolo($centroxy,28,28);
		//if($DEPURANDO>=$DEPURACION_BAJA) print "renglon $nr, grupo $gpo, alveolo $n,  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
		if($DEPURANDO>=$DEPURACION_MEDIA) print "pregunta $preg\n";
		if($DEPURANDO>=$DEPURACION_MEDIA) print "*renglon ".($nr-1).", grupo ".(5-$gpo).", alveolo ".(5-$n).",  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
	}
	//printf("termino el encontrar alveolos del grupo $gpo\n");
   	//exit;
	//$x=$centroxy['x']-165;  // del centro de la resp 4 del grupo de la derecha a la respuesta D, punto de inicio de busqueda
	//$y=$centroxy['y'];
	//$pxy[0]=$centroxy['x']-165;  // del centro de la resp 4 del grupo de la derecha a la respuesta D, punto de inicio de busqueda
	$pxy[0]=$centroxy['x']-75;  // del centro de la resp 4 del grupo de la derecha a la respuesta D, punto de inicio de busqueda
	$pxy[1]=$centroxy['y'];
	if($DEPURANDO>=$DEPURACION_MEDIA) print "\n";
   }  // sig gpo
}

if($DEPURANDO>=$DEPURACION_MEDIA){
// impresion de centros de alveolos ordenados por pregunta
for($np=57; $np<=150; $np++){
	printf("pregunta $np, ");
	for($alv=0; $alv<=3; $alv++){
		printf(" alveolo $alv, centro en (%d,%d)",$alveolos[$np][$alv]['x'],$alveolos[$np][$alv]['y']);
		plot_alveolo($alveolos[$np][$alv],10,10);
	}
	echo "\n";
}
}

for($np=57; $np<=150; $np++){
        $respuesta[$np]=0;
        for($opcion=0; $opcion<=3; $opcion++){
                $x=$alveolos[$np][$opcion]['x'];
                $y=$alveolos[$np][$opcion]['y'];
		///$gris=convolv($x,$y);
		$gris=gris_alveolo($x,$y,28,28);
                if($gris<$RELLENO)
                        //$respuesta[$np][$opcion]=1;
                        $respuesta [$np] = $respuesta[$np] | pow (2, $opcion);
                //else
                //      $respuesta[$np][$opcion]=0;
                if($DEPURANDO>=$DEPURACION_BAJA){
                        echo "preg $np, opcion $opcion ($x,$y) gris:$gris ";
                        if($gris<$RELLENO){
				plot_alveolo($alveolos[$np][$opcion],40,40);
				echo "RELLENADO\n";
			}else
				print "\n";
                }
        }
}

function log2 ($x) {
        return (log10 ($x) / log10 (2));
}

if($DEPURANDO>=$DEPURACION_BAJA){
for($np=57; $np<=150; $np++){
	printf("*** preg $np:");
	printf("%d",$respuesta[$np]);
	echo "\n";
}
}
else if($DEPURANDO>=$RESULTADOS){
	for($np=57; $np<=100; $np++){
	//for($np=57; $np<=120; $np++){
                $r = $respuesta[$np] & 15;
                //for ($cont = 0, $b=0; $b < 4; $b++)
                for ($cont = 0, $b=0; $b < 5; $b++)
                        if ($r & pow (2, $b))
                                $cont++;
                if ($cont == 0)
                        printf (" ");
                else
                        printf ("%c", log2 ($respuesta[$np]) + 65);
        }
        echo "\n";
}

/*
// linea horizontal en la direccion de la marca de tiempo
for($n=1; $n<=24; $n++){
   $x=$mt[$n]['x'];
   $y=$mt[$n]['y'];
   //$d=$mt[$n]['dir'];
   // linea por la horizontal
   for($x1=$x;$x1>10;$x1--){
		// buscar un blanco (parte inferior de rectangulo actual, rumbo a siguiente marca de tiempo)
	        $offsetp = $y*$width + $x1;  // en el arreglo lineal
	        $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$pixeles[$offsetprgb]=0;
		$pixeles[$offsetprgb+1]=255;
		$pixeles[$offsetprgb+2]=0;
   }
}
*/

if($DEPURANDO>=$DEPURACION_BAJA) {
	debug_image();
}


exit;
/// --------------------------------------------------------------------------------------------------------------------
?>
<?php
function marcatiempo($x,$y){
	//investigar por las columnas a la vez para encontrar el borde izquierdo
	//al encontrar un negro investigar a la derecha al menos 28 pixeles
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width, $pixeles, $pixelesdebug;
	$scx=$x; $scy=$y;
	for($x=$scx; $x<=$scx+70; $x++){  // antes 90, luego 40, 
		for($y=$scy; $y<=$scy+40; $y++){  // antes 30
			$offsetp = $y*$width + $x;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			if($pixeles[$offsetprgb]<100){
				// tal vez se encontro borde izquierdo
				if(negros_a_la_derecha($x,$y)<24*50) { // rectangulo 12 alto x 30 ancho  de 50 de gris
					$sinccol=array('x'=>$x+15, 'y'=>$y+6);
					break 2;
				}
			}
		}
	}
	if(!isset($sinccol)){
		echo "No se encontro marca de sincronizacion de tiempo\n";
		exit;
	}
	if($DEPURANDO>=$DEPURACION_ALTA){
	print "\tOk, posible marca de sinc de tiempo (borde mas a la izquierda) en $x,$y\n";
	print "\tPosible Centro en ".$sinccol['x'].",".$sinccol['y']."\n";
	}
	$sinccol=ajusta_centro_rectangulo($sinccol);
	if($DEPURANDO>=$DEPURACION_ALTA) print "Centro de sinc de col corregido en ".$sinccol['x'].",".$sinccol['y']."\n";
	return $sinccol;
}


?>



<?php
function ajusta_centro_rectangulo_desde_centro($sinccol){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	$sumanegros=30*12*255; // too high to force first change, rectangulo de 30 de ancho x 12 de alto
	$centroX=$sinccol['x']; $centroY=$sinccol['y'];
	for($x=$sinccol['x']-15; $x<$sinccol['x']+15; $x++){
		for($y=$sinccol['y']-6; $y<$sinccol['y']+6; $y++){
			$sn=convolv_rect($x,$y);
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\tprobando centro en $x, $y: suma $sn ";
			if($sn<$sumanegros){
				$sumanegros=$sn;
				$centroX=$x; $centroY=$y;
				if($DEPURANDO>=$DEPURACION_ALTA) echo "minimo hasta el momento";
			}
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\n";
		}
	}
	return array('x'=>$centroX, 'y'=>$centroY);
}

function ajusta_centro_rectangulo($sinccol){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	$sumanegros=30*12*255; // too high to force first change, rectangulo de 30 de ancho x 12 de alto
	$centroX=$sinccol['x']; $centroY=$sinccol['y'];
	for($x=$sinccol['x']-5; $x<$sinccol['x']+5; $x++){
		for($y=$sinccol['y']-3; $y<$sinccol['y']+3; $y++){
			$sn=convolv_rect($x,$y);
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\tprobando centro en $x, $y: suma $sn ";
			if($sn<$sumanegros){
				$sumanegros=$sn;
				$centroX=$x; $centroY=$y;
				if($DEPURANDO>=$DEPURACION_ALTA) echo "minimo hasta el momento";
			}
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\n";
		}
	}
	return array('x'=>$centroX, 'y'=>$centroY);
}
function ajusta_centro($sinccol){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	$sumanegros=28*28*255; // too high to force first change, alveolo de 28x28
	$centroX=$sinccol['x']; $centroY=$sinccol['y'];
	for($x=$sinccol['x']-3; $x<$sinccol['x']+3; $x++){
		for($y=$sinccol['y']-3; $y<$sinccol['y']+3; $y++){
			$sn=convolv($x,$y);
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\tprobando centro en $x, $y: suma $sn ";
			if($sn<$sumanegros){
				$sumanegros=$sn;
				$centroX=$x; $centroY=$y;
				if($DEPURANDO>=$DEPURACION_ALTA) echo "minimo hasta el momento";
			}
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\n";
		}
	}
	return array('x'=>$centroX, 'y'=>$centroY);
}

function convolv_rect($x,$y){  // convolucion para rectangulo de tiempos
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-17; $xi<$x+17; $xi++){  // alveolo de 28*28
		for($yi=$y-8; $yi<$y+8; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
		}
	}
	return $sumapix;
}

function convolv($x,$y){
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-14; $xi<$x+14; $xi++){  // alveolo de 28*28
		for($yi=$y-14; $yi<$y+14; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
		}
	}
	return $sumapix;
}

function gris_alveolo($x,$y,$w,$h){
	global $width,$pixeles;
	$sumapix=0;
	$xizq=$x-14+3;   // alveolo de 28, la mitad 14, 3 para no meter blancos de las esquinas
	$xder=$x+14-3;
	$ysup=$y-14+3;
	$yinf=$y+14-3;
	for($xi=$xizq; $xi<=$xder; $xi++){  // alveolo de 28*28
		for($yi=$ysup; $yi<=$yinf; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
		}
	}
	return $sumapix;
}

function negros_a_la_derecha($x, $y){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width;
	$offsetp = $y*$width + $x;  // en el arreglo lineal
	$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
	$negr = _negros_a_la_derecha($offsetprgb);
	if($DEPURANDO>=$DEPURACION_ALTA) printf("\tNegros a la derecha de coord %d,%d: %d\n",$x,$y,$negr);
	return $negr;
}
function _negros_a_la_derecha($offsetprgb){
	global $pixeles;
	$negros=0;
	for($n=0; $n<=28; $n++){
		$negros+=$pixeles[$offsetprgb];
		$offsetprgb+=3;
	}
	//if($DEPURANDO>=$DEPURACION_ALTA) print "\t$negros\n";
	return $negros;
}

?>

<?php
// -----------------------------------------------------------------------   busqueda a la izquierda
function salir_de_la_marca_de_tiempo($x,$y){
	$pxy=array($x,$y);
	$pxy=avanzar_sobre_negros_a_la_izq($pxy); // salir de la marca de tiempo
	return $pxy;
}

function encontrar_alveolo_a_la_izq($x,$y){ // desde un punto blanco a la derecha del alveolo
	$pxy=array($x,$y);
	$pxy=avanzar_sobre_blancos_a_la_izq($pxy); // encontrar alveolo
	return $pxy;
}
function avanzar_sobre_negros_a_la_izq($pxy){
	$pxy= _avanzar_izquierda($pxy,"GT",150);  // avanzar izq y detenerse cuando gris>200
	return $pxy;
}	
function avanzar_sobre_blancos_a_la_izq($pxy){
	$pxy= _avanzar_izquierda($pxy,"LT",200);  // avanzar izq y detenerse cuando gris<50, 2a opcion  127, funciona con 200 pero checar ruido
	return $pxy;
}	


function _avanzar_izquierda($pxy,$cond,$color){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles,$pixelesdebug;

	if($DEPURANDO>=$DEPURACION_ALTA) echo "Avanzando a la izq desde ".$pxy[0].",".$pxy[1]." Detener cuando gris $cond $color\n";
	$x=$pxy[0];
	$y=$pxy[1];
	for(;;$x--){
		// buscar un blanco a la izquierda
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$colorpix=$pixeles[$offsetprgb];
		if($DEPURANDO>=$DEPURACION_ALTA) print("\tx=$x color=$colorpix\n");
		if($cond=='LT'){
			if($pixeles[$offsetprgb]<$color){ // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				if(es_ruido($x,$y)<$color){
					break;
				}
			}
		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_BAJA) print("\t\trojo en $x,$y\n");
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	return array($x,$y);
}
function es_ruido($x,$y){
        global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS,
               $DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-1; $xi<=$x; $xi++){  // alveolo de 28*28
		for($yi=$y-1; $yi<=$y+1; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
			if($DEPURANDO>=$DEPURACION_ALTA) printf("x:$xi,y:$yi gris:%d\n",$pixeles[$offsetprgb]);
		}
	}
	if($DEPURANDO>=$DEPURACION_ALTA) printf("x:$x,y:$y prom:%d\n",(int)($sumapix/6));
	return (int)($sumapix/6);
}
/*
function plotcentro($xy){
        global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS,
                $DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles,$pixelesdebug;

	$x=$xy['x'];
	$y=$xy['y'];

	for($xi=$x-1; $xi<$x+1; $xi++){  // alveolo de 28*28
		for($yi=$y-1; $yi<$y+1; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$pixelesdebug[$offsetprgb]=0;
			$pixelesdebug[$offsetprgb+1]=0;
			$pixelesdebug[$offsetprgb+2]=255;
		}
	}
}
*/
function plot_alveolo($xy,$w,$h){   // centro ancho alto
	plot_mt($xy,$w,$h);
}
function plot_mt($xy,$w,$h){
        global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS,
                $DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles,$pixelesdebug;

        $x=$xy['x'];
        $y=$xy['y'];
	// $w wide,  $h height
	for($xi=$x-(int)($w/2); $xi<$x+(int)($w/2); $xi++){  // marca de tiempo de 12 de alto x 30 de ancho
        	$yi=$y-(int)($h/2);
		$offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
		$yi+=$h;
		$offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	for($yi=$y-(int)($h/2); $yi<$y+(int)($h/2); $yi++){
		$xi=$x-(int)($w/2);
                $offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
		$xi+=$w;
                $offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
        }
}

function debug_image(){
	global $image, $width, $height, $pixelesdebug, $filename;
	echo "generando imagen de depuracion $filename\n";
	$im = $image->getImage();
	$im->setImageColorspace (imagick::COLORSPACE_RGB);
	$im->setImageFormat("jpeg");
	$im->importImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR, $pixelesdebug);
	$im->writeImages($filename.".jpg", false);
}
?>
