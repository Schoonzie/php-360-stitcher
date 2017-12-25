<?php
error_reporting(-1);

define('MAX_WAIT_PER_IMAGE', 30);
define('PTO', getcwd() . '/template-XiaomiMijiaMi.pto');
define('DIR_UNSTITCHED', getcwd() . '/unstitched/');
define('DIR_STITCHING', getcwd() . '/stitching/');
define('DIR_STITCHED', getcwd() . '/stitched/');
define('TEMP_FILENAME', 'image.jpg');
define('TEMP_FILE', DIR_STITCHING . TEMP_FILENAME);

$dir = new DirectoryIterator(DIR_UNSTITCHED);
foreach ($dir as $fileinfo) {

  if (!$fileinfo->isFile()) {
    continue;
  }

  if ($fileinfo->isDot()) {
  	continue;
  }

  if (strtolower($fileinfo->getExtension()) != 'jpg') {
  	continue;
  }

  $filename = pathinfo($fileinfo->getFilename(), PATHINFO_FILENAME);

  if (file_exists(TEMP_FILE)) {
    print date("H:i:s") . ": Removing existing temporary file...\r\n";
  	unlink(TEMP_FILE);
  }

  print date("H:i:s") . ": Moving $filename to temp directory...\r\n";
  copy(DIR_UNSTITCHED . $fileinfo->getFilename(), TEMP_FILE);

  $stitched_filename = $filename . '_stitched';

  print date("H:i:s") . ": Stiching $filename...\r\n";
  $command = 'open /Applications/Hugin/PTBatcherGUI.app  --args --batch ' . PTO . ' ' . DIR_STITCHING . $stitched_filename;
  print date("H:i:s") . ": Running $command...\r\n";
  exec($command);

  $tmpfile1 = $stitched_filename . '0000.tif';
  $tmpfile2 = $stitched_filename . '0001.tif';
  $stitched_filename .= '.jpg';

  $total_wait = 0;

  // Initial 10 second sleep.
  while(
    !file_exists(DIR_STITCHING . $stitched_filename) && 
    $total_wait < MAX_WAIT_PER_IMAGE
  ) {
    print date("H:i:s") . ": Waiting for 5 seconds...\r\n";
    $total_wait += 5;
    sleep(10);
  }

  // Wait while temp files still exist.
  while(
    (file_exists(DIR_STITCHING . $tmpfile1) || file_exists(DIR_STITCHING . $tmpfile2)) && 
    $total_wait < MAX_WAIT_PER_IMAGE
  ) {
    print date("H:i:s") . ": Waiting for 5 seconds...\r\n";
    $total_wait += 5;
    sleep(5);
  }

  if ($total_wait >= MAX_WAIT_PER_IMAGE) {
    print date("H:i:s") . ": Something went wrong with. Skipping...\r\n";
    continue;
  }

  print date("H:i:s") . ": Moving files to stitched directory...\r\n";
  rename(DIR_UNSTITCHED . $fileinfo->getFilename(), DIR_STITCHED . $fileinfo->getFilename());
  rename(DIR_STITCHING . $stitched_filename, DIR_STITCHED . $stitched_filename);

  // Remove the temp file.
  if (file_exists(TEMP_FILE)) {
    unlink(TEMP_FILE);
  }

}