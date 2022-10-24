<?php

namespace modules\bee_invasion\v1\model\role;


use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use modules\bee_invasion\v1\dao\game\role\RoleArmDao;
use modules\bee_invasion\v1\dao\game\role\RoleNoteDao;
use modules\bee_invasion\v1\dao\game\role\RolePerkDao;
use modules\bee_invasion\v1\dao\user\UserCgDao;
use modules\bee_invasion\v1\dao\user\UserCgHisDao;
use modules\bee_invasion\v1\dao\user\UserDao;
use modules\bee_invasion\v1\model\economy\ConsumableGoods;
use modules\bee_invasion\v1\model\economy\Note;
use modules\bee_invasion\v1\model\play\Perk;
use modules\bee_invasion\v1\model\TCache;
use modules\bee_invasion\v1\model\user\TUserAccount;
use modules\bee_invasion\v1\model\user\User;

class RoleNote extends RoleNoteDao
{
    use TUserAccount;


    protected $valueType  = 'value';
    protected $valueField = 'curr_note_code';//db table存储值的字段

    const cacheConfigKey_value       = 'RoleNoteCurrent';
    const cacheConfigKey_accountInfo = 'RoleNoteAccountInfo';


    const noteStatus_clear = 0;
    const noteStatus_set   = 1;
    const noteStatus_wait  = 2;
    const noteStatus_using = 5;
    const noteStatus_used  = 8;


    private static $account_info_map = [];


    public function initItemModel()
    {
        $this->itemModel = new Note();
    }

    public function getUserChangeCodes()
    {
        return User::note_changed;
    }

    public function getAccountType()
    {
        return Opt::valueType_value;
    }

    /**
     * 生产一个票据 code
     * @param $user_id
     * @param string $item_code 票据item_code
     * @param int $ttl 存活多少秒
     * @return string
     * @throws \Exception
     */
    public static function generateNoteCode($user_id, $item_code, $ttl)
    {
        $expires = time() + $ttl;
        $rand    = rand(10000, 100000);
        $str     = "{$expires}#{$rand}#{$item_code}#{$user_id}";
        return $str . '#' . substr(md5(Sys::app()->params['secret_key']['note_md5'] . "#{$str}"), 8, 16);
    }

    /**
     * 检查票据code
     *
     * @param $user_id
     * @param string $item_code 票据item_code
     * @param string $note_code 具体票据code
     * @return array  用 in_arary() 判断里面有没有  0 ，有0即失败  全是1 则通过
     * @throws \Exception
     */
    public static function verifyNoteCode($user_id, $item_code, $note_code)
    {
        $res = [
            'format'    => -1,
            'expires'   => -1,
            'user_id'   => -1,
            'item_code' => -1,
            'sign'      => -1,
        ];
        $ar  = explode('#', $note_code);
        if (count($ar) !== 5)
        {
            $res['format'] = 0;
            return $res;
        }
        $res['format'] = 1;
        list($expires, $rand, $item_code_str, $user_id_str, $sign_str) = $ar;
        if (time() > $expires)
        {
            $res['expires'] = 0;
            return $res;
        }
        $res['expires'] = 1;

        if ($item_code_str !== $item_code)
        {
            $res['item_code'] = 0;
            return $res;
        }
        $res['item_code'] = 1;


        if ($user_id_str !== strval($user_id))
        {
            $res['user_id'] = 0;
            return $res;
        }
        $res['user_id'] = 1;


        $res['sign'] = $sign_str === substr(md5(Sys::app()->params['secret_key']['note_md5'] . "#{$expires}#{$rand}#{$item_code}#{$user_id}"), 8, 16) ? 1 : 0;
        return $res;

    }

    public function getOpenInfo()
    {
        return [
            'note_type'           => $this->item_code,
            'current_note_code'   => $this->item_value,
            'current_note_status' => $this->item_status >= Opt::noteStatus_useless ? 'useless' : 'ok',
            'update_time'         => $this->update_time,
        ];

    }
}