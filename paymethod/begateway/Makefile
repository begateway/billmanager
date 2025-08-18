MGR = billmgr
PLUGIN = pmbegateway
BASE ?= /usr/local/mgr5

all:
	@echo "BeGateway: copy module files"
	cp -a include ${BASE}
	cp -a cgi ${BASE}
	cp -a etc ${BASE}
	cp -a paymethods ${BASE}
	cp -a skins ${BASE}
	chmod 755 ${BASE}/cgi/begatewaypayment.php
	chmod 755 ${BASE}/cgi/begatewaypayurl.php
	chmod 755 ${BASE}/paymethods/pmbegateway
	killall core
