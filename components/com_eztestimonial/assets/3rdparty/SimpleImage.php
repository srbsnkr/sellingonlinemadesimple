<?php
 // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

/*
* I have combined 2 codes in one class for testimonial component
* Thanks to Author: White-hat-web-design.co.uk And Abeautifulsite.net
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
* And 
* http://www.abeautifulsite.net/blog/2009/08/cropping-an-image-to-make-square-thumbnails-in-php/
*/


class SimpleImage {
		  var $image;
		  var $image_type;
		 //function to make square
		 function square_crop($src_image, $dest_image, $thumb_size = 100, $jpg_quality = 90) {
			// Get dimensions of existing image
			$image = getimagesize($src_image);
			// Check for valid dimensions
			if( $image[0] <= 0 || $image[1] <= 0 ) return false;
			// Determine format from MIME-Type
			$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));
			// Import image
			switch( $image['format'] ) {
				case 'jpg':
				case 'jpeg':
					$image_data = imagecreatefromjpeg($src_image);
				break;
				case 'png':
					$image_data = imagecreatefrompng($src_image);
				break;
				case 'gif':
					$image_data = imagecreatefromgif($src_image);
				break;
				default:
					// Unsupported format
					return false;
				break;
			}
			// Verify import
			if( $image_data == false ) return false;
			// Calculate measurements
			if( $image[0] & $image[1] ) {
				// For landscape images
				$x_offset = ($image[0] - $image[1]) / 2;
				$y_offset = 0;
				$square_size = $image[0] - ($x_offset * 2);
			} else {
				// For portrait and square images
				$x_offset = 0;
				$y_offset = ($image[1] - $image[0]) / 2;
				$square_size = $image[1] - ($y_offset * 2);
			}
			// Resize and crop
			$canvas = imagecreatetruecolor($thumb_size, $thumb_size);
			if( imagecopyresampled(
				$canvas,
				$image_data,
				0,
				0,
				$x_offset,
				$y_offset,
				$thumb_size,
				$thumb_size,
				$square_size,
				$square_size
			)) {
				// Create thumbnail
				switch( strtolower(preg_replace('/^.*\./', '', $dest_image)) ) {
					case 'jpg':
					case 'jpeg':
						return imagejpeg($canvas, $dest_image, $jpg_quality);
					break;
					case 'png':
						return imagepng($canvas, $dest_image);
					break;
					case 'gif':
						return imagegif($canvas, $dest_image);
					break;
					default:
						// Unsupported format
						return false;
					break;
				}
		 
			} else {
				return false;
			}
		 
		}
		
		   function load($filename) {
		 
			  $image_info = getimagesize($filename);
			  $this->image_type = $image_info[2];
			  if( $this->image_type == IMAGETYPE_JPEG ) {
		 
				 $this->image = imagecreatefromjpeg($filename);
			  } elseif( $this->image_type == IMAGETYPE_GIF ) {
		 
				 $this->image = imagecreatefromgif($filename);
			  } elseif( $this->image_type == IMAGETYPE_PNG ) {
		 
				 $this->image = imagecreatefrompng($filename);
			  }
		   }
		   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
		 
			  if( $image_type == IMAGETYPE_JPEG ) {
				 imagejpeg($this->image,$filename,$compression);
			  } elseif( $image_type == IMAGETYPE_GIF ) {
		 
				 imagegif($this->image,$filename);
			  } elseif( $image_type == IMAGETYPE_PNG ) {
		 
				 imagepng($this->image,$filename);
			  }
			  if( $permissions != null) {
		 
				 chmod($filename,$permissions);
			  }
		   }
		   function output($image_type=IMAGETYPE_JPEG) {
		 
			  if( $image_type == IMAGETYPE_JPEG ) {
				 imagejpeg($this->image);
			  } elseif( $image_type == IMAGETYPE_GIF ) {
		 
				 imagegif($this->image);
			  } elseif( $image_type == IMAGETYPE_PNG ) {
		 
				 imagepng($this->image);
			  }
		   }
		   function getWidth() {
		 
			  return imagesx($this->image);
		   }
		   function getHeight() {
		 
			  return imagesy($this->image);
		   }
		   function resizeToHeight($height) {
		 
			  $ratio = $height / $this->getHeight();
			  $width = $this->getWidth() * $ratio;
			  $this->resize($width,$height);
		   }
		 
		   function resizeToWidth($width) {
			  $ratio = $width / $this->getWidth();
			  $height = $this->getheight() * $ratio;
			  $this->resize($width,$height);
		   }
		 
		   function scale($scale) {
			  $width = $this->getWidth() * $scale/100;
			  $height = $this->getheight() * $scale/100;
			  $this->resize($width,$height);
		   }
		 
		   function resize($width,$height) {
			  $new_image = imagecreatetruecolor($width, $height);
			  imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
			  $this->image = $new_image;
		   }  	
}
?>