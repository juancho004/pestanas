<?php 
/* 
 *    -------------------------------------------------------------------------------------------------------------------------- 
 *    Program            : Image class, PHP Class Library 
 *    Version            : 1.0.0 
 *    Files            : image.inc.php, test.php 
 *    Author            : Lasantha Samarakoon 
 *    Date released    : Monday, November 2, 2009 
 *    Email            : lasn1987@gmail.com 
 *    Licence            : http://www.gnu.org/licenses/gpl.txt 
 *    -------------------------------------------------------------------------------------------------------------------------- 
 * 
 *    This program is a freeware, which falls under GNU Genral Public Licence. 
 *    --------------------------------------------------------------------------------------------------------------------------- 
 *    You can modify this program, without any permission from the author. But be kind enough to send the updated version to the 
 *    author through the above mentioned Email address. 
 *    --------------------------------------------------------------------------------------------------------------------------- 
 *    Documentation: 
 *            Please refer the test.php file for hints on the usage of this class library. 
 *    --------------------------------------------------------------------------------------------------------------------------- 
 *    ************************************* PROUD TO BE A SRI LANKAN...!!! ****************************************************** 
 */ 

class Image { 
     
    protected $properties = array( 
        'img_orig'     => null,        // contents of original image 
        'width'        => 0,            // width of the original image 
        'height'    => 0            // height of the original image 
    ); 
     
    // getter 
    function __get($k) { 
         
        if(array_key_exists($k, $this->properties)) 
            return $this->properties[$k]; 
    } 
     
    // setter 
    function __set($k, $v) { 
         
        $this->properties[$k] = $v; 
    } 
     
    // this functoin is used to open an image file 
    function open($filename) { 
         
        // check if the file exists 
        if(file_exists($filename)) { 
         
            // extract attributes of the image file 
            list($this->width, $this->height, $t, $a) = getimagesize($filename); 

            switch($t) { 
                 
                case 1: $this->img_orig = imagecreatefromgif($filename); break;        // open gif file 
                case 2: $this->img_orig = imagecreatefromjpeg($filename); break;    // open jpg file 
                case 3: $this->img_orig = imagecreatefrompng($filename); break;        // open png file 
            } 
        } 
         
        return $this; 
    } 
     
    // save image as a file in 3 formats... working same as convert... 
    function save($dest, $type = 'png') { 
        
        $this->status  = true;
        if(! file_exists($dest)) { 
             
            switch($type) { 
                 
                case 'gif': $response = @imagegif($this->img_orig, $dest); break; 
                case 'jpg':
                 case 'jpeg': $response = @imagejpeg($this->img_orig, $dest); break; 
                case 'png': $response = @imagepng($this->img_orig, $dest); break; 
            } 
        } 
         

        if(!$response):
            $this->message = 'Error, Permission denied';
            $this->status  = false;
        endif;
        return $this;

        
    } 
     
    // I'm finished :( 
    function __destruct() { 
        if($this->img_orig!=null) 
			imagedestroy($this->img_orig); 
    } 
     
    // resize image 
    function resize($w, $h) { 
         
        $t = imagecreatetruecolor($w, $h); 
         
        imagecopyresampled($t, $this->img_orig, 0, 0, 0, 0, $w, $h, $this->width, $this->height); 
         
        imagedestroy($this->img_orig); 
         
        $this->img_orig = imagecreatetruecolor($w, $h); 
         
        imagecopyresampled($this->img_orig, $t, 0, 0, 0, 0, $w, $h, $w, $h); 
         
        imagedestroy($t); 
         
        $this->width = $w; 
        $this->height = $h; 
         
        return $this; 
    } 
     
    // crop image 
    function crop($x, $y, $w, $h) { 
     
        if($x + $w > $this->width) 
            $w = $this->width - $x; 
             
        if($y + $h > $this->height) 
            $h = $this->height - $y; 
             
        if($w <= 0 || $h <= 0) return false; 
         
        $t = imagecreatetruecolor($w, $h); 
         
        imagecopyresampled($t, $this->img_orig, 0, 0, $x, $y, $this->width, $this->height, $this->width, $this->height); 
         
        imagedestroy($this->img_orig); 
         
        $this->img_orig = imagecreatetruecolor($w, $h); 
         
        imagecopyresampled($this->img_orig, $t, 0, 0, 0, 0, $w, $h, $w, $h); 
         
        imagedestroy($t); 
         
        $this->width = $w; 
        $this->height = $h; 
         
        return $this; 
    } 
     
    // convert image into grayscale 
    function convertGrayscale() { 
         
        imagefilter($this->img_orig, IMG_FILTER_GRAYSCALE); 
         
        return $this; 
    } 
     
    // convert image into sepia 
    function convertSepia() { 
         
        imagefilter($this->img_orig, IMG_FILTER_GRAYSCALE); 
        imagefilter($this->img_orig, IMG_FILTER_COLORIZE, 112, 66, 20); 
         
        return $this; 
    } 
     
    // render image directly to the web browser without saving 
    function render($type = 'png') { 
         
        //header("Content-Disposition: attachment; filename=gnome.png; modification-date=\"Wed, 12 Feb 1997 16:29:51 -0500\";"); 
         
        switch($type) { 
             
            case 'gif': 
                header("Content-Type: image/gif"); 
                imagegif($this->img_orig); 
                break; 
            case 'jpg': 
            case 'jpeg':
                header("Content-Type: image/jpeg"); 
                imagejpeg($this->img_orig); 
                break; 
            case 'png': 
                header("Content-Type: image/png"); 
                imagepng($this->img_orig); 
                break; 
        } 
         
        return $this; 
    } 
} 
?>