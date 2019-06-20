<?php
/**
 * @file
 * Default theme implementation to display a file.
 */
?>
<?php print $id; ?>
<?php if (isset($classes['something'])): ?><?php print $classes; ?>
<?php endif; ?>
<?php print $attributes; ?>
<?php
   print theme('image', array(
     'path' => $picture['thumb_img_url'],
     'alt' => $picture['description'],
     'title' => $picture['description'],
     'width' => '',
     'height' => '',
     'attributes' => array(),
   ));
?>
