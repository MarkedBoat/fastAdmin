#!/bin/bash
cmd_git_pull="git pull origin master"
username="1214834686@qq.com"
password="(0Mlovenet";

input_str=$1
hammer_key="hammer"
duck_key="duck";
input1=""
if [[ $input_str == *$duck_key* ]]
then

	input1="duck"
else
	if read -t 5 -n1 -p "是否更新 鸭鸭时代 -请输入 [ Y| N ]"  is_goon
	then
		echo "input:[ ${is_goon} ]"
		case $is_goon in
			Y|y|yes) echo "ok"
				input1="duck"
			;;
			N|n|no) echo "no...."
			;;
			$'\e') echo "Escape menus no"
			;;
			*) echo "不是Y|N ,默认自动更新"
				input1="duck"
		esac

	else
		echo "timeout for ducktime"
	fi
fi

input2=""
if [[ $input_str == *$hammer_key* ]]
then

	input2="hammer"
else

	if read -t 5 -n1 -p "是否更新 hammer 请输入 [ Y| N ]"  is_hammer_goon
	then
		echo "input:[ ${is_hammer_goon} ]"
		case $is_hammer_goon in
			Y|y|yes) echo "ok"
				input2="hammer"
			;;
			N|n|no) echo "no...."
			;;
			$'\e') echo "Escape menus no"
			;;

			*) echo "不是Y|N ,默认自动更新"
				input2="hammer"
		esac

	else
		echo "timeout for hammer"
	fi
fi

echo ""
echo ""
echo ""
echo ""
echo "************************************************"
echo " 鸭鸭时代 代码更新"
echo "************************************************"

#if [[ $input_str == *$duck_key* ]]
if [[ $input1 == *$duck_key* ]]
then
    echo "update:鸭鸭时代 代码更新"
    echo ""
    cd /www/wwwroot/duck-time-backend/
expect <<EOF
      spawn $cmd_git_pull;
      expect "Username"
      send "$username\r"
      expect	"Password\r"
      send "$password\r"
      expect eof;
EOF
else
    echo "不包含 duck_time,不更新"
fi

echo ""
echo ""
echo ""
echo ""


echo ""
echo ""
echo ""
echo "************************************************"
echo " Hammmer 代码更新"
echo "************************************************"
if [[ $input2 == *$hammer_key* ]]
then
    echo "update:hammer 代码更新"
    echo ""
    cd /www/wwwroot/hammer-for-lovenet/
expect <<EOF
      spawn $cmd_git_pull;
      expect "Username"
      send "$username\r"
      expect	"Password\r"
      send "$password\r"
      expect eof;
EOF
else
    echo "不包含 hammer,不更新"
fi
echo ""
echo ""
echo ""