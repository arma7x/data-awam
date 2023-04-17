<?php

function clear() {
  echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
}

function readline_terminal($prompt = '') {
  $prompt && print $prompt;
  $terminal_device = '/dev/tty';
  $h = fopen($terminal_device, 'r');
  if ($h === false) {
      #throw new RuntimeException("Failed to open terminal device $terminal_device");
      return false; # probably not running in a terminal.
  }
  $line = rtrim(fgets($h),"\r\n");
  fclose($h);
  return $line;
}
