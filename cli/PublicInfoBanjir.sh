#! /bin/bash

BASE_DIR="$( readlink -f -- "$0"; )";
REPLACE=".sh"

while read line
do
  echo "$line"
done < <(php ${BASE_DIR//$REPLACE//StateList})

function printRainLevel() {
  read -p "Masukan kod negeri: " STATE
  echo $STATE | xargs ${BASE_DIR//$REPLACE//RainLevel}
}

function printRiverLevel() {
  read -p "Masukan kod negeri: " STATE
  echo $STATE | xargs ${BASE_DIR//$REPLACE//RiverLevel}
}

printf "\r\nPilih kod operasi:\r\n"
operations=(RainLevel RiverLevel)
select operation in ${operations[@]}
do
  if [ $operation = "RainLevel" ]; then
    printRainLevel
    break
  elif [ $operation = "RiverLevel" ]; then
    printRiverLevel
    break
  fi
done
