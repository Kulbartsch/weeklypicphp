# weeklypicphp

[German Version of this README.](README_DE.md)

A PHP web application to scale a JPG image to the right proportions and set EXIF data for our Weeklypic.de community. 

Many things are configurable, so might be generally useful.
Texts in the webapp are in german, internationalization is missing.

# Features

* Scale picture to 2000px (longest side)
* Resize picture to max 500KB 
* Setting meta data for
  * *title*
  * *description* (= weekliy-pic-username / title)
  * *creator*
  * *license*
* Show table of existing/required/new EXIF parameters
* Storing the parameters *weekly-pic-username*, *creator*, *license* and *delete-GPS-data* in a cookie for convenience, if requested
* Choose between *weekly* and *monthly* pic
* Preset week and month numbers for filename (but changeable)
* Automatic generation of valid weekly-pic filename
* Direct upload of picture to weeklypic.de
* Mobile-friendly layout
* Call up a map with the GPS coordinates of the image (if available)
* Check the date, on which the picture was taken, against the week or month
* Check participants
* Use existing title from metadata of picture
* Upload problematic pictures in a "to check" folder
* No adjustment of the image size if it is already OK
* Expert mode in which no metadata is changed
* Display of all metadata of an uploaded image
  
# User documentation

See ["how to use.pdf"](how to use.pdf) (german).

# Roadmap

* Fixing incorrect EXIF data after exporting from Darktable (see "Known Problems")

# Hint

Not only EXIF tags are stored in Pictures, but also IPTC, GPS and other tags.
Whenever we say/write EXIF we generally mean also all the other tags.

So, for example, if you want to change the artist, the following tags are affected:
* EXIF:Artist
* IPTC:by-line
* XMP:creator

# Setup

1. Prerequisites
    * PHP 7.*
    * imagemagick
    * EXIFtool
    * curl
    * For statistic
        * bash and standard utilities like sort, find
        * awk (the GNU version!)
        * bars (from https://github.com/Kulbartsch/bars )
    * For API
        * PHP module mbstring (`apt install php-mbstring` and `service apache2 restart`)
1. Copy this repo to your http folder.
2. Check File ownership and access rights
2. Check src/config.php and adapt to your needs.
3. In `_log` directory copy `htaccess` file to `.htaccess`.
3. In `src` directory copy `htaccess` file to `.htaccess`.
4. Create `src/config.config` file and make customizing. (see src/config.php for more information).
7. (optional) If you use this Application in an hidden subdirectory and you don't have start-page, you may want to copy the file "UR_wrong.html" as "index.html" into the webservers root directory. 


# Known Problem

There are graphic tools which manipulate the EXIF Tags, but do this wrong so the EXIF data is corrupt or at least not consitent.
This results in warnings or errors when this is processed with with exiftool. 
Warings can mostly ignored. When errors occur, the picture is not processed and the change of the meta data will fail.
Examples of this are:

* Darktable EXIF data probably wrong.
  * It seems, that data exported from Darktable will result in an `Error = Bad format (0) for IFD0 entry 0` when processed with exiftool. In this case the picture can't be processed and the program stops. I'm working on a solution, that in this and similar cases the EXIF data will be rewritten correctly.
* Rotating a picture with an (unknow to me) Linux preview tool corrupts the EXIF data.

Maybe I'll implement an optional processing step, that drops the corrupt meta-data and just updates the necessary data.
