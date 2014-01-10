server.username = "www"
server.modules = (
  "mod_cgi",
  "mod_rewrite",
  "mod_redirect",
  "mod_alias"
 )
server.errorlog-use-syslog = "enable"
# debugmode ° debug.log-request-handling enable/disable
debug.log-request-handling = "disable"
server.event-handler = "freebsd-kqueue"
server.max-write-idle = 360
index-file.names   = ( "index.php", "index.html", "index.htm", "index.shtml", "default.htm" )
# mimetype mapping
mimetype.assign             = (
   ".spl"          =>      "application/futuresplash",
  ".class"        =>      "application/octet-stream",
  ".dvi"          =>      "application/x-dvi",
  ".gz"           =>      "application/x-gzip",
  ".swf"          =>      "application/x-shockwave-flash",
  ".mp3"          =>      "audio/mpeg",
  ".m3u"          =>      "audio/x-mpegurl",
  ".wma"          =>      "audio/x-ms-wma",
  ".wax"          =>      "audio/x-ms-wax",
  ".ogg"          =>      "application/ogg",
  ".wav"          =>      "audio/x-wav",
  ".gif"          =>      "image/gif",
  ".jar"          =>      "application/x-java-archive",
  ".jpg"          =>      "image/jpeg",
  ".jpeg"         =>      "image/jpeg",
  ".png"          =>      "image/png",
  ".svg"          =>      "image/svg+xml",
  ".xbm"          =>      "image/x-xbitmap",
  ".xpm"          =>      "image/x-xpixmap",
  ".xwd"          =>      "image/x-xwindowdump",
  ".css"          =>      "text/css",
  ".html"         =>      "text/html",
  ".htm"          =>      "text/html",
  ".js"           =>      "text/javascript",
  ".asc"          =>      "text/plain",
  ".conf"         =>      "text/plain",
  ".spec"         =>      "text/plain",
  ".dtd"          =>      "text/xml",
  ".xml"          =>      "text/xml",
  ".mp4"          =>      "video/mp4",
  ".mpg4"         =>      "video/mp4",
  ".mpeg"         =>      "video/mpeg",
  ".mpg"          =>      "video/mpeg",
  ".mov"          =>      "video/quicktime",
  ".qt"           =>      "video/quicktime",
  ".avi"          =>      "video/x-msvideo",
  ".asf"          =>      "video/x-ms-asf",
  ".asx"          =>      "video/x-ms-asf",
  ".wmv"          =>      "video/x-ms-wmv",
  # make the default mime type application/octet-stream.
  ""              =>      "application/octet-stream",
 )
url.access-deny = ( "~", ".inc", ".websrv_htpasswd" )
static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )
server.pid-file = "/var/run/web_dlna.pid"
cgi.assign = (".php" => "/usr/local/bin/php-cgi-websrv")
server.tag = "minidlna"
server.upload-dirs = ( "/var/tmp/ftmp" )
server.max-request-size = 2097152
