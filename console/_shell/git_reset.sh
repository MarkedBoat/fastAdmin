#!/bin/bash
cmd_git_pull="git pull origin master"
username="1214834686@qq.com"
password="(0Mlovenet";
userhome=yangjl
dir=/home/$userhome/codes/$1
echo "目标:${dir}"
if [ ! -x $dir ]
	then
      		echo "文件不存在${dir}"
	      	exit
	else
		echo "ok"
fi

echo ""
echo "************************************************"
echo " 预备更新 "
echo "************************************************"

cd $dir
expect <<EOF
      spawn git fetch --all;
      expect {
        "Username" {      send "$username\r";exp_continue}
        "Password" {      send "$password\r"      }
      }
EOF

expect <<EOF
      spawn git reset --hard origin/test;
      expect {
        "Username" {      send "$username\r";exp_continue}
        "Password" {      send "$password\r"      }
      }
EOF
