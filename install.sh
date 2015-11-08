#!/bin/sh
#

exerr () { echo -e "$*" >&2 ; exit 1; }
. /etc/rc.subr
. /etc/configxml.subr
. /etc/util.subr

MYPATH=$1
name='minidlna'
STARTDIR=`pwd`
REVISION=`cat /etc/prd.revision`
INSTALLED=`/usr/local/bin/xml sel -t -i "count(//minidlna) > 0" -o "1" --else -o "0" -b /conf/config.xml`
# This first checks to see that the user has supplied an argument
if [ "0" != ${INSTALLED} ]; then
	MINIDLNA_HOME=`configxml_get "//${name}/homefolder"`
else
	if [ ! -z ${MYPATH} ]; then
			# The first argument will be the path that the user wants to be the root folder.
			# If this directory does not exist, it is created
			MINIDLNA_HOME=${MYPATH}
				# This checks if the supplied argument is a directory. If it is not
				# then we will try to create it
			if [ ! -d ${MINIDLNA_HOME} ]; then
				echo "Attempting to create a new destination directory....."
				mkdir -p ${MINIDLNA_HOME} || exerr "ERROR: Could not create directory!"
			fi
	else
	# We are here because the user did not specify an alternate location. Thus, we should use the 
	# current directory as the root.
		MINIDLNA_HOME=${STARTDIR} 
	fi
fi

# Make and move into the install staging folder
mkdir -p ${STARTDIR}/install_stage || exerr "ERROR: Could not create staging directory!"
cd ${STARTDIR}/install_stage || exerr "ERROR: Could not access staging directory!"
# Fetch the simple branch as a zip file
echo "Retrieving the most recent stable version of "${name}
fetch https://github.com/alexey1234/minidlna-nas4free/archive/simple.zip || exerr "ERROR: Could not write to install directory!"

# Extract the files we want, stripping the leading directory, and exclude
# the git nonsense
echo "Unpacking the tarball..."
tar -xf simple.zip --exclude='.git*' --strip-components 1
echo "Done!"
rm simple.zip
# Copy downloaded version to the install destination
rsync -r ${STARTDIR}/install_stage/* ${MINIDLNA_HOME}/
echo "Installing..."
		# Create the symlinks/schema. We can't use thebrig_start since
		# there is nothing for the brig in the config XML
mkdir -p /usr/local/www/ext/minidlna
cp -f ${MINIDLNA_HOME}/ext/minidlna/menu.inc /usr/local/www/ext/minidlna/menu.inc

if [ ! -h "/usr/local/www/extensions_minidlna_config.php" ]; then
			ln -s  ${MINIDLNA_HOME}/ext/minidlna/extensions_minidlna_config.php /usr/local/www/extensions_minidlna_config.php
fi
# Store the install destination into the /tmp/minidlna.install in case updates
	if [ "1" != ${INSTALLED} ]; then
		echo ${MINIDLNA_HOME} > /tmp/minidlna.install		
		echo "Congratulations! Extension was installed. Navigate to rudimentary config tab and push Save."
	else
		echo "Congratulations! Extension was upgraded."
	fi
cd $STARTDIR
# Get rid of staged updates & cleanup
rm -rf $STARTDIR/install_stage

