if [ "$1" == "" ]; then
	echo "debe indicar el directorio"
	exit 1
fi
INICIAL=$(ls $1 | head -1 | cut -c -3)
FINAL=$(ls $1 | tail -1 | cut -c -3)

php genbatch.php $1 $INICIAL  $FINAL > $1.sh

