<?php

namespace modules\bee_invasion\v1\model\user;


use models\common\opt\Opt;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\dao\user\UserInviterDao;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\TInfo;
use modules\bee_invasion\v1\model\tool\BiParam;

class User extends UserDao
{
    use TInfo;
    const armed_changed     = 'armed_changed';    //装备(动词，武装)变化
    const cg_changed        = 'cg_changed';       //道具数量变化
    const currency_changed  = 'currency_changed'; //通货数量变化
    const equipment_changed = 'equipment_changed';//名下装备(名词，资产)数量有变化
    const lev_changed       = 'lev_up';           //升级了
    const note_changed      = 'note_changed';     //名下凭据值变化


    private $changed_data_codes = ['get' => 'get'];

    public function addChangedCode($code)
    {
        $this->changed_data_codes[$code] = $code;
    }

    public function getChangedCodes()
    {
        return $this->changed_data_codes;
    }


    public function checkLevelUp(UserCurrency $points_account)
    {
        $role                 = RoleDao::model()->findOneByWhere(['user_id' => $this->id]);
        $curr_lev             = $role->lev;
        $lev_cfgs             = RoleLevCfgDao::model()->addSort('lev', 'asc')->findAllByWhere(['is_ok' => Opt::isOk]);
        $vip_fragment_account = UserCurrency::model()->setUser($this)->getAccount('vip_fragment');
        foreach ($lev_cfgs as $lev_cfg)
        {
            if ($lev_cfg->lev <= $curr_lev || $lev_cfg->lev_up_points > $points_account->item_amount)
            {
                continue;
            }
            if (isset($lev_cfg->award['vip_fragment']) && $lev_cfg->award['vip_fragment'] > 0)
            {
                $points_his        = (new UserCurrencyHis())->setUserAccountModel($vip_fragment_account)->setOperationStep(1);
                $points_record_res = $points_his->tryRecord(UserCurrencyHis::srcLevUp, $lev_cfg->lev, $lev_cfg->award['vip_fragment']);
            }
            $this->addChangedCode(self::lev_changed);
            $role->lev = $lev_cfg->lev;
            $role->update();
        }

    }

    public function generateOpenId()
    {
        if (empty($this->open_id))
        {
            $this->open_id = date('Ymdh', time()) . (100009999 + $this->id);
            $this->update(false);
        }

    }

    public static function openId2TrueId($fake_user_id)
    {
        return intval(substr($fake_user_id, 10)) - 100009999;
    }


    public function getOpenCode()
    {

        //['0','1','i','l','o'],['v','w','x','y','z']  去除  1 L I  O 0
        return self::trueId2OpenCode($this->id);
    }

    public static function trueId2OpenCode($user_id)
    {
        return BiParam::getCurrentDbBlockOpenIndex() . BiParam::std10Number10ToNew30($user_id);
    }

    public static function openCode2TrueId($open_code)
    {
        return BiParam::new30numberToStd10(substr($open_code, 2));
    }

    public static function openCode2DbBlockIndex($open_code)
    {
        return BiParam::new30numberToStd10(substr($open_code, 0, 2));
    }


    public function getOpenInfo()
    {
        $this->generateOpenId();
        return [
            'id'          => intval($this->id),
            'open_id'     => $this->open_id,
            'nickname'    => $this->nickname,
            'avatar'      => $this->avatar,
            'mobile'      => preg_replace('/(\d{3})(\d{4})(\d{4})/i', '$1****$3', $this->mobile),
            'reg_time'    => $this->reg_time,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ];
    }

    public function getSimpleOpenInfo()
    {
        $this->generateOpenId();
        return [
            'id'          => intval($this->id),
            'open_id'     => $this->open_id,
            'nickname'    => $this->nickname,
            'avatar'      => $this->avatar,
            'mobile'      => preg_replace('/(\d{3})(\d{4})(\d{4})/i', '$1****$3', $this->mobile),
            'reg_time'    => $this->reg_time,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ];
    }

    public function getInviter()
    {
        $dao = UserInviterDao::model()->findOneByWhere(['be_invited_id' => $this->id], false);
        if (empty($dao) || $dao->inviter_id)
        {
            return false;
        }

    }
}