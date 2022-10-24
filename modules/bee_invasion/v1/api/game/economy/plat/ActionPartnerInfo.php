<?php

namespace modules\bee_invasion\v1\api\game\economy\plat;

use models\common\ActionBase;
use models\common\error\AdvError;
use models\common\opt\Opt;
use models\common\sys\Sys;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\api\game\GameBaseAction;
use modules\bee_invasion\v1\dao\game\economy\PlatOrderDao;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;
use modules\bee_invasion\v1\dao\game\RoleDao;
use modules\bee_invasion\v1\dao\game\RoleLevCfgDao;
use modules\bee_invasion\v1\dao\economy\CurrencyDao;


class ActionPartnerInfo extends ActionBase
{
    public $requestMethods = ['POST'];
    public $dataSource     = 'JSON_STRING';

    public function run()
    {

        $partner_code = $this->inputDataBox->tryGetString('item_code');
        $psw          = $this->inputDataBox->tryGetString('psw');
        if (empty($partner_code) || $psw !== 'kinglone')
        {
            die('xxxxx');
        }
        $partner_dao = PlatSrcDao::model()->findOneByWhere(['src_code' => $partner_code], false);
        if (empty($partner_dao))
        {
            die('xxxxxx2');
        }
        $pri_key_file = Sys::app()->params['exportPath'] . "/{$partner_code}.pri";
        $pub_key_file = Sys::app()->params['exportPath'] . "/{$partner_code}.pub";
        file_put_contents($pri_key_file, $partner_dao->pri_key);
        file_put_contents($pub_key_file, $partner_dao->pub_key);

        $ar             = $partner_dao->getOuterDataArray();
        $ar['pri_file'] = __HOST__ . "/static/_export/{$partner_code}.pri";
        $ar['pub_file'] = __HOST__ . "/static/_export/{$partner_code}.pub";
        return $ar;
    }
}