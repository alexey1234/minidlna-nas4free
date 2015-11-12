#!/bin/sh

#
# PROVIDE: minidlna
# REQUIRE: LOGIN
# KEYWORD: shutdown
# XQUERY: -i "count(//minidlna/enable) > 0" -o "0" -b
# RCVAR: minidlna

. /etc/rc.subr
. /etc/configxml.subr
. /etc/util.subr

name=minidlna
rcvar=${name}_enable
load_rc_config "${name}"
minidlna_uid=${minidlna_uid-"dlna"}
minidlna_enable=${minidlna_enable:="NO"}
homefolder=`configxml_get "//${name}/homefolder"`
minidlna_config_dir="/var/etc"
scanner_indicator="/var/run/${name}/upnp-av.scan"
minidlna_config=${minidlna_config_dir}/${name}.conf
minidlna_logdir=${minidlna_logdir-"/var/log"}

#Commands 
command=/usr/local/sbin/${name}d
mkconf_cmd="minidlna_mkconf"
start_precmd="minidlna_prestart"

stop_postcmd="minidlna_poststop"
rescan_cmd="minidlna_rescan"
extra_commands="mkconf rescan"

pidfile="/var/run/${name}/${name}.pid"
command_args=" -P $pidfile -u $minidlna_uid -f $minidlna_config"

minidlna_mkconf()
{

		_name=`configxml_get "//minidlna/name"`
		_if=`configxml_get "//minidlna/if"`
		_port=`configxml_get "//minidlna/port"`
		_serial=`minidlnad -V | awk '{print$2}'`
		_model=`cat /etc/prd.revision`
		_container=`configxml_get "//minidlna/container"`
		_notifyinterval=`configxml_get "//minidlna/notify_int"`
		_loglevel=`configxml_get "//minidlna/loglevel"`
		_ip_adress=`configxml_get "//interfaces/lan/ipaddr"`
		if [ "${_ip_adress}" = "dhcp" ]; then
			_ip_adress=`get_ipaddr inet ${_if}`
		fi

	cat << EOF > ${minidlna_config}
friendly_name=${_name}
network_interface=${_if}
port=${_port}
serial=${_serial}
model_number=${_model}
notify_interval=${_notifyinterval}
db_dir=${homefolder}/db
log_dir=${minidlna_logdir}
root_container=${_container}
log_level=general,artwork,database,inotify,scanner,metadata,http,ssdp,tivo=${_loglevel}
album_art_names=Cover.jpg/cover.jpg/AlbumArtSmall.jpg/albumartsmall.jpg/AlbumArt.jpg/albumart.jpg/Album.jpg/album.jpg/Folder.jpg/folder.jpg/Thumb.jpg/thumb.jpg
minissdpdsocket=/var/run/minissdpd.sock
presentation_url=http://${_ip_adress}:${_port}/index.php
EOF

	xml sel -t \
		-i "count(//minidlna/tivo) > 0" -o "enable_tivo=yes" --else -o "enable_tivo=no" -b -n \
		      ${configxml_file} | /usr/local/bin/xml unesc >> ${minidlna_config}
	xml sel -t \
		-i "count(//minidlna/strict) > 0" -o "strict_dlna=yes" --else -o "strict_dlna=no" -b -n \
		      ${configxml_file} | /usr/local/bin/xml unesc >> ${minidlna_config}
	/usr/local/bin/xml sel -t -m "//minidlna/content" \
		-o "media_dir=" -v "." -n \
		      ${configxml_file} | /usr/local/bin/xml unesc >> ${minidlna_config}
  xml sel -t \
		-i "count(//minidlna/inotify) > 0" -o "inotify=yes" --else -o "inotify=no" -b -n \
		      ${configxml_file} | /usr/local/bin/xml unesc >> ${minidlna_config}
}

minidlna_prestart()
{
minidlna_mkconf
NETSTATCHECK=`netstat -rn | grep 224.0.0.0 |wc -m`
if [ $NETSTATCHECK -gt 5 ]; 
	then 
	/sbin/route -q delete 224.0.0.0/4  >/dev/null 2>&1
fi	
/sbin/route add -net 239.0.0.0 -netmask 240.0.0.0 -interface ${_if} >/dev/null 2>&1
#install -d -o $minidlna_uid ${pidfile%/*} /var/db/minidlna
}
minidlna_poststop()
{
	/sbin/route delete 224.0.0.0/4 >/dev/null 2>&1
	rm -f $pidfile
}
minidlna_rescan()
{
PID=`cat $pidfile`
kill $PID
/sbin/route delete 224.0.0.0/4 >/dev/null 2>&1
rm -f $pidfile
if [ -f ${homefolder}/db/files.db ]; then
      rm -f ${homefolder}/db/files.db
fi
if [ -d ${homefolder}/db/art_cache ]; then
      rm -fr ${homefolder}/db/art_cache
fi
$command $command_args
logger "rescan minidlna"
sleep 5
wait_on -t 1800 $scanner_indicator
case $? in
		0)
		    logger "minidlna rescan timeout"
		    ;;
		1)
		    logger "minidlna rescan completed"
		    sleep 5
		    #clean wrong daemons		    
		    process=`ps ax | grep sbin/minidlna | grep -v grep | awk '{print$1}'`
		    kill -s KILL ${process}
		    minidlna_prestart
		    $command $command_args		   
		    ;;
esac
}

run_rc_command $1
