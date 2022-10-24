<?php

namespace modules\bee_invasion\v1\api\com;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\param\DataBox;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\com\AddOrderDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;


class ActionAdd extends ActionBase
{
    public function run()
    {
        $table_name   = $this->inputDataBox->getStringNotNull('table_name');
        $project_name = $this->inputDataBox->getStringNotNull('project_name');
        $id           = $this->inputDataBox->getIntNotNull('id');

        if ($project_name === 'duck_time')
        {
            $now_ts = time();
            $param  = [
                'proj_table_name' => $table_name,
                'proj_data_id'    => $id
            ];
            $secret = 'edadb5d487ae304128117e797f07';
            $json   = json_encode($param);
            $md5    = md5($json . $now_ts . $secret);

            //https://duck-time.dev.aiqingyinghang.com:2023/api/get_com_data?sign=8048b723edb1113a55a6f090f2a10afd&timeStamp=1662630947
            list($http_code, $res) = (new Curl())->post2(Sys::app()->params['com_project_api']['duck_time'] . "/api/get_com_data?sign={$md5}&timeStamp={$now_ts}", $json, true, 2000, 2000, Curl::application_json);
            $com_order                  = new AddOrderDao();
            $com_order->project_name    = $project_name;
            $com_order->proj_table_name = $table_name;
            $com_order->proj_data_id    = $id;
            $com_order->order_info      = $res['data'];
            Sys::app()->addLog($res);
            $insert_res = $com_order->insert(false);
            if (!$insert_res)
            {
                $com_order = AddOrderDao::model()->findOneByWhere(['project_name' => $project_name, 'proj_table_name' => $table_name, 'proj_data_id' => $id,]);
            }
            if ($com_order->is_add === Opt::YES || $com_order->is_err === Opt::YES)
            {
                return ['companyOrder' => $com_order->getOuterDataArray()];
            }
            if (isset($res['data']['order_info']))
            {
                //{"tel":18101033230,"open_id":"2022071817100010000","item_class":"currency","item_code":"vip_channel_ticket","amount":2}
                $order_info   = json_decode($res['data']['order_info'], true);
                $info_databox = new DataBox($order_info);
                $open_user_id = $info_databox->tryGetString('open_id');
                $tel          = $info_databox->tryGetString('tel');
                $item_class   = $info_databox->tryGetString('item_class');
                $item_code    = $info_databox->tryGetString('item_code');
                $amount       = $info_databox->tryGetInt('amount');
                if ($open_user_id && $tel && $item_class && $item_code && $amount)
                {
                    $amount = $amount * pow(10, 8);
                    if (empty($com_order->user_id))
                    {
                        $com_order->user_id = User::openId2TrueId($open_user_id);
                        $user               = User::model()->findByPk($com_order->user_id, false);
                        if (empty($user))
                        {
                            $com_order->is_err = Opt::YES;
                            $com_order->remark = '用户不存在';
                        }

                        $com_order->item_class  = $item_class;
                        $com_order->item_code   = $item_code;
                        $com_order->item_amount = $amount;
                        $com_order->update(false);
                    }
                    else
                    {
                        $user = User::model()->findByPk($com_order->user_id, false);
                    }

                    if ($item_code === 'vip_channel_ticket')
                    {
                        $src_id       = "{$project_name}.{$table_name}.{$id}";
                        $user_account = UserCurrency::model()->setUser($user)->getAccount($item_code);
                        $goods_his    = (new UserCurrencyHis())->setUserAccountModel($user_account)->setOperationStep(1);
                        ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $user->id], time());
                        $user_account->verifyKeyProperties();
                        $goods_his->setOperationStep(1);
                        $goods_record_res = $goods_his->tryRecord(UserCurrencyHis::srcComAdd, $src_id, $amount);
                        if ($goods_record_res === false)
                        {
                            return ['companyOrder' => $com_order->getOuterDataArray()];
                        }
                        else
                        {
                            $com_order->is_add = Opt::YES;
                            $com_order->update(false);
                        }
                    }
                }
                else
                {
                    $com_order->is_err = Opt::YES;
                    $com_order->remark = 'order_info 不完善';
                }
            }
            else
            {
                $com_order->is_err = Opt::YES;
                $com_order->remark = '没有order_info';
            }
            return ['companyOrder' => $com_order->getOuterDataArray()];
        }
        else
        {
            return false;
        }


    }

}