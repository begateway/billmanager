#!/bin/sh

[ -z "${TERM}" ] && export TERM=xterm

if [ ! -d /var/lib/mysql ]; then
	mkdir -p /var/lib/mysql
	mysql_install_db
	service mysql start
	mysqladmin create billmgr5
	test -z "${BILLMGR_PASSWD}" && exit 1
	#/usr/local/mgr5/sbin/mgrctl -m billmgr user.edit default_access_allow=on name=admin passwd=${BILLMGR_PASSWD} sok=ok
	echo "root:${BILLMGR_PASSWD}" | chpasswd
	service mysql stop
fi
if [ ! -f /root/.installed ]; then
	test -z "${BILLMGR_PASSWD}" && exit 1
	echo "root:${BILLMGR_PASSWD}" | chpasswd
	touch /root/.installed
fi

if [ -n "${FORWARDED_SECRET}" ]; then
	if grep -q "ForwardedSecret" ${CORE_DIR}/etc/billmgr.conf ; then
		sed -i -r "s/ForwardedSecret.*/ForwardedSecret ${FORWARDED_SECRET}/g" ${CORE_DIR}/etc/billmgr.conf
	else
		echo "ForwardedSecret ${FORWARDED_SECRET}" >> ${CORE_DIR}/etc/billmgr.conf
	fi
	${CORE_DIR}/sbin/mgrctl -m billmgr exit
fi

if [ -n "${DEV}" ]; then
  if [ ! -d ${CORE_DIR}/include/php/ ]; then
    mkdir -p ${CORE_DIR}/include/php/
    # symlink module files
    ln -s /usr/local/src/begateway/include/php/bill_util.php ${CORE_DIR}/include/php/bill_util.php
    ln -s /usr/local/src/begateway/paymethod/begateway/cgi/begatewaypayment.php ${CORE_DIR}/cgi/begatewaypayment.php
    ln -s /usr/local/src/begateway/paymethod/begateway/cgi/begatewaypayurl.php ${CORE_DIR}/cgi/begatewaypayurl.php
    ln -s /usr/local/src/begateway/paymethod/begateway/etc/xml/billmgr_mod_pmbegateway.xml ${CORE_DIR}/etc/xml/billmgr_mod_pmbegateway.xml
    ln -s /usr/local/src/begateway/paymethod/begateway/paymethods/pmbegateway ${CORE_DIR}/paymethods/pmbegateway
    cp /usr/local/src/begateway/paymethod/begateway/skins/common/plugin-logo/billmanager-plugin-pmbegateway.png ${CORE_DIR}/skins/common/plugin-logo/billmanager-plugin-pmbegateway.png
    cp /usr/local/src/begateway/paymethod/begateway/skins/client/img/payments/pmbegateway.svg ${CORE_DIR}/skins/client/img/payments/pmbegateway.svg
  fi
fi

func_TERM() {
	service cron stop
	service ihttpd stop
	service mysql stop
	exit 0
}

trap func_TERM TERM INT

case $1 in

	start|billmgr)
		service mysql start
		service cron start
		service ihttpd start

		shift
		exec $0 daemon
	;;
	daemon)
		while true; do
			sleep 10 &
			wait $!
		done
	;;
	*)
		exec $@
	;;
esac

exec "$@"
