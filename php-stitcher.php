<?php

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

  if ($fileinfo->getExtension() != 'jpg') {
  	continue;
  }

  $filename = $fileinfo->getBasename('.jpg');

  if (file_exists(TEMP_FILE)) {
    print "Removing existing temporary file...\r\n";
  	unlink(TEMP_FILE);
  }

  print "Moving $filename to temp directory...\r\n";
  rename(DIR_UNSTITCHED . $fileinfo->getFilename(), TEMP_FILE);

  $stitched_filename = $filename . '_stitched';

  print "Stiching $filename...\r\n";
  exec('open /Applications/Hugin/PTBatcherGUI.app  --args --batch ' . PTO . ' ' . DIR_STITCHING . $stitched_filename);

  $tmpfile1 = $stitched_filename . '0000.tif';
  $tmpfile2 = $stitched_filename . '0001.tif';
  $stitched_filename .= '.jpg';

  // Initial 10 second sleep.
  while(
    !file_exists(DIR_STITCHING . $stitched_filename)
  ) {
    sleep(10);
  }

  // Wait while temp files still exist.
  while(
    file_exists(DIR_STITCHING . $tmpfile1) || 
    file_exists(DIR_STITCHING . $tmpfile2)
  ) {
    sleep(5);
  }

  print "Moving files to stitched directory...\r\n";
  rename(TEMP_FILE, DIR_STITCHED . $fileinfo->getFilename());
  rename(DIR_STITCHING . $stitched_filename, DIR_STITCHED . $stitched_filename);
}