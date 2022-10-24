<?php

namespace modules\bee_invasion\v1\api\game\economy\plat;

use models\common\ActionBase;
use models\ext\tool\RSA;
use modules\bee_invasion\v1\dao\game\economy\PlatSrcDao;


class ActionGeneratePartner extends ActionBase
{
    public $requestMethods = ['POST'];
    public $dataSource     = 'JSON_STRING';

    /**
     * @return array
     * @throws \models\common\error\AdvError|\Exception
     */
    public function run()
    {

        $partner_code = $this->inputDataBox->tryGetString('item_code');
        $partner_name = $this->inputDataBox->tryGetString('item_name');
        $psw          = $this->inputDataBox->tryGetString('psw');
        if (empty($partner_code) || empty($partner_name) || $psw !== 'kinglone')
        {
            die('xxxxx');
        }
        $partner_dao = PlatSrcDao::model()->findOneByWhere(['src_code' => $partner_code], false);
        if (!empty($partner_dao))
        {
            die('xxxxxx2');
        }
        $partner_dao           = PlatSrcDao::model();
        $partner_dao->src_code = $partner_code;
        $partner_dao->src_name = $partner_name;
        $pub                   = '';
        $pri                   = '';
        new RSA($pub, $pri);
        $partner_dao->pri_key = $pri;
        $partner_dao->pub_key = $pub;
        $partner_dao->insert();
        return $partner_dao->getOuterDataArray();

    }
}