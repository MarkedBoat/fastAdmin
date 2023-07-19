#!/bin/bash
cmd=$1
psw=$2
un=root
#echo "{$cmd}"
expect -c "
spawn $cmd;
expect {
\"yes\/no\" { send \"yes\r\"; exp_continue }
\"password\:\" { send \"$psw\r\"; exp_continue }
}
"
echo "ssh exec ok"


