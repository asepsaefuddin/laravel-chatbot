<?php

echo '<pre>';

echo "TEMP DIR: ";
echo sys_get_temp_dir();

echo "\n\n";

echo "UPLOAD TMP DIR: ";
echo ini_get('upload_tmp_dir');

echo "\n\n";

print_r($_FILES);

echo '</pre>';