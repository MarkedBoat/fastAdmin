#!/bin/bash
username="1214834686@qq.com"
password="(0Mlovenet";
dir=$1
git_url=$2
http_key="https"

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

echo ""



cd $dir

if [[ $git_url == *$http_key* ]]
then
    echo "clone: ${git_url}"
expect <<EOF
      spawn git clone $git_url;
	      expect "Username"
      send "$username\r"
      expect	"Password\r"
      send "$password\r"
      expect eof;
EOF
    echo "代码已经clone 结束";
    exit
else
    echo "不是clone(${git_url})，继续 "
fi

echo ""


expect <<EOF
      spawn git fetch --all;
      expect {
        "Username" {
          send "$username\r";
          exp_continue
        }
        "Password" {
          send "$password\r";
          exp_continue
        }
      }
EOF
echo ""
echo "fetch ok"

expect <<EOF
      spawn git reset --hard origin/$git_url;
      expect {
        "Username" {
          send "$username\r";
          exp_continue
        }
        "Password" {
          send "$password\r";
          exp_continue
        }
      }
EOF



echo ""
echo "************************************************"
echo "ok "
echo "************************************************"
echo ""