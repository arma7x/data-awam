#! /bin/bash

while read line
do
  echo "$line"
done < <(php ./PublicInfoBanjir/StateList)

function printRainLevel() {
  read -p "Masukan kod negeri: " STATE
  echo $STATE | xargs ./cli/PublicInfoBanjir/RainLevel
}

function printRiverLevel() {
  read -p "Masukan kod negeri: " STATE
  echo $STATE | xargs ./cli/PublicInfoBanjir/RiverLevel
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
