#! /bin/bash

while read line
do
  echo "$line"
done < <(php ./PublicInfoBanjir/StateList)

function printRainLevel() {
  read -p "Masukan kod negeri: " STATE
  echo $STATE | xargs ./PublicInfoBanjir/RainLevel
}

function printRiverLevel() {
  read -p "Masukan kod negeri: " STATE
  echo $STATE | xargs ./PublicInfoBanjir/RiverLevel
}

printf "\r\nPilih kod operasi:\r\n"
operations=(RainLevel RiverLevel)
select operation in ${operations[@]}
do
  if [ $operation = "RainLevel" ]; then
    printRainLevel
    break
  else
    printRiverLevel
    break
  fi
done