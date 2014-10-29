#!/bin/sh
# scanner1
#

. /etc/rc.subr
. /etc/configxml.subr
. /etc/util.subr
errormessage="Usage: $0 [ start | stop | restart | status] [task]"

if [ -z "$1" ]; then
    echo ${errormessage}
    return 1
    exit
fi


name=$2
pidfile="/var/run/${name}.pid"
daemon_folder="/var/tmp"
command=${daemon_folder}/${name}
timeout1=`configxml_get "//minidlna/notifyint"`
timeout2=`configxml_get "//minidlna/notifyhold"`

prestart(){
cat << EOF > ${daemon_folder}/${name}
#!/bin/sh
#${name}
#
#Nas4free only specified

. /etc/rc.subr
. /etc/configxml.subr
. /etc/util.subr

homefolder=\`configxml_get "//minidlna/homefolder"\`
name="${name}"
file_types=".*\.(mpo|jpeg|png|jpg|mpg|mpeg|avi|divx|asf|wmv|mp4|m4v|mts|m2ts|m2t|mkv|vob|ts|flv|xvid|TiVo|mov|3gp|mp3|flac|wma|asf|fla|flc|m4a|aac|mp4|m4p|wav|ogg|pcm|3gp|m3u|pls|srt|smi)"
scanner_db_file=\${homefolder}db/\${name}.db


#first time create db for scanner
if [ ! -f \${scanner_db_file} ]; then 
	mediafolder=\`/usr/local/bin/xml sel -t -m "//upnp/content" -v "." -n ${configxml_file}\`
	echo \${mediafolder}
	for i in \${mediafolder}
		 do
			find -E \${i} -iregex \${file_types} >>\${scanner_db_file}
		done
fi



scanner_start()
{
  while :; do
    
	if [ ! -f "/var/run/\${name}.pid" ]; then
		sleep 1
		echo \$\$ > /var/run/\${name}.pid
	fi
	
	tempfile="/tmp/tmpfile"
	if [ -f "/tmp/tmpfile" ]; then 
		rm \${tempfile}
	fi
	mediafolder=\`/usr/local/bin/xml sel -t -m "//upnp/content" -v "." -n \${configxml_file}\`
	for i in \${mediafolder}
	do
		newfile=\`find -E \${i} -iregex \${file_types} >> \${tempfile}\`
	done
	current=\`cat \${tempfile} | wc -l | tr -d ' '\` 
	stored=\`cat \${scanner_db_file} | wc -l | tr -d ' '\`
	echo \${current} > /tmp/daemon.log
	if [ \${current} -ne \${stored} ]; then

		sleep ${timeout1}
		if [ -f \${scanner_db_file} ]; then
			  rm \${scanner_db_file}
		fi
		retval=\`cp \${tempfile} \${scanner_db_file}\`
		scanner_rebuild_db
		break;
	fi
	# this may be replaced to cycle organize
	sleep ${timeout2}
	sleep 2
done
}
scanner_rebuild_db()
{
#determinate which server call scanner
fuppespid=\`ps ax | grep fuppes | grep -v grep | awk '{print\$1}'\`
if [ -f "/var/run/minidlna.pid" ]; then
		/etc/rc.d/minidlna rescan
	elseif [-n \${fuppespid} ]
		/etc/rc.d/fuppes rebuilddb
	else 
		break;
fi

while :; do
# Here scanner wait when minidlna end scn-job
retval_l=\`tail - n 10 \${homefolder}mminidlna.log | grep playlists | grep -v grep\`
if [ -n \${retval} ]; then 
      break
fi
#Minidlna not answer for rescan few minutes after rescan. Value 60 need adjust
sleep 60
done
}

scanner_start

if [  -f "/var/run/\${name}.pid" ]; then
	rm "/var/run/\${name}.pid"
fi
EOF
chmod 755 ${daemon_folder}/${name}
return 0
}
killproc() {
   pid=`cat ${pidfile}`
   echo "Stopping $1 now."
   daemonpid=`ps ax | grep ${pid} | grep daemon | awk '{print$1}'`
   kill ${daemonpid}
   #[ "$pid" != "" ] && kill -15 ${pid}
   #rm ${pidfile}
   echo "Stopped"
   return 0
}
start() {
if [ -f ${pidfile} ]; then
      pid=`cat ${pidfile}`
      echo "${name} already running? (pid=${pid})."
      retval=1
       else
   prestart
   echo -n "Starting ${name} "
   daemon -fr -p ${pidfile} ${command}
   echo ""
   retval=0
fi   
}
# Start/stop processes 
case "$1" in
  'start')
  retval=0
  start
   return ${retval}
        ;;
  'stop')
    killproc ${name}
    rm -f ${command}
    return 0
     ;;
  'status')
	if [ -f ${pidfile} ]; then
		pid=`cat ${pidfile}`
		echo "Running with pid ${pid}"
		return 0
	else
		echo "${name} not running"
		return 1
	fi 
      ;;
   'restart')
    killproc ${name}
   start
    return 0
        ;;
    'mk')
    prestart
    return 0
     ;;
  *)
     echo "Usage: $0 [ start | stop | restart | status] [task]"
     ;;
esac