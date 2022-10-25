<?php
/**
 *
 */

namespace console\bee_invasion\bonus;

use models\common\CmdBase;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\Curl;
use models\ext\tool\Printer;
use modules\bee_invasion\v1\dao\bonus\team\UserScoreDailyDao;
use modules\bee_invasion\v1\dao\bonus\team\UserScoreHisDao;
use modules\bee_invasion\v1\dao\bonus\team\UserTeamDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\admin\dbdata\DbStruct;
use modules\bee_invasion\v1\model\cache\ApiCache;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Currency;
use modules\bee_invasion\v1\model\economy\MObject;
use modules\bee_invasion\v1\model\economy\Order;
use modules\bee_invasion\v1\model\game\Config;
use modules\bee_invasion\v1\model\play\Equipment;
use modules\bee_invasion\v1\model\user\User;
use modules\bee_invasion\v1\model\user\UserCurrency;
use modules\bee_invasion\v1\model\user\UserCurrencyHis;

class CmdTeam extends CmdBase
{
    /** @var Printer */
    protected $printer;
    protected $score_levs      = [];
    protected $desc_score_levs = [];
    protected $bonus_items     = [];

    protected $db_part_id        = 0;
    protected $ymd               = 0;
    protected $statis_start_date = '';
    protected $statis_end_date   = '';

    const hisStepInit             = 0;
    const hisStepInitLevAndScore  = 1;
    const hisStepAddUpScoreAndLev = 3;
    const hisStepMatchBonusItem   = 5;
    const hisStepComputeBonus     = 7;
    const hisStepCountedDaily     = 9;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->printer = new Printer();
    }


    public function getScoreLevs()
    {
        if (empty($this->score_levs))
        {
            $dao = Config::model()->findOneByWhere(['item_code' => 'teamBonusLevs', 'is_ok' => Opt::isOk], false);
            if ($dao && $dao->setting['status'] === true)
            {
                foreach ($dao->setting['levs'] as $lev_info)
                {
                    $this->score_levs[$lev_info['lev']] = $lev_info['lev_score'][0] * pow(10, 8 - $lev_info['lev_score'][1]);
                }
                $this->desc_score_levs = array_reverse($this->score_levs, true);
            }
            $this->printer->tabEcho(var_export($this->score_levs, true));

        }
        return $this->score_levs;

    }


    public function getBonusItems()
    {
        if (empty($this->bonus_items))
        {
            $dao = Config::model()->findOneByWhere(['item_code' => 'teamBonusItems', 'is_ok' => Opt::isOk], false);
            if ($dao && $dao->setting['status'] === true)
            {
                $this->bonus_items = $dao->setting['items'];
            }

        }
        return $this->bonus_items;
    }

    public function getScoreLev($score)
    {
        if (empty($this->desc_score_levs))
        {
            $this->getScoreLevs();
        }
        if ($score < $this->score_levs[1])
        {
            return 0;
        }
        $matched_lev = 0;
        foreach ($this->desc_score_levs as $lev => $lev_score)
        {
            if ($score >= $lev_score)
            {
                $matched_lev = $lev;
                break;
            }
        }
        return $matched_lev;

    }


    public function initParam()
    {
        $ymd = $this->inputDataBox->tryGetInt('ymd');

        $ymd = $ymd ? $ymd : date('Ymd', time() - 86400);
        $this->initStatisYmd($ymd);
    }

    public function getOneDayOrderRows()
    {
        $order    = Order::model();
        $order_tn = $order->getTableName();
        $yes      = Opt::YES;
        $pay_code = 'gold_ingot';
        $sql      = "select id,user_id,order_sum from {$order_tn} where is_payed={$yes} and payed_time>='{$this->statis_start_date}' and payed_time<'{$this->statis_end_date}' and payment_code='{$pay_code}'  order by id asc limit 10000";
        echo "\n{$sql}\n";
        return $order->getDbConnect()->setText($sql)->queryAll();

    }

    /**
     * 获取用户上级
     * @param array $map
     * @param $user_ids
     * @param int $count_times
     * @throws \Exception
     */
    public function getUserUper(&$map, $user_ids, $count_times = 0)
    {
        $model = UserInviterDao::model();
        $tn    = $model->getTableName();

        $curr_rows = $model->getDbConnect()->batchQueryAll("select inviter_id,be_invited_id from {$tn} where be_invited_id in ({VAR}) and is_ok=1 ", $user_ids);
        if (count($curr_rows))
        {
            $new_user_ids = [];
            foreach ($curr_rows as $curr_row)
            {
                $id                                      = intval($curr_row['inviter_id']);
                $map[intval($curr_row['be_invited_id'])] = $id;
                $new_user_ids[]                          = $id;
            }
            $this->getUserUper($map, array_unique($new_user_ids), $count_times + 1);
        }
    }

    public function getUperPath($map, $user_id)
    {
        $path_array = [];
        $tmp_id     = $user_id;
        for ($i = 0; $i < 1000; $i++)
        {
            if (isset($map[$tmp_id]))
            {
                $tmp_id       = $map[$tmp_id];
                $path_array[] = $tmp_id;
            }
            else
            {
                break;
            }
        }
        return $path_array;
    }

    public function initStatisYmd($start_ymd)
    {
        $this->ymd               = $start_ymd;
        $this->statis_start_date = preg_replace('/(\d{4})(\d{2})(\d{2})/i', '$1-$2-$3 00:00:00', $this->ymd);
        $this->statis_end_date   = date('Y-m-d 00:00:00', strtotime($this->statis_start_date) + 86400);
        echo "\n{$start_ymd}  start:{$this->statis_start_date}  end:{$this->statis_end_date}\n";
    }

    public function countData()
    {
        $this->initParam();
        if ($this->inputDataBox->tryGetString('clear') === 'yes')
        {

            $this->printer->tabEcho('清理数据');
            $sql        = "SELECT op_step FROM bee_invade.dp_bonus_user_score_daily  where ymd={$this->ymd};";
            $daily_dao  = UserScoreDailyDao::model();
            $daily_rows = $daily_dao->getDbConnect()->setText($sql)->queryAll();
            $op_steps   = array_unique(array_column($daily_rows, 'op_step'));
            $tmp_count  = count($op_steps);
            $this->printer->tabEcho("op_steps " . json_encode($op_steps));
            if ($tmp_count === 0 || ($tmp_count === 1 && empty($op_steps[0])))
            {
                $sql1 = "DELETE FROM `bee_invade`.`dp_bonus_user_score_daily`  where ymd={$this->ymd} and op_step=0;";
                $daily_dao->getDbConnect()->setText($sql1)->execute();
                $sql2 = "DELETE FROM bee_invade.dp_bonus_user_score_his where ymd={$this->ymd};";
                $daily_dao->getDbConnect()->setText($sql2)->execute();


            }

        }
        $this->printer->newTabEcho('countDataimportOrder', '导入订单');
        $this->importOrder();
        $this->printer->endTabEcho('countDataimportOrder', '导入订单');


        $this->printer->newTabEcho('countDataaddUpPayOrder', '附加数据');
        $this->addUpPayOrder();
        $this->printer->endTabEcho('countDataaddUpPayOrder', '附加数据');


        $this->printer->newTabEcho('countDatamatchedBonus', '匹配收益挡位');
        $this->matchedBonus();
        $this->printer->endTabEcho('countDatamatchedBonus', '匹配收益挡位');


        $this->printer->newTabEcho('countDatacountDaily', '附加数据到天统计');
        $this->countDaily();
        $this->printer->endTabEcho('countDatacountDaily', '附加数据到天统计');

    }

    public function importOrder()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 填充关系树  start\n";


        $this->initParam();
        $order_rows = $this->getOneDayOrderRows();

        $this->printer->newTabEcho('foreach_orders', '开始循环订单');
        $pay_user_ids     = [];
        $pay_user_ids_map = [];
        foreach ($order_rows as $order_i => $order_row)
        {
            $user_id                    = intval($order_row['user_id']);
            $pay_user_ids[]             = $user_id;
            $pay_user_ids_map[$user_id] = $user_id;

        }
        $this->printer->endTabEcho('foreach_orders', '#');

        $this->printer->newTabEcho('make_user_team', '整理user_team/score');

        $pay_user_ids = array_unique($pay_user_ids);


        $this->printer->newTabEcho('creete_not_exist_user_team', '开始填充user_team/score');

        if (count($pay_user_ids_map))
        {
            $relat_map = [];
            $this->getUserUper($relat_map, $pay_user_ids_map, 0);
            $all_user_ids = array_merge(array_values($pay_user_ids_map), array_values($relat_map));
            foreach ($all_user_ids as $user_id)
            {
                $user_team             = UserTeamDao::model();
                $user_team->db_part_id = $this->db_part_id;
                $user_team->user_id    = $user_id;
                $user_team->pid_path   = json_encode($this->getUperPath($relat_map, $user_id));
                $user_team->score_sum  = 0;
                $user_team->lev        = 0;
                $user_team->setOnDuplicateKeyUpdate(['pid_path' => $user_team->pid_path]);
                $user_team->insert(false);
            }
        }
        $this->printer->endTabEcho('creete_not_exist_user_team', '结束填充user_team/score');
        $this->printer->endTabEcho('make_user_team', '整理user_team/score');

        /** @var UserTeamDao[] $pay_user_team_map */
        $pay_user_team_map = [];

        $pay_user_teams = UserTeamDao::model()->findAllByWhere(['db_part_id' => $this->db_part_id, 'user_id' => $pay_user_ids]);
        foreach ($pay_user_teams as $pay_user_team)
        {
            $pay_user_team_map[$pay_user_team->user_id] = $pay_user_team;
        }


        $this->printer->newTabEcho('fetch_orders_and_import', '遍历订单，准备填充upper path history');

        foreach ($order_rows as $order_i => $order_row)
        {
            $this->printer->newTabEcho('fetch_order_and_import', '遍历订单，准备填充upper path history' . json_encode($order_row, JSON_UNESCAPED_SLASHES));

            $pay_user_id = intval($order_row['user_id']);
            if (!isset($pay_user_team_map[$pay_user_id]))
            {
                die("\n Error: no_pay_user_team  {$pay_user_id}\n");
            }
            $pay_user_team = $pay_user_team_map[$pay_user_id];
            $pids          = is_string($pay_user_team->pid_path) ? json_decode($pay_user_team->pid_path, true) : $pay_user_team->pid_path;
            if (count($pids) === 0)
            {
                $this->printer->tabEcho('Skip:no pids' . var_export($pids, true));
                continue;
            }
            /** @var UserTeamDao[] $upper_user_team_map */
            $upper_user_team_map = [];
            $upper_user_teams    = UserTeamDao::model()->findAllByWhere(['db_part_id' => $this->db_part_id, 'user_id' => $pids]);
            $read_got_pids       = [];
            foreach ($upper_user_teams as $upper_user_team)
            {
                $upper_user_team_map[$upper_user_team->user_id] = $upper_user_team;
                $read_got_pids[]                                = $upper_user_team->user_id;
            }
            unset($upper_user_teams);
            $pids_cnt = count($pids);
            if (count($read_got_pids) !== count($pids))
            {
                $this->printer->tabEcho('Error:lost_upper_data' . var_export(array_diff($pids, $read_got_pids), false));
                continue;
            }
            foreach ($pids as $upper_path_sn => $upper_user_id)
            {
                $this->printer->newTabEcho('insert_upper_path', " upper sn:{$upper_path_sn}/{$pids_cnt}  upper user id:{$upper_user_id}");

                if (!isset($upper_user_team_map[$upper_user_id]))
                {
                    throw new \Exception("二遍查不到 :{$upper_user_id}");
                }
                $upper_user_team           = $upper_user_team_map[$upper_user_id];
                $his_dao                   = UserScoreHisDao::model();
                $his_dao->ymd              = $this->ymd;
                $his_dao->db_part_id       = $this->db_part_id;
                $his_dao->pay_user_id      = $pay_user_id;
                $his_dao->pay_order_id     = $order_row['id'];
                $his_dao->pay_order_sum    = $order_row['order_sum'];
                $his_dao->user_id          = $upper_user_id;
                $his_dao->path_sn          = $upper_path_sn;
                $his_dao->before_score_sum = $upper_user_team->score_sum;
                $his_dao->before_lev       = $upper_user_team->lev;
                $his_dao->after_score_sum  = 0;
                $his_dao->after_lev        = 0;
                $his_dao->is_take          = Opt::NOT;
                $his_dao->take_item_sn     = -1;
                $his_dao->take_number      = 0;
                $his_dao->take_rate        = 0;
                $his_dao->op_step          = self::hisStepInit;
                $his_dao->insert(false);

                $this->printer->endTabEcho('insert_upper_path', " upper sn:{$upper_path_sn}/{$pids_cnt}  upper user id:{$upper_user_id}");

            }
            $this->printer->endTabEcho('fetch_order_and_import', '遍历订单，准备填充upper path history');


        }
        $this->printer->endTabEcho('fetch_order_and_import', '遍历订单，准备填充upper path history');

    }

    public function addUpPayOrder()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 填充关系树  start\n";

        $this->initParam();

        $order_rows = $this->getOneDayOrderRows();

        $this->printer->newTabEcho('foreach_orders', '开始循环订单');

        $order_cnt      = count($order_rows);
        $tmp_upper_info = [];
        foreach ($order_rows as $order_i => $order_row)
        {

            $pay_user_id = intval($order_row['user_id']);
            $this->printer->newTabEcho('AddUpScoreAndLevItem_OrderRow', " {$order_i}/{$order_cnt} " . json_encode($order_row, JSON_UNESCAPED_SLASHES));

            $this->printer->tabEcho("get pay user team START");
            $pay_user_team = UserTeamDao::model()->findOneByWhere(['user_id' => $pay_user_id, 'db_part_id' => $this->db_part_id], false);
            if (!$pay_user_team)
            {
                die("\n Error: no_pay_user_team  {$pay_user_id}\n");
            }
            $this->printer->tabEcho(" get pay user team  END ");
            $this->printer->tabEcho("get upper user teams START");

            $pids = is_string($pay_user_team->pid_path) ? json_decode($pay_user_team->pid_path, true) : $pay_user_team->pid_path;
            if (count($pids) === 0)
            {
                $this->printer->endTabEcho('AddUpScoreAndLevItem_OrderRow', 'Skip:no pids' . var_export($pids, true));
                continue;
            }
            /** @var UserTeamDao[] $upper_user_team_map */
            $upper_user_teams = UserTeamDao::model()->findAllByWhere(['db_part_id' => $this->db_part_id, 'user_id' => $pids]);
            foreach ($upper_user_teams as $upper_user_team)
            {
                if (!isset($tmp_upper_info[$upper_user_team->user_id]))
                {
                    $tmp_upper_info[$upper_user_team->user_id] = ['score' => $upper_user_team->score_sum, 'lev' => $upper_user_team->lev];

                }
                // $upper_user_team_map[$upper_user_team->user_id] = $upper_user_team;
            }
            $this->printer->tabEcho("get upper user teams END cnt" . count($upper_user_teams));

            $this->printer->tabEcho("get his daos START");

            $his_daos = UserScoreHisDao::model()->addSort('path_sn', 'asc')->setLimit(0, 1000)->findAllByWhere(['db_part_id' => $this->db_part_id, 'pay_user_id' => $pay_user_id, 'pay_order_id' => $order_row['id'], 'op_step' => self::hisStepInit]);
            $this->printer->tabEcho("get his daos END count:" . count($his_daos));
            $this->printer->newTabEcho('AddUpScoreAndLevItem_fetch_his_daos', '遍历his daos ');

            foreach ($his_daos as $his_i => $his_dao)
            {
                $this->printer->newTabEcho('AddUpScoreAndLevItem_fetch_his_dao', '遍历his' . json_encode($his_dao, JSON_UNESCAPED_SLASHES));

                if (!isset($tmp_upper_info[$his_dao->user_id]))
                {
                    throw new \Exception("二遍查不到 :{$his_dao->user_id}");
                }

                $this->printer->tabEcho("old: " . json_encode($tmp_upper_info[$his_dao->user_id]));

                $his_dao->before_score_sum                  = $tmp_upper_info[$his_dao->user_id]['score'];
                $his_dao->after_score_sum                   = $his_dao->before_score_sum + $his_dao->pay_order_sum;
                $tmp_upper_info[$his_dao->user_id]['score'] = $his_dao->after_score_sum;
                $his_dao->before_lev                        = $tmp_upper_info[$his_dao->user_id]['lev'];
                $his_dao->after_lev                         = $this->getScoreLev($his_dao->after_score_sum);
                $tmp_upper_info[$his_dao->user_id]['lev']   = $his_dao->after_lev;

                $his_dao->op_step = self::hisStepAddUpScoreAndLev;
                $this->printer->tabEcho("new: " . json_encode($tmp_upper_info[$his_dao->user_id]));

                $this->printer->tabEcho("新属性: user_id:{$his_dao->user_id}  pay_order_sum:{$his_dao->pay_order_sum}  before_score_sum:{$his_dao->before_score_sum}  after_score_sum:{$his_dao->after_score_sum}");


                $his_dao->update();


                $this->printer->endTabEcho('AddUpScoreAndLevItem_fetch_his_dao', '遍历his');


            }
            $this->printer->endTabEcho('AddUpScoreAndLevItem_fetch_his_daos', '结束遍历his');

            $this->printer->endTabEcho('AddUpScoreAndLevItem_OrderRow', '完成订单');


        }
        $this->printer->endTabEcho('foreach_orders', '#');


    }


    public function matchedBonus()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 填充关系树  start\n";

        $this->initParam();

        $order_rows = $this->getOneDayOrderRows();

        $this->printer->newTabEcho('foreach_orders', '开始循环订单');

        $order_cnt = count($order_rows);
        foreach ($order_rows as $order_i => $order_row)
        {

            $pay_user_id = intval($order_row['user_id']);
            $order_sum   = intval($order_row['order_sum']);
            $this->printer->newTabEcho('MatchBonusItem_order_row', " {$order_i}/{$order_cnt} {$pay_user_id} {$order_row['id']}");

            $this->printer->tabEcho("get pay user team START");
            $pay_user_team = UserTeamDao::model()->findOneByWhere(['user_id' => $pay_user_id, 'db_part_id' => $this->db_part_id], false);
            if (!$pay_user_team)
            {
                die("\n Error: no_pay_user_team  {$pay_user_id}\n");
            }
            $this->printer->tabEcho(" get pay user team  END ");
            $this->printer->tabEcho("get upper user teams START");

            $pids = is_string($pay_user_team->pid_path) ? json_decode($pay_user_team->pid_path, true) : $pay_user_team->pid_path;
            if (count($pids) === 0)
            {
                $this->printer->endTabEcho('AddUpScoreAndLevItem_OrderRow', 'Skip:no pids ' . var_export($pids, true));
                continue;
            }


            $this->printer->tabEcho("get upper user teams END");

            $this->printer->tabEcho("get his daos START");

            $his_daos = UserScoreHisDao::model()->addSort('path_sn', 'asc')->setLimit(0, 1000)->findAllByWhere(['db_part_id' => $this->db_part_id, 'pay_user_id' => $pay_user_id, 'pay_order_id' => $order_row['id'], 'op_step' => self::hisStepAddUpScoreAndLev]);
            $this->printer->tabEcho("get his daos END");
            $this->printer->newTabEcho('MatchBonusItem_fetch_his_daos', '遍历his daos ');
            $curr_bonus_items = $this->getBonusItems();

            $taked_team_rate10pow8 = 0;
            foreach ($curr_bonus_items as $item_sn => $curr_bonus_item)
            {
                $this->printer->newTabEcho('MatchBonusItem_fetch_bonus_item', "遍历 bonus_item [{$item_sn}]  " . json_encode($curr_bonus_item, JSON_UNESCAPED_SLASHES));

                foreach ($his_daos as $his_i => $his_dao)
                {

                    if ($his_dao->op_step >= self::hisStepComputeBonus)
                    {
                        //$this->printer->endTabEcho('MatchBonusItem_fetch_his_dao', '跳过 遍历his');

                        continue;
                    }
                    $this->printer->newTabEcho('MatchBonusItem_fetch_his_dao', '遍历his ' . json_encode($his_dao, JSON_UNESCAPED_SLASHES));


                    if ($his_dao->before_lev > $curr_bonus_item['lev'])
                    {
                        $this->printer->tabEcho("跳过等级  用户等级 大于 奖励项等级  {$his_dao->before_lev} > {$curr_bonus_item['lev']}");

                        break;
                    }
                    $his_daos[$his_i]->op_step = self::hisStepComputeBonus;

                    if ($his_dao->before_lev === $curr_bonus_item['lev'])
                    {
                        $rate10pow8 = bcmul($curr_bonus_item['number'][0], pow(10, 8 - $curr_bonus_item['number'][1]));
                        if ($curr_bonus_item['reward_type'] === 'team')
                        {
                            $tmp                   = $rate10pow8;
                            $rate10pow8            = bcsub($rate10pow8, $taked_team_rate10pow8);
                            $taked_team_rate10pow8 = $tmp;

                        }

                        $taked_number = bcdiv(bcmul($rate10pow8, $order_sum), pow(10, 8));

                        $his_daos[$his_i]->is_take      = Opt::YES;
                        $his_daos[$his_i]->take_item_sn = $item_sn;
                        $his_daos[$his_i]->take_rate    = bcdiv($rate10pow8, pow(10, 8));
                        $his_daos[$his_i]->take_number  = $taked_number;
                        $this->printer->tabEcho("Match  用户等级 === 奖励项等级  {$his_dao->before_lev} === {$curr_bonus_item['lev']}  " . json_encode($his_daos[$his_i], JSON_UNESCAPED_SLASHES));
                        $his_daos[$his_i]->update();


                        break;
                    }
                    else
                    {
                        $his_daos[$his_i]->is_take = Opt::NOT;
                        $this->printer->tabEcho("not match ");
                        $his_daos[$his_i]->update();

                    }

                    $this->printer->endTabEcho('MatchBonusItem_fetch_his_dao', '遍历his');


                }
                $this->printer->endTabEcho('MatchBonusItem_fetch_bonus_item', 'bonus_item');

            }

            $this->printer->endTabEcho('MatchBonusItem_fetch_his_daos', '结束遍历his');

            $this->printer->endTabEcho('MatchBonusItem_order_row', '完成订单');


        }
        $this->printer->endTabEcho('foreach_orders', '#');


    }

    public function countDaily()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 填充关系树  start\n";

        $this->initParam();


        $this->printer->newTabEcho('countDaily', '开始循环');
        $sql        = "SELECT user_id,sum(pay_order_sum) as orders_sum,max(after_lev) as after_lev,min(before_lev) as before_lev,max(after_score_sum) as after_score_sum,min(before_score_sum) as before_score_sum,sum(take_number) take_sum  FROM bee_invade.dp_bonus_user_score_his where ymd={$this->ymd} group by user_id;";
        $count_rows = UserScoreHisDao::model()->getDbConnect()->setText($sql)->queryAll();
        $rows_cnt   = count($count_rows);

        foreach ($count_rows as $i => $count_row)
        {
            $this->printer->tabEcho("{$i}/{$rows_cnt} copy to daily " . json_encode($count_row));
            $daily_dao                   = UserScoreDailyDao::model();
            $daily_dao->ymd              = $this->ymd;
            $daily_dao->user_id          = $count_row['user_id'];
            $daily_dao->daily_score_sum  = $count_row['orders_sum'];
            $daily_dao->db_part_id       = $this->db_part_id;
            $daily_dao->before_lev       = $count_row['before_lev'];
            $daily_dao->before_score_sum = $count_row['before_score_sum'];
            $daily_dao->after_lev        = $count_row['after_lev'];
            $daily_dao->after_score_sum  = $count_row['after_score_sum'];
            $daily_dao->take_num         = $count_row['take_sum'];

            $daily_dao->insert(false);


        }
        $status = self::hisStepCountedDaily;
        $sql    = "update dp_bonus_user_score_his set op_step={$status} where ymd={$this->ymd} ";
        UserScoreHisDao::model()->getDbConnect()->setText($sql)->execute();

        $this->printer->endTabEcho('countDaily', '#');


    }


    public function deliverBonus()
    {
        $now_date = date('Y-m-d H:i:s', time());
        echo "\nnow:{$now_date} 填充关系树  start\n";

        $this->initParam();

        $sql        = "SELECT id, db_part_id, user_id, ymd, daily_score_sum, before_score_sum, after_score_sum, before_lev, after_lev, take_num, op_step, is_ok FROM bee_invade.dp_bonus_user_score_daily  where ymd={$this->ymd};";
        $daily_dao  = UserScoreDailyDao::model();
        $daily_rows = $daily_dao->getDbConnect()->setText($sql)->queryAll();
        $cnt        = count($daily_rows);
        foreach ($daily_rows as $tmp_i => $daily_row)
        {
            $this->printer->newTabEcho('handle_daily_row', '完成订单');
            if (intval($daily_row['is_ok']) === 2)
            {
                $this->printer->endTabEcho('handle_daily_row', '审核拒绝了，跳过 ' . json_encode($daily_row, JSON_UNESCAPED_SLASHES));
                continue;
            }
            $this->printer->tabEcho('附加 user_team');
            $update_user_team_sql = "insert ignore into dp_bonus_user_team set db_part_id={$daily_row['db_part_id']},user_id={$daily_row['user_id']},score_sum={$daily_row['after_score_sum']},lev={$daily_row['after_lev']} on duplicate key update score_sum={$daily_row['after_score_sum']},lev={$daily_row['after_lev']} ";
            $update_user_team_res = $daily_dao->getDbConnect()->setText($update_user_team_sql)->execute();
            $update_daliy_sql     = "update dp_bonus_user_score_daily set op_step=1 where id={$daily_row['id']} and op_step=0";
            $update_daily_res     = $daily_dao->getDbConnect()->setText($update_daliy_sql)->execute();
            $this->printer->tabEcho('附加 user_team  ok');

            $amount_sum = intval($daily_row['take_num']);
            if ($amount_sum > 0)
            {
                $upper_user   = User::model()->findByPk($daily_row['user_id']);
                $user_account = UserCurrency::model()->setUser($upper_user)->getAccount('gold_ingot');
                $goods_his    = (new UserCurrencyHis())->setUserAccountModel($user_account)->setOperationStep(1);
                ApiCache::model()->setCache('ChangeFlagUserCurrency', ['user_id' => $upper_user->id], time());
                $user_account->verifyKeyProperties();
                $goods_his->setOperationStep(1);
                $goods_record_res  = $goods_his->tryRecord(UserCurrencyHis::srcTeambonus, $daily_row['id'], $amount_sum);
                $update_daily_res2 = 0;
                if ($goods_record_res === false)
                {
                    $this->printer->tabEcho("\n记录 bill {}*{$amount_sum} 失败\n");
                }
                else
                {
                    $this->printer->tabEcho("\n记录 bill {}*{$amount_sum} 成功\n");
                    $update_daliy_sql2 = "update dp_bonus_user_score_daily set op_step=2 where id={$daily_row['id']} and op_step=1";
                    $update_daily_res2 = $daily_dao->getDbConnect()->setText($update_daliy_sql2)->execute();
                }
            }
            else
            {
                $this->printer->tabEcho("\n记录 不需要添加 [{$amount_sum}] 权益\n");
            }


            $this->printer->endTabEcho('handle_daily_row', '完成订单');

        }


    }
}