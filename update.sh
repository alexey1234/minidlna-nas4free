#!/bin/sh
#
# minidlna fast update binaries script
. /etc/rc.subr
. /etc/configxml.subr
homefolder=`configxml_get "//minidlna/homefolder"`
echo "Backup current binary"
mv  ${homefolder}bin/minidlna ${homefolder}bin/minidlna.bak
echo "Fetch new binary"
fetch -o ${homefolder}bin/minidlna https://sites.google.com/site/aganimkarmiel/home/free-time/file-sharing/minidlna-1.1.1_5_x64
chmod 755 ${homefolder}bin/minidlna
version=`minidlna -V`
echo "Updated " ${version}
