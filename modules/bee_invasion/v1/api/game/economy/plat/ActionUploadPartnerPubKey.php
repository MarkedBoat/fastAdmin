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


class ActionUploadPartnerPubKey extends ActionBase
{
    public $requestMethods = ['POST'];

    public function run()
    {

        $partner_code = $this->inputDataBox->tryGetString('item_code');
        if (empty($partner_code) || $this->inputDataBox->tryGetString('psw') !== 'kinglone')
        {
            die('xxxxx');
        }

        $partner_dao = PlatSrcDao::model()->findOneByWhere(['src_code' => $partner_code], false);
        if (empty($partner_dao))
        {
            die('xxxxxx2');
        }
        if (!(isset($_FILES['pubkey']) && isset($_FILES['pubkey']['tmp_name']) && is_file($_FILES['pubkey']['tmp_name'])))
        {
            die('xxxxxx3');
        }
        $pub_key   = file_get_contents($_FILES['pubkey']['tmp_name']);
        $pubKeyRes = openssl_get_publickey($pub_key);
        if ($pubKeyRes === false)
            throw  new \Exception('公钥初始化错误', 400);
        //openssl_free_key($pubKeyRes);
        $partner_dao->src_pub_key = $pub_key;
        $partner_dao->update();

        return $partner_dao->getOuterDataArray();

    }
}