<?php
require_once('inc/data.inc');

// A photo repository refers to a directory on the server where we look for
// original photos.  We keep separate PhotoRepository objects for racer and car
// photos, but it's OK for them to refer to the same directory.
class PhotoRepository {
  private $directory;
  private $all_renders = array();

  public function __construct($dir) {
    $this->directory = $dir;
  }

  public function all_renders() {
    return $this->all_renders;
  }

  public function lookup($label) {
    return $this->all_renders[$label];
  }

  public function directory() {
    return $this->directory;
  }

  public function make_render($label, $max_width, $max_height, $antecedent) {
    return $this->all_renders[$label] = 
        new PhotoRender($this, $label, $max_width, $max_height, $antecedent);
  }
}

// A photo "render" here is a subdirectory of the photo repository, into which
// are written cropped and/or scaled versions of the original photos.
class PhotoRender {

  public function __construct($repository, $label, $max_width, $max_height, $antecedent) {
    $this->repository = $repository;
    $this->label = $label;
    $this->directory = $repository->directory();
    if ($label != 'original') {
      if ($max_width == 0 || $max_height == 0) {
        $this->directory .= DIRECTORY_SEPARATOR.$label;
      } else {
        $this->directory .= DIRECTORY_SEPARATOR.$max_width.'x'.$max_height;
      }
    }

    $this->max_width = $max_width;
    $this->max_height = $max_height;
    $this->antecedent = $antecedent;
  }

  public function repository() { return $this->repository; }
  public function label() { return $this->label; }
  public function directory() { return $this->directory; }
  public function max_width() { return $this->max_width; }
  public function max_height() { return $this->max_height; }
  public function antecedent() { return $this->antecedent; }

  public function file_path($imagename) {
    return $this->directory.DIRECTORY_SEPARATOR.$imagename;
  }

  public function target_file_path($imagename) {
    if (!file_exists($this->directory())) {
      mkdir($this->directory());
    }
    return $this->file_path($imagename);
  }

  public function find_source_render($image_name) {
    if ($this->antecedent) {
      $source = $this->repository->lookup($this->antecedent);
      if (file_exists($source->file_path($image_name))) {
        return $source;
      } else {
        return $source->find_source_render($image_name);
      }
    } else {
      return false;
    }
  }

  public function delete_dependents($image_name) {
    foreach ($this->repository->all_renders() as $render) {
      if ($render->antecedent() == $this->label()) {
        @unlink($render->file_path($image_name));
        $render->delete_dependents($image_name);
      }
    }
  }

  public function wipe_directory() {
    if (is_dir($this->directory())) {
      foreach (glob($this->directory().DIRECTORY_SEPARATOR.'*.{jpg,jpeg,JPG,JPEG,gif,GIF,png,PNG}', GLOB_BRACE)
               as $file) {
        @unlink($file);
      }
    }
  }

  // Constructs a URL for the photo whose base name is $imagefile.
  public function url($imagefile) {
    if ($imagefile != '') {
      $imagefile = basename($imagefile);
      $targetpath = $this->file_path($imagefile);
      $breaker = @filemtime($targetpath);
      if (!$breaker) {
        // If the file doesn't exist, use the current (request) time as a cache
        // breaker, but negate it to make sure there's no collision with the
        // filemtime when the file does get created.
        $breaker = -time();
      }
      return 'photo.php/'.$this->label().'/'.$breaker.'/'.urlencode($imagefile);
    } else {
      return '';
    }
  }

  public function find_or_make_image_file($image_name) {
    $target_file_path = $this->target_file_path($image_name);
    if (!file_exists($target_file_path)) {
      $source = $this->find_source_render($image_name);
      $source_file_path = $source->file_path($image_name);

      if ($this->max_height() == 0) {
        return $source_file_path;
      } else {
        list($source_width, $source_height, $image_type) = getimagesize($source_file_path);
        if ($source_height == 0) {
          return FALSE;
        }

        $im = open_image($source_file_path, $image_type);

        $aspect = $source_width / $source_height;

        if ($aspect > $this->max_width() / $this->max_height()) {
          // At any height, source image is too wide, so pick scaling to
          // convert $source_width to $max_width.  The scale factor is then
          // $max_width/$source_width.
          $width = $this->max_width();
          $height = (int) ($source_height * $this->max_width() / $source_width);
        } else {
          // Conversely, scale to max_height.
          $width = (int) ($source_width * $this->max_height() / $source_height);
          $height = $this->max_height();
        }

        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresampled($thumb, $im, 0,0,0,0, $width, $height, $source_width, $source_height);

        write_image($thumb, $target_file_path, $image_type);

        imagedestroy($im);
        imagedestroy($thumb);
      }
    }
    return $target_file_path;
  }

  private $repository;
  private $label;
  private $directory;
  private $max_height;
  private $max_width;
  private $antecedent;
}

// TODO: This name is used globally
$headshots = new PhotoRepository(photo_directory());
$photosize = explode('x', photo_size());

$headshots->make_render('original', 0, 0, '');
$headshots->make_render('cropped', 0, 0, 'original');
// "Working size" images: big enough to edit, but small enough to fit on screen
$headshots->make_render('work', 950, 950, 'original');
$headshots->make_render('display', 0 + $photosize[0], 0 + $photosize[1], 'cropped');
// "Thumbnail" images are what get displayed on the photo-thumbs page
$headshots->make_render('thumb', 150, 150, 'cropped');
// "Tiny": no more than 80 pixels, because that's what jQuery Mobile
// limits to in a listview.
$headshots->make_render('tiny', 80, 80, 'cropped');

function open_image($filepath, $image_type) {
  switch ($image_type) {
  case IMAGETYPE_GIF:
    return imagecreatefromgif($filepath);
  case IMAGETYPE_JPEG:
    return imagecreatefromjpeg($filepath);
  case IMAGETYPE_PNG:
    return imagecreatefrompng($filepath);
  }

  return FALSE;
}

function write_image($image, $filepath, $image_type) {
  switch ($image_type) {
  case IMAGETYPE_GIF:
    return imagegif($image, $filepath);
  case IMAGETYPE_JPEG:
    return imagejpeg($image, $filepath, 90);
  case IMAGETYPE_PNG:
    return imagepng($image, $filepath, 0);
  }
  return FALSE;
}

function pseudo_mime_content_type($filename) {
  $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

  $ext = strtolower(array_pop(explode('.',$filename)));
  if (array_key_exists($ext, $mime_types)) {
	return $mime_types[$ext];
  }
  elseif (function_exists('finfo_open')) {
	$finfo = finfo_open(FILEINFO_MIME);
	$mimetype = finfo_file($finfo, $filename);
	finfo_close($finfo);
	return $mimetype;
  }
  else {
	return 'application/octet-stream';
  }
}
?>