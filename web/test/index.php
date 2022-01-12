<?php

  // Read file data
  $data = file_get_contents('file.png');

  // Pixels per inch
  $ppi = 300;

  // Unit conversion PPI to PPM
  $ppm = round($ppi * 100 / 2.54);

  $ppm_bin = str_pad(base_convert($ppm, 10, 2), 32, '0', STR_PAD_LEFT);

  // Split PNG data at first IDAT chunk
  $data_splitted = explode('IDAT', $data, 2);

  // Generate "pHYs" chunk data

  // 4-byte data length
  $length_bin = '00000000000000000000000000001001';
  // 4-byte type or 'pHYs'
  $chunk_name_bin = '01110000010010000101100101110011';
  // 9-byte data
  $ppu_data_bin = 
           $ppm_bin // Pixels per unit, x axis
          .$ppm_bin // Pixels per unit, y axis
          .'00000001'; // units - 1 for meters
  // Calculate 4-byte CRC
  $hash_buffer = '';
  foreach(str_split($chunk_name_bin.$ppu_data_bin, 8) as $b)
    $hash_buffer .= chr(bindec($b));
  $crc_bin = str_pad(base_convert(crc32($hash_buffer), 10, 2), 32, '0', STR_PAD_LEFT);
  // Create chunk binary string
  $binstring = $length_bin
          . $chunk_name_bin
          . $ppu_data_bin
          . $crc_bin;

  // Convert binary string to raw
  $phys_chunk_raw = '';
  foreach(str_split($binstring, 8) as $b)
    $phys_chunk_raw .= chr(bindec($b));

  // Insert "pHYs" chunk before first IDAT tag
  $new_image_data = substr($data_splitted[0], 0, strlen($data_splitted[0]) - 4)
        . $phys_chunk_raw
        . substr($data_splitted[0], strlen($data_splitted[0]) - 4, 4)
        . 'IDAT'
        . $data_splitted[1];

  // Output modified image
  header("Content-Type: image/png");
  echo $new_image_data;

?>