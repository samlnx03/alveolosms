<?php

// adaptarse a las rotaciones
// buscar marca de tiempo start
// determinar el centro y descender por la vertical ajustando en cada subsiguiente marca de tiempo
//
// implementar busqueda horizontal a la derecha desde los centros de la marca de tiempo
// identifica numero de pregunta, numero de grupo, y centros de alveolos
//
$NOINFO=0; $RESULTADOS=1; $ERRORES=2; $ADVERTENCIAS=3; 
$DEPURACION_BAJA=4; $DEPURACION_MEDIA=5; $DEPURACION_ALTA=6;

//$DEPURANDO=$DEPURACION_BAJA;
//$DEPURANDO=$ADVERTENCIAS;
$DEPURANDO=$RESULTADOS;

$RELLENONUMSOL=22*22*150;     // umbral para determinar alveolo relleno en numero de solicitud
$RELLENO=22*22*127;     // umbral para determinar alveolo relleno

//$filename='Imagen1.bmp';
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

if($DEPURANDO>=$DEPURACION_ALTA) echo "Buscando numero de solicitud de ingreso (recuadro derecho)\n";
$x=1640;
$y=620;
// afuera del rectangulo de alveolos de sol de ingreso lado derecho centro
//
$xy=avanzar_sobre_blancos_a_la_izq(array($x,$y));
$xy[0]-=13; // meterse dentro del cuadro
$xy=avanzar_sobre_blancos_hacia_arriba(array($xy[0],$xy[1]));
$xy[1]+=30; // bajar a la altura del alveolo 0 de mas a la derecha
$xy[0]-=20; // meterse dentro del alveolo

$centroxy=array('x'=>$xy[0], 'y'=>$xy[1]);

for($r=0; $r<=9; $r++){
	for($i=6; $i>=1; $i--){
		if($DEPURANDO>=$DEPURACION_MEDIA) echo "renglon $r, alveolo $i\n";
		$centroxy=ajusta_centro(array('x'=>$centroxy['x'], 'y'=>$centroxy['y']));
		$solingreso[$r][$i]=$centroxy;
			// plots hasta el ultimo
			//plotcentro($centroxy);
		$centroxy['x']-=68;
	}
	$centroxy=$solingreso[$r][6];
	$centroxy['y']+=35;
}


if($DEPURANDO>=$DEPURACION_BAJA){
	echo "\n";
	for($r=0; $r<=9; $r++){
		for($i=1; $i<=6; $i++){
			//plot_alveolo($solingreso[$r][$i],28,28);
			printf("renglon $r, alveolo $i en (%d,%d)\n",$solingreso[$r][$i]['x'], $solingreso[$r][$i]['y']);
		}
	}
}

//   ------------------  determinacion de alveolos rellenos en numero de solicitud de ingreso
$nsol="";
for($c=1; $c<=6; $c++){ // columna
	for($r=0; $r<=9; $r++){
		$x=$solingreso[$r][$c]['x'];
		$y=$solingreso[$r][$c]['y'];
		//$gris=convolv($x,$y);
		$gris=gris_alveolo($x,$y,28,28);
/*if($gris<100000)
			$respuesta[$np][$opcion]=1;
		else
			$respuesta[$np][$opcion]=0;
 */
		if($DEPURANDO>=$DEPURACION_BAJA) echo "digito $c en posicion $r gris:$gris\n";
		if($gris<$RELLENONUMSOL){
			plot_alveolo($solingreso[$r][$c],40,40);
			$nsol.="$r";
			if($DEPURANDO>=$DEPURACION_BAJA) echo "digito $c en posicion $r gris:$gris RELLENADO\n";
		}
		
	}
}
if($DEPURANDO>=$DEPURACION_MEDIA)
	echo "*** numero de solicitud: $nsol\n";


if(strlen($nsol)<6) { 
	echo "NUM SOLICITUD ERRONEA $filename ";
	 /*exit(1);*/
}

if($DEPURANDO==$RESULTADOS){
  if(!$solicitud_archivo=fopen(dirname($filename)."-solicitud-archivo.txt", "a")){
	echo "No se pudo abrir archivo para guardar la relacion del # de solicitud al nombre de archivo\n";
	exit(1);
  }
  fwrite($solicitud_archivo,"$nsol $filename\n");
  fclose($solicitud_archivo);
}

//------------------------------------------------------------------------------------
//fin de numero de solitud de ingreso





if($DEPURANDO>=$DEPURACION_ALTA) echo "Buscando centro de marca de tiempo 0 (rectangulos) del lado izquierdo\n";


$x=20;   // justo arriba de la primer marca de tiempo lado izquierdo  <antes 40, hace 2 versiones> regresar a 40, otra vez 20
$y=473;		// antes 450
$mt[0]=marcatiempop1p2(array('x'=>$x, 'y'=>$y),array('x'=>$x+54,'y'=>$y+32));  // primer renglon de numero de solicitud de ingreso, antes p2:+34,+27
plot_mt($mt[0],30,12); // plotcentro($mt[0]);
for($i=1; $i<=9; $i++){
	if($DEPURANDO>=$DEPURACION_BAJA) echo "Buscando centro de marcas de tiempo $i\n";
	$xy=salir_marca_tiempo_vert($mt[$i-1]);
	$xy=encontrar_siguiente_marca_tiempo($xy);  // parte superior
	$xy['y']+=6;
	$mt[$i]=ajusta_centro_rectangulo_desde_centro($xy);
	plot_mt($mt[$i],30,12); //plotcentro($mt[$i]);
}
if($DEPURANDO>=$DEPURACION_BAJA){
	echo "\n";
	for($i=0; $i<=9; $i++)
		plot_mt($mt[$i],30,12);   //   marcas de tiempo, ancho y alto
}
// la mt9 es importante por la clave del examen,  170 pixeles a la derecha.
//
//        CLAVE DE EXAMEN   un nume del 1 al 5 y una letra de A - C

$clave_exam="";
$x=$mt[9]['x']+170;
$y=$mt[9]['y'];
for($alv=1; $alv<=5; $alv++){   // numero de la clave del examen
	$centroxy['x']=$x;
	$centroxy['y']=$y;
	$centroxy=ajusta_centro($centroxy);
	if($DEPURANDO>=$DEPURACION_MEDIA) print "clave de examen, centro alveolo $alv corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
	$x=$centroxy['x'];
	$y=$centroxy['y'];
	$gris=gris_alveolo($x,$y,28,28);
	if($gris<$RELLENONUMSOL){
		$clave_exam.="$alv";
		plot_alveolo($centroxy,40,40);  // resalta el relleno
	}
	//plot_alveolo($centroxy,28,28);
	$x+=66;   // distancia entre centros
}
for($alv='A'; $alv<='C'; $alv++){  //     letra de la clave del examen
	$centroxy['x']=$x;
	$centroxy['y']=$y;
	$centroxy=ajusta_centro($centroxy);
	if($DEPURANDO>=$DEPURACION_MEDIA) print "clave de examen, centro alveolo $alv corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
	$x=$centroxy['x'];
	$y=$centroxy['y'];
	$gris=gris_alveolo($x,$y,28,28);
	if($gris<$RELLENONUMSOL){
		$clave_exam.="$alv";
		plot_alveolo($centroxy,40,40);  // resalta el relleno
	}
	//plot_alveolo($centroxy,28,28);
	$x+=66;   // distancia entre centros
}
	//if(rellenado($centroxy)) 

if($DEPURANDO>=$DEPURACION_MEDIA)
	print "*** Clave de examen $clave_exam\n";
else if($DEPURANDO==$RESULTADOS){
	print "$clave_exam";
        print "$nsol";
}
if(strlen($clave_exam)<2) { echo "CLAVE DE EXAM ERRONEA $filename"; 
	//exit(1);
}

// siguen dos marcas de tiempo sin importancia, a la altura del nombre y la otra para separacion entre columnas
//
// las preguntas se empiezan a buscar desde 40,1185
$x=20;   // justo arriba de la primer marca de tiempo lado izquierdo, 20 porque agrega muchos balncos a la derecha e la marca, ubicar mejor el lado izquierdo
$y=1185;
if($DEPURANDO>=$DEPURACION_MEDIA) echo "Buscando centro de marca de tiempo PREGUNTA 1 desde $x,$y\n";
$mtpreg[1]=marcatiempo($x,$y);  // primer pregunta
if($DEPURANDO>=$DEPURACION_BAJA) echo "centro de mtpreg[1]: (".$mtpreg[1]['x'].",".$mtpreg[1]['y'].")\n";
for($i=2; $i<=14; $i++){
	if($DEPURANDO>=$DEPURACION_MEDIA) echo "\nBuscando centro de marca de tiempo PREGUNTA $i desde ".$mtpreg[1]['x'].",".$mtpreg[1]['y']."\n";
	$xy=salir_marca_tiempo_vert($mtpreg[$i-1]);
	$xy=encontrar_siguiente_marca_tiempo($xy);  // parte superior
	$xy['y']+=6;
	$mtpreg[$i]=ajusta_centro_rectangulo_desde_centro($xy);
	if($DEPURANDO>=$DEPURACION_BAJA) {
		echo "centro de mtpreg[$i]: (".$mtpreg[$i]['x'].",".$mtpreg[$i]['y'].")\n";
	}
}
if($DEPURANDO>=$DEPURACION_BAJA){
	echo "\n";
	for($i=1; $i<=14; $i++)
		plot_mt($mtpreg[$i],30,12);   //   marcas de tiempo, ancho y alto
}

//-------------------------------------------------------
// BUSQUEDA A LA derecha DEL PRIMER ALVEOLO
//

if($DEPURANDO>=$DEPURACION_MEDIA) print "Busqueda de alveolos ***** \n";
for($preg=1,$nr=1; $nr<=14; $nr++,$preg++){  	
   // renglon 1: preg 1, preg 15, preg 29, y preg 43
   // renglon 2: preg 2, preg 16, preg 30, y preg 44
   // etc. hast el reng 14
   if($DEPURANDO>=$DEPURACION_MEDIA) print "alveolos renglon $nr\n";
   $x=$mtpreg[$nr]['x'];	// centros de marca de tiempo
   $y=$mtpreg[$nr]['y'];
   $x=$x+100;  // al primer alveolo, opcion A de la pregunta del grupo de la izquierda

   $pregs=$preg;
   for($gpo=1; $gpo<=4; $gpo++,$pregs+=14){
	$centroxy['x']=$x;
	$centroxy['y']=$y;
	//verificar_tipo_alveolo($centroxy);
   	if($DEPURANDO>=$DEPURACION_MEDIA) {
		print "alveolos grupo $gpo\n";
		print "centro posible en ".$centroxy['x'].",".$centroxy['y']."\n";
	}
	$centroxy=ajusta_centro($centroxy);
	if($DEPURANDO>=$DEPURACION_MEDIA) print "centro corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
	$opcion='A';
	$alveolos[$pregs][$opcion]=$centroxy;
	plot_alveolo($centroxy,10,10);
	//if(rellenado($centroxy)) 
	if($DEPURANDO>=$DEPURACION_BAJA) print "renglon $nr, grupo $gpo, alveolo 1,  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
	if($DEPURANDO>=$DEPURACION_BAJA) print "pregunta $pregs\n";
	//if($DEPURANDO>=$DEPURACION_BAJA) print "*renglon ".($nr-1).", grupo ".(4-$gpo).", alveolo ".(4-1).",  centro en ".$centroxy['x'].",".$centroxy['y']."\n";

	for($n=2; $n<=4; $n++){
		// siguiente alveolo  n
   		if($DEPURANDO>=$DEPURACION_MEDIA) print "alveolos $n\n";
		$centroxy['x']=$centroxy['x']+15+37+15;  // del centro 15 para salir del alveolo, 37 entre alveolos y 15 al nuevo centro
		//verificar_tipo_alveolo($centroxy);
		if($DEPURANDO>=$DEPURACION_MEDIA) print "\talveolo $n centro posible en ".$centroxy['x'].",".$centroxy['y']."\n";
		$centroxy=ajusta_centro($centroxy);
		if($DEPURANDO>=$DEPURACION_MEDIA) print "\talveolo $n centro corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
		$alveolos[$pregs][++$opcion]=$centroxy;
		plot_alveolo($centroxy,10,10);
		if($DEPURANDO>=$DEPURACION_BAJA) print "\trenglon $nr, grupo $gpo, alveolo $n,  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
		//if($DEPURANDO>=$DEPURACION_BAJA) print "pregunta $pregs\n";
		//if($DEPURANDO>=$DEPURACION_BAJA) print "*renglon ".($nr-1).", grupo ".($gpo).", alveolo ".($n).",  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
	}
	$x=$centroxy['x']+200;  // del centro de la resp D del grupo de la izquierda a la respuesta A, dentro del alveolo
	$y=$centroxy['y'];
	if($DEPURANDO>=$DEPURACION_BAJA) print "\n";
   }  // sig gpo
}

if($DEPURANDO>=$DEPURACION_ALTA){
for($np=1; $np<=56; $np++){
	echo "pregunta $np\n";
	print_r($alveolos[$np]);
}
}

///    *****************   DETERMINACION DE ALVEOLOS RELLENADOS ********************************
/*
for($np=1; $np<=56; $np++){
	$respuesta[$np]=0;
}

for($np=1; $np<=56; $np++){
	for($opcion='A';$opcion<='D'; $opcion++){
		$x=$alveolos[$np][$opcion]['x'];
		$y=$alveolos[$np][$opcion]['y'];
		$gris=convolv($x,$y);
		if($gris<100000)
			$respuesta[$np]=($respuesta[$np]<<1) | ord($opcion)-ord('@');
	}
}
*/

for($np=1; $np<=56; $np++){
	$respuesta[$np]=0;
	for($opcion='A';$opcion<='D'; $opcion++){
		$x=$alveolos[$np][$opcion]['x'];
		$y=$alveolos[$np][$opcion]['y'];
		//$gris=convolv($x,$y);
		$gris=gris_alveolo($x,$y,28,28);
		if($gris<$RELLENO)
			//$respuesta[$np][$opcion]=1;
			$respuesta [$np] = $respuesta[$np] | pow (2, ord($opcion) - ord ('A')); 
		//else
		//	$respuesta[$np][$opcion]=0;
		if($DEPURANDO>=$DEPURACION_BAJA){
			$centroxy=array('x'=>$x, 'y'=>$y);
			echo "preg $np, opcion $opcion ($x,$y) gris:$gris ";
			if($gris<$RELLENO){
				plot_alveolo($centroxy,40,40);
				echo "RELLENADO\n";
			}
			else
				echo "\n";
		}
	}
}
/*
for($np=1; $np<=56; $np++){
	printf("*** preg $np: ");
	for($r='A'; $r<='D'; $r++){
		printf(" $r:%d",$respuesta[$np][$r]);
	}
	echo "\n";
}
 */
function log2 ($x) {
	return (log10 ($x) / log10 (2));
}

if ($DEPURANDO>=$DEPURACION_BAJA)
{
	for ($np=1; $np<=56; $np++){
		printf ("*** preg $np:");
		printf ("%d", $respuesta[$np]);
		echo "\n";
	}
}
else if($DEPURANDO==$RESULTADOS){

        for ($np=1; $np<=56; $np++){
                $r = $respuesta[$np] & 15;
                for ($cont = 0, $b=0; $b < 4; $b++)
                        if ($r & pow (2, $b))
                                $cont++;
                if ($cont == 0)
                        printf (" ");
                else
                        printf ("%c", log2 ($respuesta[$np]) + 65);
        }
}
	

// lineas horizontales en la direccion de la marca de tiempo
/*
for($n=1; $n<=14; $n++){
   $x=$mtpreg[$n]['x'];
   $y=$mtpreg[$n]['y'];
   for($x1=$x;$x1<1650;$x1++){
	        $offsetp = $y*$width + $x1;  // en el arreglo lineal
	        $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$pixelesdebug[$offsetprgb]=0;
		$pixelesdebug[$offsetprgb+1]=255;
		$pixelesdebug[$offsetprgb+2]=0;
   }
}
 */
//

if($DEPURANDO>=$DEPURACION_BAJA) {
	debug_image();
}

exit;

// ===================================================================================================================
// funciones 
?>
<?php
function marcatiempo($x,$y){
	//investigar por las columnas a la vez para encontrar el borde izquierdo
	//al encontrar un negro investigar a la derecha al menos 28 pixeles
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width, $pixeles;
	global $filename;
	$scx=$x; $scy=$y;
	for($x=$scx; $x<=$scx+90; $x++){
		for($y=$scy; $y<=$scy+80; $y++){   // antes 30
			//print "---x:$x, y:$y\n";
			$offsetp = $y*$width + $x;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			if($pixeles[$offsetprgb]<100){
				// tal vez se encontro borde izquierdo
				$nd=negros_a_la_derecha($x,$y); // rectangulo 12 alto x 30 ancho  de 50 de gris
				//print "--- negros a la derecha $nd\n";
				if(negros_a_la_derecha($x,$y)<24*70) { // rectangulo 12 alto x 30 ancho  de 50 de gris
					$sinccol=array('x'=>$x+15, 'y'=>$y+6);
					//print "---dentro de la marca de tiempo, x=$x,y:$y\n";
					break 2;
				}
			}
		}
	}
	if(!isset($sinccol)){
		echo "\n$filename No se encontro marca de sincronizacion de tiempo\n";
		echo "punto de partida ($scx,$scy)\n";
		echo "max 90 pix a la derecha y 80 hacia abajo\n";
		echo "termino en ($x,$y)\n";
		exit;
	}
	if($DEPURANDO>=$DEPURACION_MEDIA){
		print "\tOk, posible marca de sinc de tiempo (borde mas a la izquierda) en $x,$y\n";
		print "\tPosible Centro en ".$sinccol['x'].",".$sinccol['y']."\n";
	}
	$sinccol=ajusta_centro_rectangulo($sinccol);
	if($DEPURANDO>=$DEPURACION_MEDIA)
		print "\tCentro corregido en ".$sinccol['x'].",".$sinccol['y']."\n";
	return $sinccol;
}

function marcatiempop1p2($p1,$p2){   // limites de busqueda array x,y   cada uno
	//investigar por RENGLONES hacia la derecha hasta encontrar el borde izquierdo
	//al encontrar un negro investigar a la derecha al menos 28 pixeles
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width, $pixeles, $pixelesdebug;
	global $filename;
	$scx=$p1['x']; $scy=$p1['y'];   $limx=$p2['x']; $limy=$p2['y']; 
	for($y=$scy; $y<=$limy; $y++){   // antes 30
		for($x=$scx; $x<=$limx; $x++){
			//print "---x:$x, y:$y\n";
			$offsetp = $y*$width + $x;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			if($DEPURANDO>=$DEPURACION_MEDIA){
				$pixelesdebug[$offsetprgb]=0;$pixelesdebug[$offsetprgb+1]=255;$pixelesdebug[$offsetprgb+2]=0;
			}
			if($pixeles[$offsetprgb]<127){
				// tal vez se encontro borde izquierdo
				$nd=negros_a_la_derecha($x,$y); // rectangulo 12 alto x 30 ancho  de 50 de gris
				//print "--- negros a la derecha $nd\n";
				if(negros_a_la_derecha($x,$y)<24*70) { // rectangulo 12 alto x 30 ancho  de 50 de gris
					$sinccol=array('x'=>$x+15, 'y'=>$y+6);
					//print "---dentro de la marca de tiempo, x=$x,y:$y\n";
					break 2;
				}
			}
		}
	}
	if(!isset($sinccol)){
		echo "\n$filename No se encontro marca de sincronizacion de tiempo\n";
		echo "punto de partida ($scx,$scy)\n";
		echo "max 90 pix a la derecha y 80 hacia abajo\n";
		echo "termino en ($x,$y)\n";
		debug_image();
		exit;
	}
	if($DEPURANDO>=$DEPURACION_MEDIA){
		print "\tOk, posible marca de sinc de tiempo (borde mas a la izquierda) en $x,$y\n";
		print "\tPosible Centro en ".$sinccol['x'].",".$sinccol['y']."\n";
	}
	$sinccol=ajusta_centro_rectangulo($sinccol);
	if($DEPURANDO>=$DEPURACION_MEDIA)
		print "\tCentro corregido en ".$sinccol['x'].",".$sinccol['y']."\n";
	return $sinccol;
}


?>



<?php
function ajusta_centro_rectangulo_desde_centro($sinccol){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
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
	if($DEPURANDO>=$DEPURACION_MEDIA) print "\tCentro ajustado de ".$sinccol['x'].",".$sinccol['y']." a: $centroX,$centroY\n";
	return array('x'=>$centroX, 'y'=>$centroY);
}

function ajusta_centro_rectangulo($sinccol){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
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
	if($DEPURANDO>=$DEPURACION_ALTA) print "Centro ajustado a: $centroX,$centroY\n";
	return array('x'=>$centroX, 'y'=>$centroY);
}
function ajusta_centro($sinccol){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
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
	for($xi=$x-14; $xi<=$x+14; $xi++){  // alveolo de 28*28
		for($yi=$y-14; $yi<=$y+14; $yi++){
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
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
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
	$pxy=avanzar_sobre_negros_a_la_der($pxy); // salir de la marca de tiempo
	return $pxy;
}

function encontrar_alveolo_a_la_der($x,$y){ // desde un punto blanco a la derecha del alveolo
	$pxy=array($x,$y);
	$pxy=avanzar_sobre_blancos_a_la_der($pxy); // encontrar alveolo
	return $pxy;
}
function avanzar_sobre_negros_a_la_der($pxy){
	$pxy= _avanzar_derecha($pxy,"GT",150);  // avanzar der y detenerse cuando gris>200
	return $pxy;
}	
function avanzar_sobre_blancos_a_la_der($pxy){
	$pxy= _avanzar_derecha($pxy,"LT",127);  // avanzar der y detenerse cuando gris<50
	return $pxy;
}	


function _avanzar_derecha($pxy,$cond,$color){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;

	// salir de la marca de tiempo hacia la derecha
	if($DEPURANDO>=$DEPURACION_ALTA) echo "Avanzando a la der desde ".$pxy[0].",".$pxy[1]." Detener cuando gris $cond $color\n";
	$x=$pxy[0];
	$y=$pxy[1];
	for(;;$x++){
		// buscar un blanco a la izquierda
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$colorpix=$pixeles[$offsetprgb];
		if($DEPURANDO>=$DEPURACION_ALTA) print("\tx=$x color=$colorpix\n");
		if($cond=='LT'){
			if($pixeles[$offsetprgb]<$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_ALTA) print("\t\trojo en $x,$y\n");
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	return array($x,$y);
}
// ------------------------------------------------------------------------------------------
function avanzar_sobre_blancos_a_la_izq($pxy){
	$pxy= _avanzar_izquierda($pxy,"LT",180);  // avanzar izq y detenerse cuando gris<50, luego 127
	return $pxy;
}	
function avanzar_sobre_blancos_hacia_arriba($pxy){
	$pxy= _avanzar_arriba($pxy,"LT",180);  // avanzar izq y detenerse cuando gris<50, luego 127
	return $pxy;
}	

function _avanzar_arriba($pxy,$cond,$color){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;

	if($DEPURANDO>=$DEPURACION_ALTA) echo "Avanzando hacia arriba desde ".$pxy[0].",".$pxy[1]." Detener cuando gris $cond $color\n";
	$x=$pxy[0];
	$y=$pxy[1];
	for(;;$y--){
		// buscar un blanco a la izquierda
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$colorpix=$pixeles[$offsetprgb];
		if($DEPURANDO>=$DEPURACION_ALTA) print("\ty=$y color=$colorpix\n");
		if($cond=='LT'){
			if($pixeles[$offsetprgb]<$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_ALTA) print("\t\trojo en $x,$y\n");
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	return array($x,$y);
}
function _avanzar_izquierda($pxy,$cond,$color){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;

	// salir de la marca de tiempo hacia la izquierda
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
			//	if(es_ruido($x,$y)<$color){
					break;
				//}
			}

		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_ALTA) print("\t\trojo en $x,$y\n");
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	return array($x,$y);
}

// -------------------------------------------------------------------------------------------------
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
	$bw=true;
	for($xi=$x-(int)($w/2); $xi<$x+(int)($w/2); $xi++){  // marca de tiempo de 12 de alto x 30 de ancho
		if($bw){
			$clr=255;
			$bw=false;
		}
		else{
			$clr=0;
			$bw=true;
		}
        	$yi=$y-(int)($h/2);
		$offsetp = $yi*$width + $xi;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=$clr;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
		$yi+=$h;
		$offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=$clr;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	for($yi=$y-(int)($h/2); $yi<$y+(int)($h/2); $yi++){
		if($bw){
			$clr=255;
			$bw=false;
		}
		else{
			$clr=0;
			$bw=true;
		}
		$xi=$x-(int)($w/2);
                $offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=$clr;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
		$xi+=$w;
                $offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=$clr;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
        }
}


function salir_marca_tiempo_vert($axy){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles,$pixelesdebug;
	$x=$axy['x'];
	$y=$axy['y'];
	if($DEPURANDO>=$DEPURACION_ALTA) print(" salir de marca de tiempo desde $x,$y\n");
	for(;;$y++){
		// buscar un blanco (parte inferior de recytangulo actual
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		if($DEPURANDO>=$DEPURACION_ALTA)printf("\ty=$y :%d\n",$pixeles[$offsetprgb]);
		if($pixeles[$offsetprgb]>180)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
			break;
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	if($DEPURANDO>=$DEPURACION_ALTA)
		print "terminan negros en x:$x, y:$y\n";
	return array('x'=>$x, 'y'=>$y);
}


function encontrar_siguiente_marca_tiempo($axy){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles,$pixelesdebug;
	$x=$axy['x'];
	$y=$axy['y'];
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
	if($DEPURANDO>=$DEPURACION_ALTA) print "Siguiente marca de tiempo en: $x,$y\n";
	return array('x'=>$x, 'y'=>$y);
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

function debug_image(){
	global $image, $width, $height, $pixelesdebug, $filename;
	echo "generando imagen de lineas horizontales sobre alveolos sin considerar rotacion y centros corregidos\n";
	$im = $image->getImage();
	$im->setImageColorspace (imagick::COLORSPACE_RGB);
	$im->setImageFormat("jpeg");
	$im->importImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR, $pixelesdebug);
	$im->writeImages($filename.".jpg", false);
}
?>
