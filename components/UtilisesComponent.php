<?php


namespace app\components;


use app\models\Log;

class UtilisesComponent
{
    private static $instance;

    protected function __construct() {}
    protected function __clone() {}
    protected function __wakeup() {}

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self() ;
        }

        return self::$instance;
    }


    public function getUserIP()
    {
        foreach (array('HTTP_LSWCDN_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }

                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return null;
    }

    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if(strpos($ip,',')!==false) {
            $ip = explode(',', $ip);
            $ip= trim($ip[0]);
        }

        return $ip;
    }

    public function isJSON($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    public function getUniqueClientKey(){
        global $_SERVER;
        $data = [];
        array_push($data, $this->IsSetReturn($_SERVER, 'REMOTE_ADDR', ''));
        array_push($data, $this->IsSetReturn($_SERVER, 'HTTP_CLIENT_IP', ''));
        array_push($data, $this->IsSetReturn($_SERVER, 'HTTP_X_FORWARDED_FOR', ''));
        array_push($data, $this->IsSetReturn($_SERVER, "HTTP_USER_AGENT", "")); /* Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36 */
        array_push($data, $this->IsSetReturn($_SERVER, "HTTP_ACCEPT_LANGUAGE", "")); /* en,ru;q=0.9,ru-RU;q=0.8,en-US;q=0.7,ar;q=0.6 */
        array_push($data, $this->IsSetReturn($_SERVER, "HTTP_COOKIE", "")); /* _ga=GA1.1.764635755.1578300008;  */
        return md5(implode('',$data));
    }


    private function IsSetReturn($arr, $key, $default = null) {
        if(array_key_exists($key, $arr)) {
            return $arr[$key];
        } else {
            return $default;
        }
    }

    public function ext2mime($ext) {
        $mime_map = [
            '3g2'		=>'video/3gpp2',
            '3gp'		=>'video/3gp',
            '3gp'		=>'video/3gpp',
            '7zip'		=>'application/x-compressed',
            'aac'		=>'audio/x-acc',
            'ac3'		=>'audio/ac3',
            'ai'		=>'application/postscript',
            'aif'		=>'audio/x-aiff',
            'aif'		=>'audio/aiff',
            'au'		=>'audio/x-au',
            'avi'		=>'video/x-msvideo',
            'avi'		=>'video/msvideo',
            'avi'		=>'video/avi',
            'avi'		=>'application/x-troff-msvideo',
            'bin'		=>'application/macbinary',
            'bin'		=>'application/mac-binary',
            'bin'		=>'application/x-binary',
            'bin'		=>'application/x-macbinary',
            'bmp'		=>'image/bmp',
            'bmp'		=>'image/x-bmp',
            'bmp'		=>'image/x-bitmap',
            'bmp'		=>'image/x-xbitmap',
            'bmp'		=>'image/x-win-bitmap',
            'bmp'		=>'image/x-windows-bmp',
            'bmp'		=>'image/ms-bmp',
            'bmp'		=>'image/x-ms-bmp',
            'bmp'		=>'application/bmp',
            'bmp'		=>'application/x-bmp',
            'bmp'		=>'application/x-win-bitmap',
            'cdr'		=>'application/cdr',
            'cdr'		=>'application/coreldraw',
            'cdr'		=>'application/x-cdr',
            'cdr'		=>'application/x-coreldraw',
            'cdr'		=>'image/cdr',
            'cdr'		=>'image/x-cdr',
            'cdr'		=>'zz-application/zz-winassoc-cdr',
            'cpt'		=>'application/mac-compactpro',
            'crl'		=>'application/pkix-crl',
            'crl'		=>'application/pkcs-crl',
            'crt'		=>'application/x-x509-ca-cert',
            'crt'		=>'application/pkix-cert',
            'css'		=>'text/css',
            'csv'		=>'text/x-comma-separated-values',
            'csv'		=>'text/comma-separated-values',
            'csv'		=>'application/vnd.msexcel',
            'dcr'		=>'application/x-director',
            'docx'		=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dvi'		=>'application/x-dvi',
            'eml'		=>'message/rfc822',
            'exe'		=>'application/x-msdownload',
            'f4v'		=>'video/x-f4v',
            'flac'		=>'audio/x-flac',
            'flv'		=>'video/x-flv',
            'gif'		=>'image/gif',
            'gpg'		=>'application/gpg-keys',
            'gtar'		=>'application/x-gtar',
            'gzip'		=>'application/x-gzip',
            'hqx'		=>'application/mac-binhex40',
            'hqx'		=>'application/mac-binhex',
            'hqx'		=>'application/x-binhex40',
            'hqx'		=>'application/x-mac-binhex40',
            'html'		=>'text/html',
            'ico'		=>'image/x-icon',
            'ico'		=>'image/x-ico',
            'ico'		=>'image/vnd.microsoft.icon',
            'ics'		=>'text/calendar',
            'jar'		=>'application/java-archive',
            'jar'		=>'application/x-java-application',
            'jar'		=>'application/x-jar',
            'jp2'		=>'image/jp2',
            'jp2'		=>'video/mj2',
            'jp2'		=>'image/jpx',
            'jp2'		=>'image/jpm',
            'jpeg'		=>'image/jpeg',
            'jpeg'		=>'image/pjpeg',
            'js'		=>'application/x-javascript',
            'json'		=>'application/json',
            'json'		=>'text/json',
            'kml'		=>'application/vnd.google-earth.kml+xml',
            'kmz'		=>'application/vnd.google-earth.kmz',
            'log'		=>'text/x-log',
            'm4a'		=>'audio/x-m4a',
            'm4a'		=>'audio/mp4',
            'm4u'		=>'application/vnd.mpegurl',
            'mid'		=>'audio/midi',
            'mif'		=>'application/vnd.mif',
            'mov'		=>'video/quicktime',
            'movie'		=>'video/x-sgi-movie',
            'mp3'		=>'audio/mpeg',
            'mp3'		=>'audio/mpg',
            'mp3'		=>'audio/mpeg3',
            'mp3'		=>'audio/mp3',
            'mp4'		=>'video/mp4',
            'mpeg'		=>'video/mpeg',
            'oda'		=>'application/oda',
            'ogg'		=>'audio/ogg',
            'ogg'		=>'video/ogg',
            'ogg'		=>'application/ogg',
            'otf'		=>'font/otf',
            'p10'		=>'application/x-pkcs10',
            'p10'		=>'application/pkcs10',
            'p12'		=>'application/x-pkcs12',
            'p7a'		=>'application/x-pkcs7-signature',
            'p7c'		=>'application/pkcs7-mime',
            'p7c'		=>'application/x-pkcs7-mime',
            'p7r'		=>'application/x-pkcs7-certreqresp',
            'p7s'		=>'application/pkcs7-signature',
            'pdf'		=>'application/pdf',
            'pdf'		=>'application/octet-stream',
            'pem'		=>'application/x-x509-user-cert',
            'pem'		=>'application/x-pem-file',
            'pgp'		=>'application/pgp',
            'php'		=>'application/x-httpd-php',
            'php'		=>'application/php',
            'php'		=>'application/x-php',
            'php'		=>'text/php',
            'php'		=>'text/x-php',
            'php'		=>'application/x-httpd-php-source',
            'png'		=>'image/png',
            'png'		=>'image/x-png',
            'ppt'		=>'application/powerpoint',
            'ppt'		=>'application/vnd.ms-powerpoint',
            'ppt'		=>'application/vnd.ms-office',
            'ppt'		=>'application/msword',
            'pptx'		=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'psd'		=>'application/x-photoshop',
            'psd'		=>'image/vnd.adobe.photoshop',
            'ra'		=>'audio/x-realaudio',
            'ram'		=>'audio/x-pn-realaudio',
            'rar'		=>'application/x-rar',
            'rar'		=>'application/rar',
            'rar'		=>'application/x-rar-compressed',
            'rpm'		=>'audio/x-pn-realaudio-plugin',
            'rsa'		=>'application/x-pkcs7',
            'rtf'		=>'text/rtf',
            'rtx'		=>'text/richtext',
            'rv'		=>'video/vnd.rn-realvideo',
            'sit'		=>'application/x-stuffit',
            'smil'		=>'application/smil',
            'srt'		=>'text/srt',
            'svg'		=>'image/svg+xml',
            'swf'		=>'application/x-shockwave-flash',
            'tar'		=>'application/x-tar',
            'tgz'		=>'application/x-gzip-compressed',
            'tiff'		=>'image/tiff',
            'ttf'		=>'font/ttf',
            'txt'		=>'text/plain',
            'vcf'		=>'text/x-vcard',
            'vlc'		=>'application/videolan',
            'vtt'		=>'text/vtt',
            'wav'		=>'audio/x-wav',
            'wav'		=>'audio/wave',
            'wav'		=>'audio/wav',
            'wbxml'		=>'application/wbxml',
            'webm'		=>'video/webm',
            'webp'		=>'image/webp',
            'wma'		=>'audio/x-ms-wma',
            'wmlc'		=>'application/wmlc',
            'wmv'		=>'video/x-ms-wmv',
            'wmv'		=>'video/x-ms-asf',
            'woff'		=>'font/woff',
            'woff2'		=>'font/woff2',
            'xhtml'		=>'application/xhtml+xml',
            'xl'		=>'application/excel',
            'xls'		=>'application/msexcel',
            'xls'		=>'application/x-msexcel',
            'xls'		=>'application/x-ms-excel',
            'xls'		=>'application/x-excel',
            'xls'		=>'application/x-dos_ms_excel',
            'xls'		=>'application/xls',
            'xls'		=>'application/x-xls',
            'xlsx'		=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsx'		=>'application/vnd.ms-excel',
            'xml'		=>'application/xml',
            'xml'		=>'text/xml',
            'xsl'		=>'text/xsl',
            'xspf'		=>'application/xspf+xml',
            'z'		=>'application/x-compress',
            'zip'		=>'application/x-zip',
            'zip'		=>'application/zip',
            'zip'		=>'application/x-zip-compressed',
            'zip'		=>'application/s-compressed',
            'zip'		=>'multipart/x-zip',
            'zsh'		=>'text/x-scriptzsh',
        ];
        return isset($mime_map[$ext]) ? $mime_map[$ext] : false;
    }

    public function mime2ext($mime) {
        $mime_map = [
            'video/3gpp2'                                                               => '3g2',
            'video/3gp'                                                                 => '3gp',
            'video/3gpp'                                                                => '3gp',
            'application/x-compressed'                                                  => '7zip',
            'audio/x-acc'                                                               => 'aac',
            'audio/ac3'                                                                 => 'ac3',
            'application/postscript'                                                    => 'ai',
            'audio/x-aiff'                                                              => 'aif',
            'audio/aiff'                                                                => 'aif',
            'audio/x-au'                                                                => 'au',
            'video/x-msvideo'                                                           => 'avi',
            'video/msvideo'                                                             => 'avi',
            'video/avi'                                                                 => 'avi',
            'application/x-troff-msvideo'                                               => 'avi',
            'application/macbinary'                                                     => 'bin',
            'application/mac-binary'                                                    => 'bin',
            'application/x-binary'                                                      => 'bin',
            'application/x-macbinary'                                                   => 'bin',
            'image/bmp'                                                                 => 'bmp',
            'image/x-bmp'                                                               => 'bmp',
            'image/x-bitmap'                                                            => 'bmp',
            'image/x-xbitmap'                                                           => 'bmp',
            'image/x-win-bitmap'                                                        => 'bmp',
            'image/x-windows-bmp'                                                       => 'bmp',
            'image/ms-bmp'                                                              => 'bmp',
            'image/x-ms-bmp'                                                            => 'bmp',
            'application/bmp'                                                           => 'bmp',
            'application/x-bmp'                                                         => 'bmp',
            'application/x-win-bitmap'                                                  => 'bmp',
            'application/cdr'                                                           => 'cdr',
            'application/coreldraw'                                                     => 'cdr',
            'application/x-cdr'                                                         => 'cdr',
            'application/x-coreldraw'                                                   => 'cdr',
            'image/cdr'                                                                 => 'cdr',
            'image/x-cdr'                                                               => 'cdr',
            'zz-application/zz-winassoc-cdr'                                            => 'cdr',
            'application/mac-compactpro'                                                => 'cpt',
            'application/pkix-crl'                                                      => 'crl',
            'application/pkcs-crl'                                                      => 'crl',
            'application/x-x509-ca-cert'                                                => 'crt',
            'application/pkix-cert'                                                     => 'crt',
            'text/css'                                                                  => 'css',
            'text/x-comma-separated-values'                                             => 'csv',
            'text/comma-separated-values'                                               => 'csv',
            'application/vnd.msexcel'                                                   => 'csv',
            'application/x-director'                                                    => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'application/x-dvi'                                                         => 'dvi',
            'message/rfc822'                                                            => 'eml',
            'application/x-msdownload'                                                  => 'exe',
            'video/x-f4v'                                                               => 'f4v',
            'audio/x-flac'                                                              => 'flac',
            'video/x-flv'                                                               => 'flv',
            'image/gif'                                                                 => 'gif',
            'application/gpg-keys'                                                      => 'gpg',
            'application/x-gtar'                                                        => 'gtar',
            'application/x-gzip'                                                        => 'gzip',
            'application/mac-binhex40'                                                  => 'hqx',
            'application/mac-binhex'                                                    => 'hqx',
            'application/x-binhex40'                                                    => 'hqx',
            'application/x-mac-binhex40'                                                => 'hqx',
            'text/html'                                                                 => 'html',
            'image/x-icon'                                                              => 'ico',
            'image/x-ico'                                                               => 'ico',
            'image/vnd.microsoft.icon'                                                  => 'ico',
            'text/calendar'                                                             => 'ics',
            'application/java-archive'                                                  => 'jar',
            'application/x-java-application'                                            => 'jar',
            'application/x-jar'                                                         => 'jar',
            'image/jp2'                                                                 => 'jp2',
            'video/mj2'                                                                 => 'jp2',
            'image/jpx'                                                                 => 'jp2',
            'image/jpm'                                                                 => 'jp2',
            'image/jpeg'                                                                => 'jpeg',
            'image/pjpeg'                                                               => 'jpeg',
            'application/x-javascript'                                                  => 'js',
            'application/json'                                                          => 'json',
            'text/json'                                                                 => 'json',
            'application/vnd.google-earth.kml+xml'                                      => 'kml',
            'application/vnd.google-earth.kmz'                                          => 'kmz',
            'text/x-log'                                                                => 'log',
            'audio/x-m4a'                                                               => 'm4a',
            'audio/mp4'                                                                 => 'm4a',
            'application/vnd.mpegurl'                                                   => 'm4u',
            'audio/midi'                                                                => 'mid',
            'application/vnd.mif'                                                       => 'mif',
            'video/quicktime'                                                           => 'mov',
            'video/x-sgi-movie'                                                         => 'movie',
            'audio/mpeg'                                                                => 'mp3',
            'audio/mpg'                                                                 => 'mp3',
            'audio/mpeg3'                                                               => 'mp3',
            'audio/mp3'                                                                 => 'mp3',
            'video/mp4'                                                                 => 'mp4',
            'video/mpeg'                                                                => 'mpeg',
            'application/oda'                                                           => 'oda',
            'audio/ogg'                                                                 => 'ogg',
            'video/ogg'                                                                 => 'ogg',
            'application/ogg'                                                           => 'ogg',
            'font/otf'                                                                  => 'otf',
            'application/x-pkcs10'                                                      => 'p10',
            'application/pkcs10'                                                        => 'p10',
            'application/x-pkcs12'                                                      => 'p12',
            'application/x-pkcs7-signature'                                             => 'p7a',
            'application/pkcs7-mime'                                                    => 'p7c',
            'application/x-pkcs7-mime'                                                  => 'p7c',
            'application/x-pkcs7-certreqresp'                                           => 'p7r',
            'application/pkcs7-signature'                                               => 'p7s',
            'application/pdf'                                                           => 'pdf',
            'application/octet-stream'                                                  => 'pdf',
            'application/x-x509-user-cert'                                              => 'pem',
            'application/x-pem-file'                                                    => 'pem',
            'application/pgp'                                                           => 'pgp',
            'application/x-httpd-php'                                                   => 'php',
            'application/php'                                                           => 'php',
            'application/x-php'                                                         => 'php',
            'text/php'                                                                  => 'php',
            'text/x-php'                                                                => 'php',
            'application/x-httpd-php-source'                                            => 'php',
            'image/png'                                                                 => 'png',
            'image/x-png'                                                               => 'png',
            'application/powerpoint'                                                    => 'ppt',
            'application/vnd.ms-powerpoint'                                             => 'ppt',
            'application/vnd.ms-office'                                                 => 'ppt',
            'application/msword'                                                        => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop'                                                   => 'psd',
            'image/vnd.adobe.photoshop'                                                 => 'psd',
            'audio/x-realaudio'                                                         => 'ra',
            'audio/x-pn-realaudio'                                                      => 'ram',
            'application/x-rar'                                                         => 'rar',
            'application/rar'                                                           => 'rar',
            'application/x-rar-compressed'                                              => 'rar',
            'audio/x-pn-realaudio-plugin'                                               => 'rpm',
            'application/x-pkcs7'                                                       => 'rsa',
            'text/rtf'                                                                  => 'rtf',
            'text/richtext'                                                             => 'rtx',
            'video/vnd.rn-realvideo'                                                    => 'rv',
            'application/x-stuffit'                                                     => 'sit',
            'application/smil'                                                          => 'smil',
            'text/srt'                                                                  => 'srt',
            'image/svg+xml'                                                             => 'svg',
            'application/x-shockwave-flash'                                             => 'swf',
            'application/x-tar'                                                         => 'tar',
            'application/x-gzip-compressed'                                             => 'tgz',
            'image/tiff'                                                                => 'tiff',
            'font/ttf'                                                                  => 'ttf',
            'text/plain'                                                                => 'txt',
            'text/x-vcard'                                                              => 'vcf',
            'application/videolan'                                                      => 'vlc',
            'text/vtt'                                                                  => 'vtt',
            'audio/x-wav'                                                               => 'wav',
            'audio/wave'                                                                => 'wav',
            'audio/wav'                                                                 => 'wav',
            'application/wbxml'                                                         => 'wbxml',
            'video/webm'                                                                => 'webm',
            'image/webp'                                                                => 'webp',
            'audio/x-ms-wma'                                                            => 'wma',
            'application/wmlc'                                                          => 'wmlc',
            'video/x-ms-wmv'                                                            => 'wmv',
            'video/x-ms-asf'                                                            => 'wmv',
            'font/woff'                                                                 => 'woff',
            'font/woff2'                                                                => 'woff2',
            'application/xhtml+xml'                                                     => 'xhtml',
            'application/excel'                                                         => 'xl',
            'application/msexcel'                                                       => 'xls',
            'application/x-msexcel'                                                     => 'xls',
            'application/x-ms-excel'                                                    => 'xls',
            'application/x-excel'                                                       => 'xls',
            'application/x-dos_ms_excel'                                                => 'xls',
            'application/xls'                                                           => 'xls',
            'application/x-xls'                                                         => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.ms-excel'                                                  => 'xlsx',
            'application/xml'                                                           => 'xml',
            'text/xml'                                                                  => 'xml',
            'text/xsl'                                                                  => 'xsl',
            'application/xspf+xml'                                                      => 'xspf',
            'application/x-compress'                                                    => 'z',
            'application/x-zip'                                                         => 'zip',
            'application/zip'                                                           => 'zip',
            'application/x-zip-compressed'                                              => 'zip',
            'application/s-compressed'                                                  => 'zip',
            'multipart/x-zip'                                                           => 'zip',
            'text/x-scriptzsh'                                                          => 'zsh',
        ];

        return isset($mime_map[$mime]) ? $mime_map[$mime] : false;
    }



}
